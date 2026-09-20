<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Person;
use App\Models\Question;
use App\Models\Survey;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionIssuesTest extends TestCase
{
    use RefreshDatabase;

    public function test_survey_creation_wizard_route_resolves(): void
    {
        $admin = Person::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/surveys/create-survey/step-1');

        $response->assertOk();
    }

    public function test_unknown_web_route_returns_404_status(): void
    {
        $admin = Person::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/this-route-does-not-exist');

        $response->assertStatus(404);
    }

    public function test_dashboard_summary_endpoint_works(): void
    {
        $person = Person::factory()->create();

        $response = $this->actingAs($person)->getJson('/api/dashboard/summary');

        $response->assertOk()->assertJsonStructure(['respondents', 'pollsters', 'results']);
    }

    public function test_admin_can_edit_a_staff_member(): void
    {
        $admin = Person::factory()->admin()->create();
        $staff = Person::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/person/pollster-admin/update/{$staff->id}", [
            'name' => 'Nombre Actualizado',
            'email' => $staff->email,
            'sex_id' => $staff->sex_id,
            // Reutiliza el rol ya asignado por la factory en vez de asumir un
            // id fijo: los ids de "roles" dependen del orden de creación entre
            // tests (RefreshDatabase no reinicia la secuencia de autoincremento).
            'rol_id' => $staff->rol_id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('persons', ['id' => $staff->id, 'name' => 'Nombre Actualizado']);
    }

    public function test_admin_can_list_staff_roles(): void
    {
        $admin = Person::factory()->admin()->create();
        Person::factory()->create(); // asegura que el rol POLLSTER también exista

        $response = $this->actingAs($admin)->getJson('/api/person/roles');

        $response->assertOk();
        $names = collect($response->json())->pluck('name');
        $this->assertTrue($names->contains('ADMIN'));
        $this->assertTrue($names->contains('POLLSTER'));
    }

    public function test_generated_urls_use_app_url_as_root_regardless_of_request_host(): void
    {
        config(['app.url' => 'http://example.test:9999']);
        (new AppServiceProvider(app()))->boot();

        $this->assertStringStartsWith('http://example.test:9999', url('/foo'));
    }

    public function test_settings_page_is_reachable(): void
    {
        $person = Person::factory()->create();

        $response = $this->actingAs($person)->get('/settings');

        $response->assertOk();
    }

    public function test_a_user_can_update_their_own_profile(): void
    {
        $person = Person::factory()->create();

        $response = $this->actingAs($person)->putJson('/api/profile', [
            'name' => 'Nombre Nuevo',
            'email' => $person->email,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('persons', ['id' => $person->id, 'name' => 'Nombre Nuevo']);
    }

    public function test_a_user_cannot_change_password_without_the_current_one(): void
    {
        $person = Person::factory()->create(['password' => bcrypt('OldPassword123')]);

        $response = $this->actingAs($person)->putJson('/api/profile', [
            'name' => $person->name,
            'email' => $person->email,
            'password' => 'NewPassword123',
        ]);

        $response->assertStatus(422);
    }

    public function test_a_user_can_change_their_password_with_the_correct_current_one(): void
    {
        $person = Person::factory()->create(['password' => bcrypt('OldPassword123')]);

        $response = $this->actingAs($person)->putJson('/api/profile', [
            'name' => $person->name,
            'email' => $person->email,
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword123',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('NewPassword123', $person->fresh()->password));
    }

    public function test_a_non_admin_can_hide_a_survey_via_the_delete_route(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();

        $response = $this->actingAs($pollster)->deleteJson("/api/survey/delete/{$survey->id}");

        $response->assertOk();
        $this->assertSoftDeleted('surveys', ['id' => $survey->id]);
    }

    public function test_a_non_admin_can_hide_a_category_via_the_delete_route(): void
    {
        $pollster = Person::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($pollster)->deleteJson("/api/category/delete/{$category->id}");

        $response->assertOk();
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_a_non_admin_can_hide_a_question_via_the_delete_route(): void
    {
        $pollster = Person::factory()->create();
        $question = Question::factory()->create();

        $response = $this->actingAs($pollster)->deleteJson("/api/question/delete/{$question->id}");

        $response->assertOk();
        $this->assertSoftDeleted('questions', ['id' => $question->id]);
    }

    public function test_a_non_admin_can_hide_an_answer_via_the_delete_route(): void
    {
        $pollster = Person::factory()->create();
        $answer = Answer::factory()->create();

        $response = $this->actingAs($pollster)->deleteJson("/api/answer/delete/{$answer->id}");

        $response->assertOk();
        $this->assertSoftDeleted('answers', ['id' => $answer->id]);
    }

    public function test_a_non_admin_cannot_restore_or_force_delete_an_answer(): void
    {
        $pollster = Person::factory()->create();
        $answer = Answer::factory()->create();
        $answer->delete();

        $this->actingAs($pollster)->patchJson("/api/answer/restore/{$answer->id}")->assertStatus(403);
        $this->actingAs($pollster)->deleteJson("/api/answer/force-delete/{$answer->id}")->assertStatus(403);
        $this->assertSoftDeleted('answers', ['id' => $answer->id]);
    }

    public function test_an_admin_can_restore_and_force_delete_an_answer(): void
    {
        $admin = Person::factory()->admin()->create();
        $answer = Answer::factory()->create(['name' => 'RESPUESTA ORIGINAL']);
        $answer->delete();

        $this->actingAs($admin)->patchJson("/api/answer/restore/{$answer->id}")->assertOk();
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'deleted_at' => null,
            'name' => 'RESPUESTA ORIGINAL',
        ]);

        $this->actingAs($admin)->deleteJson("/api/answer/force-delete/{$answer->id}")->assertOk();
        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    public function test_an_admin_can_reorder_categories_within_the_same_survey(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $categoryA = Category::factory()->create(['survey_id' => $survey->id, 'order' => 1]);
        $categoryB = Category::factory()->create(['survey_id' => $survey->id, 'order' => 2]);

        $response = $this->actingAs($admin)->putJson('/api/category/reorder', [
            'items' => [
                ['id' => $categoryA->id, 'order' => 2],
                ['id' => $categoryB->id, 'order' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('categories', ['id' => $categoryA->id, 'order' => 2]);
        $this->assertDatabaseHas('categories', ['id' => $categoryB->id, 'order' => 1]);
    }

    public function test_reordering_categories_across_different_surveys_is_rejected(): void
    {
        $admin = Person::factory()->admin()->create();
        $categoryFromSurveyA = Category::factory()->create();
        $categoryFromSurveyB = Category::factory()->create();

        $response = $this->actingAs($admin)->putJson('/api/category/reorder', [
            'items' => [
                ['id' => $categoryFromSurveyA->id, 'order' => 1],
                ['id' => $categoryFromSurveyB->id, 'order' => 2],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_an_admin_can_reorder_questions_within_the_same_category(): void
    {
        $admin = Person::factory()->admin()->create();
        $category = Category::factory()->create();
        $questionA = Question::factory()->create(['category_id' => $category->id, 'order' => 1]);
        $questionB = Question::factory()->create(['category_id' => $category->id, 'order' => 2]);

        $response = $this->actingAs($admin)->putJson('/api/question/reorder', [
            'items' => [
                ['id' => $questionA->id, 'order' => 2],
                ['id' => $questionB->id, 'order' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('questions', ['id' => $questionA->id, 'order' => 2]);
        $this->assertDatabaseHas('questions', ['id' => $questionB->id, 'order' => 1]);
    }

    public function test_an_admin_can_reorder_answers_within_the_same_question(): void
    {
        $admin = Person::factory()->admin()->create();
        $question = Question::factory()->create();
        $answerA = Answer::factory()->create(['question_id' => $question->id, 'order' => 1]);
        $answerB = Answer::factory()->create(['question_id' => $question->id, 'order' => 2]);

        $response = $this->actingAs($admin)->putJson('/api/answer/reorder', [
            'items' => [
                ['id' => $answerA->id, 'order' => 2],
                ['id' => $answerB->id, 'order' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('answers', ['id' => $answerA->id, 'order' => 2]);
        $this->assertDatabaseHas('answers', ['id' => $answerB->id, 'order' => 1]);
    }

    public function test_reordering_answers_across_different_questions_is_rejected(): void
    {
        $admin = Person::factory()->admin()->create();
        $answerFromQuestionA = Answer::factory()->create();
        $answerFromQuestionB = Answer::factory()->create();

        $response = $this->actingAs($admin)->putJson('/api/answer/reorder', [
            'items' => [
                ['id' => $answerFromQuestionA->id, 'order' => 1],
                ['id' => $answerFromQuestionB->id, 'order' => 2],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_a_non_admin_cannot_restore_or_force_delete_a_survey(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $survey->delete();

        $this->actingAs($pollster)->patchJson("/api/survey/restore/{$survey->id}")->assertStatus(403);
        $this->actingAs($pollster)->deleteJson("/api/survey/force-delete/{$survey->id}")->assertStatus(403);
        $this->assertSoftDeleted('surveys', ['id' => $survey->id]);
    }

    public function test_an_admin_can_restore_and_force_delete_a_survey(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $survey->delete();

        $this->actingAs($admin)->patchJson("/api/survey/restore/{$survey->id}")->assertOk();
        $this->assertDatabaseHas('surveys', ['id' => $survey->id, 'deleted_at' => null]);

        $this->actingAs($admin)->deleteJson("/api/survey/force-delete/{$survey->id}")->assertOk();
        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
    }

    public function test_survey_search_query_param_filters_results(): void
    {
        $admin = Person::factory()->admin()->create();
        Survey::factory()->create(['name' => 'ENCUESTA DE SALUD']);
        Survey::factory()->create(['name' => 'ENCUESTA DE EDUCACION']);

        $response = $this->actingAs($admin)->getJson('/api/survey/show-all?search=SALUD');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('ENCUESTA DE SALUD'));
        $this->assertFalse($names->contains('ENCUESTA DE EDUCACION'));
    }

    public function test_person_search_query_param_filters_results(): void
    {
        $admin = Person::factory()->admin()->create();
        Person::factory()->create(['name' => 'Juan Perez']);
        Person::factory()->create(['name' => 'Maria Gomez']);

        $response = $this->actingAs($admin)->getJson('/api/person/pollster-admin/list?search=Juan');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Juan Perez'));
        $this->assertFalse($names->contains('Maria Gomez'));
    }

    public function test_disabling_a_person_requires_a_reason(): void
    {
        $admin = Person::factory()->admin()->create();
        $staff = Person::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/person/disable/{$staff->id}", []);

        $response->assertStatus(422);
        $this->assertDatabaseHas('persons', ['id' => $staff->id, 'disabled_at' => null]);
    }

    public function test_an_admin_cannot_disable_their_own_account(): void
    {
        $admin = Person::factory()->admin()->create();

        $response = $this->actingAs($admin)->putJson("/api/person/disable/{$admin->id}", [
            'reason' => 'motivo de prueba',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('persons', ['id' => $admin->id, 'disabled_at' => null]);
    }

    public function test_an_admin_cannot_disable_the_last_active_admin(): void
    {
        // Un admin ya deshabilitado no debe contar para mantener "activo" al
        // último administrador restante.
        $disabledAdmin = Person::factory()->admin()->create([
            'disabled_at' => now(),
            'disabled_reason' => 'Ya estaba deshabilitado previamente',
        ]);
        $activeAdmin = Person::factory()->admin()->create();

        $response = $this->actingAs($disabledAdmin)->putJson("/api/person/disable/{$activeAdmin->id}", [
            'reason' => 'motivo de prueba',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('persons', ['id' => $activeAdmin->id, 'disabled_at' => null]);
    }

    public function test_an_admin_can_disable_and_enable_a_staff_member(): void
    {
        $admin = Person::factory()->admin()->create();
        Person::factory()->admin()->create(); // asegura que no sea "el último admin"
        $staff = Person::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/person/disable/{$staff->id}", [
            'reason' => 'Incumplimiento de normas',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('persons', [
            'id' => $staff->id,
            'disabled_reason' => 'Incumplimiento de normas',
        ]);
        $this->assertNotNull($staff->fresh()->disabled_at);

        $response = $this->actingAs($admin)->putJson("/api/person/enable/{$staff->id}");

        $response->assertOk();
        $this->assertDatabaseHas('persons', [
            'id' => $staff->id,
            'disabled_at' => null,
            'disabled_reason' => null,
        ]);
    }

    public function test_a_disabled_person_cannot_log_in(): void
    {
        $person = Person::factory()->create([
            'password' => bcrypt('Password123'),
            'disabled_at' => now(),
            'disabled_reason' => 'Motivo de prueba',
        ]);

        // El middleware statefulApi() de Sanctum solo arranca la sesión para
        // requests que reconoce como "del frontend" (dominio en
        // config('sanctum.stateful') vía el header Referer/Origin); sin eso,
        // $request->session() no existe y el controlador fallaría. Una vez
        // que se reconoce como "stateful" también exige CSRF, que un cliente
        // de test no trae, así que se desactiva solo esa validación.
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $response = $this->postJson('/api/login', [
            'email' => $person->email,
            'password' => 'Password123',
        ], ['Referer' => 'http://localhost']);

        $response->assertStatus(403);
        $this->assertGuest();
    }
}

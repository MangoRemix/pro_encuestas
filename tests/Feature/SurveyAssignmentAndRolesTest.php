<?php

namespace Tests\Feature;

use App\Models\Parish;
use App\Models\Person;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyAssignmentAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_gestor_encuestas_can_create_and_update_surveys(): void
    {
        $gestor = Person::factory()->gestorEncuestas()->create();

        $createResponse = $this->actingAs($gestor)->postJson('/api/survey/create', [
            'name' => 'Encuesta de prueba',
            'init_date' => now()->toDateString(),
            'finish_date' => now()->addMonth()->toDateString(),
            'parish_id' => $gestor->parish_id,
        ]);

        $createResponse->assertStatus(201);

        $survey = Survey::firstWhere('name', 'ENCUESTA DE PRUEBA');

        $this->actingAs($gestor)->putJson("/api/survey/update/{$survey->id}", [
            'name' => 'Encuesta actualizada',
        ])->assertOk();
    }

    public function test_a_gestor_encuestas_cannot_force_delete_a_survey(): void
    {
        $gestor = Person::factory()->gestorEncuestas()->create();
        $survey = Survey::factory()->create();
        $survey->delete();

        $this->actingAs($gestor)->deleteJson("/api/survey/force-delete/{$survey->id}")
            ->assertStatus(403);
    }

    public function test_a_gestor_encuestas_cannot_access_admin_only_staff_and_reports_endpoints(): void
    {
        $gestor = Person::factory()->gestorEncuestas()->create();

        $this->actingAs($gestor)->getJson('/api/person/pollster-admin/list')->assertStatus(403);
        $this->actingAs($gestor)->postJson('/api/parish/create', ['name' => 'X'])->assertStatus(403);
        $this->actingAs($gestor)->getJson('/api/result/reports/top-pollsters')->assertStatus(403);
    }

    public function test_a_plain_pollster_cannot_create_or_assign_surveys(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $otherPollster = Person::factory()->create();

        $this->actingAs($pollster)->postJson('/api/survey/create', [
            'name' => 'X',
            'init_date' => now()->toDateString(),
            'finish_date' => now()->addMonth()->toDateString(),
            'parish_id' => $pollster->parish_id,
        ])->assertStatus(403);

        $this->actingAs($pollster)->postJson("/api/survey/{$survey->id}/assign", [
            'person_id' => $otherPollster->id,
        ])->assertStatus(403);
    }

    public function test_an_admin_can_assign_and_unassign_a_pollster_preserving_history(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();

        $this->actingAs($admin)->postJson("/api/survey/{$survey->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(201);

        $this->assertDatabaseHas('survey_person', [
            'survey_id' => $survey->id,
            'person_id' => $pollster->id,
            'unassigned_at' => null,
        ]);

        $this->actingAs($admin)->deleteJson("/api/survey/{$survey->id}/unassign/{$pollster->id}")
            ->assertOk();

        // La fila queda en la tabla (historial), solo se marca unassigned_at.
        $this->assertDatabaseHas('survey_person', [
            'survey_id' => $survey->id,
            'person_id' => $pollster->id,
        ]);
        $this->assertDatabaseMissing('survey_person', [
            'survey_id' => $survey->id,
            'person_id' => $pollster->id,
            'unassigned_at' => null,
        ]);
    }

    public function test_cannot_assign_the_same_pollster_twice_while_active(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();

        $this->actingAs($admin)->postJson("/api/survey/{$survey->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(201);

        $this->actingAs($admin)->postJson("/api/survey/{$survey->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(409);
    }

    public function test_assigning_a_non_pollster_person_is_rejected(): void
    {
        $admin = Person::factory()->admin()->create();
        $otherAdmin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();

        $this->actingAs($admin)->postJson("/api/survey/{$survey->id}/assign", [
            'person_id' => $otherAdmin->id,
        ])->assertStatus(422);
    }

    public function test_mobile_surveys_endpoint_only_returns_assigned_and_currently_active_surveys(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();

        $activeAssigned = Survey::factory()->create([
            'init_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
        ]);
        $expiredAssigned = Survey::factory()->create([
            'init_date' => now()->subMonth(),
            'finish_date' => now()->subDay(),
        ]);
        $activeNotAssigned = Survey::factory()->create([
            'init_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
        ]);

        $this->actingAs($admin)->postJson("/api/survey/{$activeAssigned->id}/assign", ['person_id' => $pollster->id]);
        $this->actingAs($admin)->postJson("/api/survey/{$expiredAssigned->id}/assign", ['person_id' => $pollster->id]);

        $response = $this->actingAs($pollster)->getJson('/api/mobile/surveys');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id');

        $this->assertTrue($ids->contains($activeAssigned->id));
        $this->assertFalse($ids->contains($expiredAssigned->id));
        $this->assertFalse($ids->contains($activeNotAssigned->id));
    }

    public function test_unassigning_does_not_remove_survey_from_history_endpoint(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();

        $this->actingAs($admin)->postJson("/api/survey/{$survey->id}/assign", ['person_id' => $pollster->id]);
        $this->actingAs($admin)->deleteJson("/api/survey/{$survey->id}/unassign/{$pollster->id}");

        $active = $this->actingAs($admin)->getJson("/api/survey/{$survey->id}/pollsters")->json();
        $history = $this->actingAs($admin)->getJson("/api/survey/{$survey->id}/pollsters?history=true")->json();

        $this->assertCount(0, $active);
        $this->assertCount(1, $history);
    }

    public function test_reactivating_a_survey_with_a_new_jornada_preserves_the_previous_one_in_history(): void
    {
        $admin = Person::factory()->admin()->create();
        $oldParish = Parish::factory()->create();
        $newParish = Parish::factory()->create();

        $survey = Survey::factory()->create([
            'parish_id' => $oldParish->id,
            'init_date' => now()->subMonth(),
            'finish_date' => now()->subDay(),
        ]);

        $this->actingAs($admin)->putJson("/api/survey/update/{$survey->id}", [
            'parish_id' => $newParish->id,
            'init_date' => now()->toDateString(),
            'finish_date' => now()->addMonth()->toDateString(),
            'is_new_jornada' => true,
        ])->assertOk();

        $this->assertDatabaseHas('survey_runs', [
            'survey_id' => $survey->id,
            'parish_id' => $oldParish->id,
        ]);

        $survey->refresh();
        $this->assertSame($newParish->id, $survey->parish_id);
    }

    public function test_a_plain_edit_without_the_new_jornada_flag_does_not_log_history(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();

        $this->actingAs($admin)->putJson("/api/survey/update/{$survey->id}", [
            'name' => 'Nombre corregido',
        ])->assertOk();

        $this->assertDatabaseCount('survey_runs', 0);
    }
}

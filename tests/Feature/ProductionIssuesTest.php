<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Providers\AppServiceProvider;
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
            'rol_id' => 1,
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
}

<?php

namespace Tests\Feature;

use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_settings_route_does_not_exist_yet(): void
    {
        $admin = Person::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/settings');

        $response->assertStatus(404);
    }
}

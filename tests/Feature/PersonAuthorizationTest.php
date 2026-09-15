<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Sex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pollster_cannot_create_an_admin_account(): void
    {
        $pollster = Person::factory()->create();
        $sex = Sex::factory()->create();

        $response = $this->actingAs($pollster)->postJson('/api/person/pollster-admin/create', [
            'name' => 'Nuevo Admin',
            'email' => 'nuevo-admin@example.com',
            'password' => 'password123',
            'sex_id' => $sex->id,
            'rol_id' => 3,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('persons', ['email' => 'nuevo-admin@example.com']);
    }

    public function test_an_admin_can_create_a_staff_account(): void
    {
        $admin = Person::factory()->admin()->create();
        $sex = Sex::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/person/pollster-admin/create', [
            'name' => 'Nuevo Encuestador',
            'email' => 'nuevo-encuestador@example.com',
            'password' => 'password123',
            'sex_id' => $sex->id,
            'rol_id' => 1,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('persons', ['email' => 'nuevo-encuestador@example.com']);
    }

    public function test_a_pollster_cannot_delete_another_person(): void
    {
        $pollster = Person::factory()->create();
        $victim = Person::factory()->create();

        $response = $this->actingAs($pollster)->deleteJson("/api/person/delete/{$victim->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('persons', ['id' => $victim->id, 'deleted_at' => null]);
    }

    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = Person::factory()->admin()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/person/delete/{$admin->id}");

        $response->assertUnprocessable();
        $this->assertDatabaseHas('persons', ['id' => $admin->id, 'deleted_at' => null]);
    }
}

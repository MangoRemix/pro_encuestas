<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Parish;
use App\Models\Person;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityAssignmentAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_gestor_encuestas_can_create_and_update_surveys(): void
    {
        $gestor = Person::factory()->gestorEncuestas()->create();

        $createResponse = $this->actingAs($gestor)->postJson('/api/survey/create', [
            'name' => 'Encuesta de prueba',
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

    public function test_a_plain_pollster_cannot_create_surveys_or_activities_or_assign_them(): void
    {
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create();
        $otherPollster = Person::factory()->create();

        $this->actingAs($pollster)->postJson('/api/survey/create', [
            'name' => 'X',
        ])->assertStatus(403);

        $this->actingAs($pollster)->postJson('/api/activity/create', [
            'survey_id' => $activity->survey_id,
            'parish_ids' => $activity->parishes()->pluck('parishes.id')->all(),
            'init_date' => now()->toDateString(),
            'finish_date' => now()->addMonth()->toDateString(),
        ])->assertStatus(403);

        $this->actingAs($pollster)->postJson("/api/activity/{$activity->id}/assign", [
            'person_id' => $otherPollster->id,
        ])->assertStatus(403);
    }

    public function test_an_admin_can_assign_and_unassign_a_pollster_preserving_history(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create();

        $this->actingAs($admin)->postJson("/api/activity/{$activity->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(201);

        $this->assertDatabaseHas('activity_person', [
            'activity_id' => $activity->id,
            'person_id' => $pollster->id,
            'unassigned_at' => null,
        ]);

        $this->actingAs($admin)->deleteJson("/api/activity/{$activity->id}/unassign/{$pollster->id}")
            ->assertOk();

        // La fila queda en la tabla (historial), solo se marca unassigned_at.
        $this->assertDatabaseHas('activity_person', [
            'activity_id' => $activity->id,
            'person_id' => $pollster->id,
        ]);
        $this->assertDatabaseMissing('activity_person', [
            'activity_id' => $activity->id,
            'person_id' => $pollster->id,
            'unassigned_at' => null,
        ]);
    }

    public function test_cannot_assign_the_same_pollster_twice_while_active(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create();

        $this->actingAs($admin)->postJson("/api/activity/{$activity->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(201);

        $this->actingAs($admin)->postJson("/api/activity/{$activity->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(409);
    }

    public function test_activity_index_eager_loads_active_pollsters_to_avoid_n_plus_one(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create();

        $activity->assignedPollsters()->attach($pollster->id, [
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($admin)->getJson('/api/activity/show-all');

        $response->assertOk();
        $found = collect($response->json('data'))->firstWhere('id', $activity->id);
        $this->assertNotNull($found);
        $this->assertArrayHasKey('active_pollsters', $found);
        $this->assertSame($pollster->id, $found['active_pollsters'][0]['id']);
    }

    public function test_activity_index_is_paginated(): void
    {
        $admin = Person::factory()->admin()->create();
        Activity::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/activity/show-all?per_page=2');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
        $this->assertCount(2, $response->json('data'));
        $this->assertSame(2, $response->json('last_page'));
    }

    public function test_activity_index_filters_by_status(): void
    {
        $admin = Person::factory()->admin()->create();
        $active = Activity::factory()->create([
            'init_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
        ]);
        $closed = Activity::factory()->create([
            'init_date' => now()->subMonth(),
            'finish_date' => now()->subDay(),
        ]);

        $vigente = $this->actingAs($admin)->getJson('/api/activity/show-all?status=vigente');
        $ids = collect($vigente->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($active->id));
        $this->assertFalse($ids->contains($closed->id));

        $finalizada = $this->actingAs($admin)->getJson('/api/activity/show-all?status=finalizada');
        $ids = collect($finalizada->json('data'))->pluck('id');
        $this->assertFalse($ids->contains($active->id));
        $this->assertTrue($ids->contains($closed->id));
    }

    public function test_assigning_a_non_pollster_person_is_rejected(): void
    {
        $admin = Person::factory()->admin()->create();
        $otherAdmin = Person::factory()->admin()->create();
        $activity = Activity::factory()->create();

        $this->actingAs($admin)->postJson("/api/activity/{$activity->id}/assign", [
            'person_id' => $otherAdmin->id,
        ])->assertStatus(422);
    }

    public function test_mobile_activities_endpoint_only_returns_assigned_and_currently_active_activities(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();

        $activeAssigned = Activity::factory()->create([
            'init_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
        ]);
        $expiredAssigned = Activity::factory()->create([
            'init_date' => now()->subMonth(),
            'finish_date' => now()->subDay(),
        ]);
        $activeNotAssigned = Activity::factory()->create([
            'init_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
        ]);

        $this->actingAs($admin)->postJson("/api/activity/{$activeAssigned->id}/assign", ['person_id' => $pollster->id]);
        $this->actingAs($admin)->postJson("/api/activity/{$expiredAssigned->id}/assign", ['person_id' => $pollster->id]);

        $response = $this->actingAs($pollster)->getJson('/api/mobile/activities');

        $response->assertOk();
        $ids = collect($response->json())->pluck('id');

        $this->assertTrue($ids->contains($activeAssigned->id));
        $this->assertFalse($ids->contains($expiredAssigned->id));
        $this->assertFalse($ids->contains($activeNotAssigned->id));
    }

    public function test_unassigning_does_not_remove_activity_from_history_endpoint(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create();

        $this->actingAs($admin)->postJson("/api/activity/{$activity->id}/assign", ['person_id' => $pollster->id]);
        $this->actingAs($admin)->deleteJson("/api/activity/{$activity->id}/unassign/{$pollster->id}");

        $active = $this->actingAs($admin)->getJson("/api/activity/{$activity->id}/pollsters")->json();
        $history = $this->actingAs($admin)->getJson("/api/activity/{$activity->id}/pollsters?history=true")->json();

        $this->assertCount(0, $active);
        $this->assertCount(1, $history);
    }

    public function test_cannot_assign_a_pollster_to_a_closed_activity(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create([
            'init_date' => now()->subMonth(),
            'finish_date' => now()->subDay(),
        ]);

        $this->actingAs($admin)->postJson("/api/activity/{$activity->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(409);
    }

    public function test_cannot_update_a_closed_activity(): void
    {
        $admin = Person::factory()->admin()->create();
        $activity = Activity::factory()->create([
            'init_date' => now()->subMonth(),
            'finish_date' => now()->subDay(),
        ]);
        $newParish = Parish::factory()->create();

        $this->actingAs($admin)->putJson("/api/activity/update/{$activity->id}", [
            'parish_ids' => [$newParish->id],
        ])->assertStatus(409);
    }

    public function test_a_survey_can_have_several_activities_in_different_parishes(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $parishA = Parish::factory()->create();
        $parishB = Parish::factory()->create();

        $this->actingAs($admin)->postJson('/api/activity/create', [
            'survey_id' => $survey->id,
            'parish_ids' => [$parishA->id],
            'init_date' => now()->toDateString(),
            'finish_date' => now()->addDay()->toDateString(),
        ])->assertStatus(201);

        $this->actingAs($admin)->postJson('/api/activity/create', [
            'survey_id' => $survey->id,
            'parish_ids' => [$parishB->id],
            'init_date' => now()->addDays(2)->toDateString(),
            'finish_date' => now()->addDays(3)->toDateString(),
        ])->assertStatus(201);

        $this->assertDatabaseCount('activities', 2);
        $this->assertSame(2, $survey->activities()->count());
    }

    public function test_an_activity_can_cover_several_parishes_at_once(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $parishA = Parish::factory()->create();
        $parishB = Parish::factory()->create();
        $parishC = Parish::factory()->create();

        $response = $this->actingAs($admin)->postJson('/api/activity/create', [
            'survey_id' => $survey->id,
            'parish_ids' => [$parishA->id, $parishB->id, $parishC->id],
            'init_date' => now()->toDateString(),
            'finish_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(201);
        $activityId = $response->json('data.id');

        $this->assertDatabaseCount('activity_parish', 3);
        $this->assertSame(3, Activity::find($activityId)->parishes()->count());
    }

    public function test_cannot_unassign_a_pollster_from_a_closed_activity(): void
    {
        $admin = Person::factory()->admin()->create();
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create();

        $this->actingAs($admin)->postJson("/api/activity/{$activity->id}/assign", [
            'person_id' => $pollster->id,
        ])->assertStatus(201);

        $activity->update(['finish_date' => now()->subDay()]);

        $this->actingAs($admin)->deleteJson("/api/activity/{$activity->id}/unassign/{$pollster->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('activity_person', [
            'activity_id' => $activity->id,
            'person_id' => $pollster->id,
            'unassigned_at' => null,
        ]);
    }
}

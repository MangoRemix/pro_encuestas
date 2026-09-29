<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Parish;
use App\Models\Person;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Las tablas del panel deben mostrar el último registro creado primero,
 * incluso cuando varios registros comparten el mismo created_at.
 */
class ListOrderingTest extends TestCase
{
    use RefreshDatabase;

    private function idsIn(array $rows, array $onlyIds): array
    {
        return array_values(array_filter(
            array_column($rows, 'id'),
            fn ($id) => in_array($id, $onlyIds, true),
        ));
    }

    public function test_surveys_list_newest_first_even_with_identical_timestamps(): void
    {
        $admin = Person::factory()->admin()->create();
        $this->freezeTime();
        $ids = Survey::factory()->count(3)->create()->pluck('id')->all();

        $paginated = $this->actingAs($admin)->getJson('/api/survey/show-all')->json('data');
        $all = $this->actingAs($admin)->getJson('/api/survey/show-all?all=true')->json();

        $expected = array_reverse($ids);
        $this->assertSame($expected, $this->idsIn($paginated, $ids));
        $this->assertSame($expected, $this->idsIn($all, $ids));
    }

    public function test_staff_list_newest_first_even_with_identical_timestamps(): void
    {
        $admin = Person::factory()->admin()->create();
        $this->freezeTime();
        $ids = Person::factory()->count(3)->create()->pluck('id')->all();

        $rows = $this->actingAs($admin)->getJson('/api/person/pollster-admin/list')->json('data');

        $this->assertSame(array_reverse($ids), $this->idsIn($rows, $ids));
    }

    public function test_activities_list_newest_created_first_regardless_of_start_date(): void
    {
        $admin = Person::factory()->admin()->create();
        $older = Activity::factory()->create(['init_date' => now()->addDays(5)]);
        $this->travel(1)->minutes();
        $newer = Activity::factory()->create(['init_date' => now()->subDays(5)]);

        $rows = $this->actingAs($admin)->getJson('/api/activity/show-all')->json('data');

        $this->assertSame([$newer->id, $older->id], $this->idsIn($rows, [$older->id, $newer->id]));
    }

    public function test_parishes_list_newest_first(): void
    {
        $admin = Person::factory()->admin()->create();
        $ids = Parish::factory()->count(3)->create()->pluck('id')->all();

        $rows = $this->actingAs($admin)->getJson('/api/parish/show-all')->json();

        $this->assertSame(array_reverse($ids), $this->idsIn($rows, $ids));
    }
}

<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Parish;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        $init = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'survey_id' => Survey::factory(),
            'parish_id' => fn () => Parish::query()->firstOrCreate(['name' => 'ALTAGRACIA'])->id,
            'init_date' => $init,
            'finish_date' => fake()->dateTimeBetween($init, '+1 month'),
            'created_by' => null,
        ];
    }
}

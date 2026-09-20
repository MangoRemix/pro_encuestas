<?php

namespace Database\Factories;

use App\Models\Parish;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition(): array
    {
        $init = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'name' => strtoupper(fake()->unique()->sentence(3)),
            'init_date' => $init,
            'finish_date' => fake()->dateTimeBetween($init, '+1 month'),
            'parish_id' => fn () => Parish::query()->firstOrCreate(['name' => 'ALTAGRACIA'])->id,
        ];
    }
}

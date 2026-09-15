<?php

namespace Database\Factories;

use App\Models\Parish;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Parish>
 */
class ParishFactory extends Factory
{
    protected $model = Parish::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Sex;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sex>
 */
class SexFactory extends Factory
{
    protected $model = Sex::class;

    public function definition(): array
    {
        return [
            'abbreviation' => fake()->unique()->randomElement(['M', 'F']),
            'description' => fake()->randomElement(['Masculino', 'Femenino']),
        ];
    }
}

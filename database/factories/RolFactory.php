<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rol>
 */
class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([Rol::POLLSTER, Rol::RESPONDENT, Rol::ADMIN]),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['name' => Rol::ADMIN]);
    }

    public function pollster(): static
    {
        return $this->state(fn () => ['name' => Rol::POLLSTER]);
    }
}

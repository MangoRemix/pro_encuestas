<?php

namespace Database\Factories;

use App\Models\Parish;
use App\Models\Person;
use App\Models\Rol;
use App\Models\Sex;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Person>
 */
class PersonFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Rol/Sex/Parish son catálogos acotados: se reutiliza la fila existente
            // en vez de crear una nueva por cada Person (evita agotar valores únicos
            // y violar los unique() de la tabla roles cuando el test crea varias personas).
            'sex_id' => fn () => Sex::query()->firstOrCreate(
                ['abbreviation' => 'M'],
                ['description' => 'Masculino']
            )->id,
            'age' => fake()->numberBetween(18, 80),
            'parish_id' => fn () => Parish::query()->firstOrCreate(['name' => 'ALTAGRACIA'])->id,
            'rol_id' => fn () => Rol::query()->firstOrCreate(['name' => Rol::POLLSTER])->id,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'rol_id' => fn () => Rol::query()->firstOrCreate(['name' => Rol::ADMIN])->id,
        ]);
    }

    public function gestorEncuestas(): static
    {
        return $this->state(fn () => [
            'rol_id' => fn () => Rol::query()->firstOrCreate(['name' => Rol::GESTOR_ENCUESTAS])->id,
        ]);
    }
}

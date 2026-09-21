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
        // Por defecto siempre vigente (ni cerrada ni futura): la mayoría de
        // los tests solo necesitan una actividad "normal" y asumible como
        // asignable. Los tests que sí les importa el estado (cerrada,
        // futura, vencida) pasan sus propias init_date/finish_date.
        return [
            'survey_id' => Survey::factory(),
            'parish_id' => fn () => Parish::query()->firstOrCreate(['name' => 'ALTAGRACIA'])->id,
            'init_date' => now()->subDay(),
            'finish_date' => now()->addMonth(),
            'created_by' => null,
        ];
    }
}

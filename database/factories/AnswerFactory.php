<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->words(3, true)),
            'order' => fake()->unique()->numberBetween(1, 1000),
            'question_id' => Question::factory(),
        ];
    }
}

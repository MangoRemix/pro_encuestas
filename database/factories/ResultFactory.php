<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Result>
 */
class ResultFactory extends Factory
{
    protected $model = Result::class;

    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'question_id' => Question::factory(),
            'answer_id' => Answer::factory(),
            'pollster_id' => Person::factory(),
        ];
    }
}

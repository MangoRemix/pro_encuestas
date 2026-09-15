<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->sentence(4)),
            'order' => fake()->unique()->numberBetween(1, 1000),
            'category_id' => Category::factory(),
        ];
    }
}

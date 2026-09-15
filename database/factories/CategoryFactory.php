<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->unique()->words(2, true)),
            'order' => fake()->unique()->numberBetween(1, 1000),
            'survey_id' => Survey::factory(),
        ];
    }
}

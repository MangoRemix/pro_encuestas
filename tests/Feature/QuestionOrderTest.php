<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Person;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_order_is_allowed_across_different_categories(): void
    {
        $admin = Person::factory()->admin()->create();
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        Question::factory()->create(['category_id' => $categoryA->id, 'order' => 1]);

        $response = $this->actingAs($admin)->postJson('/api/question/create', [
            'name' => 'Pregunta en otra categoria',
            'order' => 1,
            'category_id' => $categoryB->id,
        ]);

        $response->assertCreated();
    }

    public function test_duplicate_order_within_the_same_category_is_rejected(): void
    {
        $admin = Person::factory()->admin()->create();
        $category = Category::factory()->create();

        Question::factory()->create(['category_id' => $category->id, 'order' => 1]);

        $response = $this->actingAs($admin)->postJson('/api/question/create', [
            'name' => 'Pregunta duplicada',
            'order' => 1,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(409);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_category_soft_deletes_its_results_instead_of_crashing(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $result = Result::factory()->create(['question_id' => $question->id]);

        $category->delete();

        $this->assertSoftDeleted($category);
        $this->assertSoftDeleted($result);
    }

    public function test_hiding_a_category_cascades_to_its_questions_and_answers(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $category->delete();

        $this->assertSoftDeleted($category);
        $this->assertSoftDeleted($question);
        $this->assertSoftDeleted($answer);
    }

    public function test_hiding_a_question_cascades_to_its_answers(): void
    {
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $question->delete();

        $this->assertSoftDeleted($question);
        $this->assertSoftDeleted($answer);
    }

    public function test_restoring_a_category_restores_its_results(): void
    {
        $category = Category::factory()->create();
        $question = Question::factory()->create(['category_id' => $category->id]);
        $result = Result::factory()->create(['question_id' => $question->id]);

        $category->delete();
        $category->restore();

        $this->assertNotSoftDeleted($category);
        $this->assertNotSoftDeleted($result->fresh());
    }
}

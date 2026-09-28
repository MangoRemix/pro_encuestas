<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_hidden_answers_are_excluded_by_default(): void
    {
        $admin = Person::factory()->admin()->create();
        $question = Question::factory()->create();
        $visible = Answer::factory()->create(['question_id' => $question->id]);
        $hidden = Answer::factory()->create(['question_id' => $question->id]);
        $hidden->delete();

        $response = $this->actingAs($admin)->getJson(
            "/api/answer/show-by-question/{$question->id}"
        );

        $response->assertOk();
        $ids = collect($response->json('answers'))->pluck('id');

        $this->assertTrue($ids->contains($visible->id));
        $this->assertFalse($ids->contains($hidden->id));
    }

    public function test_an_admin_can_request_hidden_answers_with_with_trashed(): void
    {
        $admin = Person::factory()->admin()->create();
        $question = Question::factory()->create();
        $visible = Answer::factory()->create(['question_id' => $question->id]);
        $hidden = Answer::factory()->create(['question_id' => $question->id]);
        $hidden->delete();

        $response = $this->actingAs($admin)->getJson(
            "/api/answer/show-by-question/{$question->id}?with_trashed=1"
        );

        $response->assertOk();
        $ids = collect($response->json('answers'))->pluck('id');

        $this->assertTrue($ids->contains($visible->id));
        $this->assertTrue($ids->contains($hidden->id));
    }

    public function test_a_non_admin_cannot_see_hidden_answers_even_with_with_trashed(): void
    {
        $pollster = Person::factory()->create();
        $question = Question::factory()->create();
        $hidden = Answer::factory()->create(['question_id' => $question->id]);
        $hidden->delete();

        $response = $this->actingAs($pollster)->getJson(
            "/api/answer/show-by-question/{$question->id}?with_trashed=1"
        );

        $response->assertOk();
        $ids = collect($response->json('answers'))->pluck('id');

        $this->assertFalse($ids->contains($hidden->id));
    }

    public function test_a_hidden_answer_can_be_restored(): void
    {
        $admin = Person::factory()->admin()->create();
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);
        $answer->delete();

        $this->actingAs($admin)->patchJson("/api/answer/restore/{$answer->id}")
            ->assertOk();

        $this->assertNotSoftDeleted($answer->fresh());
    }

    public function test_updating_an_answer_no_longer_requires_a_minimum_name_length(): void
    {
        $admin = Person::factory()->admin()->create();
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id, 'order' => 1]);

        $response = $this->actingAs($admin)->putJson("/api/answer/update/{$answer->id}", [
            'name' => 'SI',
            'order' => 1,
        ]);

        $response->assertOk();
        $this->assertSame('SI', $answer->fresh()->name);
    }
}

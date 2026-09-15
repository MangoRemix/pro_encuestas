<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_person_cannot_answer_the_same_question_twice(): void
    {
        $pollster = Person::factory()->create();
        $respondent = Person::factory()->create();
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        Result::factory()->create([
            'person_id' => $respondent->id,
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $pollster->id,
        ]);

        $response = $this->actingAs($pollster)->postJson('/api/result/create', [
            'person_id' => $respondent->id,
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $pollster->id,
        ]);

        $response->assertStatus(409);
        $this->assertSame(1, Result::where('person_id', $respondent->id)
            ->where('question_id', $question->id)
            ->count());
    }

    public function test_an_answer_must_belong_to_the_question(): void
    {
        $pollster = Person::factory()->create();
        $respondent = Person::factory()->create();
        $question = Question::factory()->create();
        $otherQuestion = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $otherQuestion->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/create', [
            'person_id' => $respondent->id,
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $pollster->id,
        ]);

        $response->assertStatus(400);
    }
}

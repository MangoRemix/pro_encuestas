<?php

namespace Tests\Feature;

use App\Jobs\ProcessResultBatch;
use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_a_person_can_mark_several_answers_for_a_multiple_choice_question(): void
    {
        $pollster = Person::factory()->create();
        $respondent = Person::factory()->create();
        $question = Question::factory()->create(['allows_multiple_answers' => true]);
        $answerA = Answer::factory()->create(['question_id' => $question->id]);
        $answerB = Answer::factory()->create(['question_id' => $question->id]);

        $this->actingAs($pollster)->postJson('/api/result/create', [
            'person_id' => $respondent->id,
            'question_id' => $question->id,
            'answer_id' => $answerA->id,
            'pollster_id' => $pollster->id,
        ])->assertStatus(201);

        $this->actingAs($pollster)->postJson('/api/result/create', [
            'person_id' => $respondent->id,
            'question_id' => $question->id,
            'answer_id' => $answerB->id,
            'pollster_id' => $pollster->id,
        ])->assertStatus(201);

        $this->assertSame(2, Result::where('person_id', $respondent->id)
            ->where('question_id', $question->id)
            ->count());
    }

    public function test_a_person_cannot_mark_the_same_answer_twice_even_for_a_multiple_choice_question(): void
    {
        $pollster = Person::factory()->create();
        $respondent = Person::factory()->create();
        $question = Question::factory()->create(['allows_multiple_answers' => true]);
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

    public function test_batch_processing_stores_every_answer_of_a_multiple_choice_question(): void
    {
        $pollster = Person::factory()->create();
        $respondent = Person::factory()->create();
        $question = Question::factory()->create(['allows_multiple_answers' => true]);
        $answerA = Answer::factory()->create(['question_id' => $question->id]);
        $answerB = Answer::factory()->create(['question_id' => $question->id]);

        // El job real se despacha con ->delay(), así que se prueba
        // directamente en vez de esperar a que la cola lo procese.
        (new ProcessResultBatch(
            [[
                ['person_id' => $respondent->id, 'question_id' => $question->id, 'answer_id' => $answerA->id, 'pollster_id' => $pollster->id],
                ['person_id' => $respondent->id, 'question_id' => $question->id, 'answer_id' => $answerB->id, 'pollster_id' => $pollster->id],
            ]],
            (string) Str::uuid(),
        ))->handle();

        $this->assertSame(2, Result::where('person_id', $respondent->id)
            ->where('question_id', $question->id)
            ->count());
    }
}

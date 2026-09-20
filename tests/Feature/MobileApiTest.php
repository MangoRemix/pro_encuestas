<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pollster_can_log_in_via_mobile_and_use_the_token(): void
    {
        $pollster = Person::factory()->create(['password' => bcrypt('Password123')]);

        $response = $this->postJson('/api/mobile/login', [
            'email' => $pollster->email,
            'password' => 'Password123',
            'device_name' => 'test-device',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'token_type', 'user']);

        $token = $response->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/mobile/surveys')
            ->assertOk();
    }

    public function test_a_non_pollster_cannot_log_in_via_mobile(): void
    {
        $admin = Person::factory()->admin()->create(['password' => bcrypt('Password123')]);

        $this->postJson('/api/mobile/login', [
            'email' => $admin->email,
            'password' => 'Password123',
            'device_name' => 'test-device',
        ])->assertStatus(422);
    }

    public function test_a_disabled_pollster_cannot_log_in_via_mobile(): void
    {
        $pollster = Person::factory()->create([
            'password' => bcrypt('Password123'),
            'disabled_at' => now(),
            'disabled_reason' => 'Motivo de prueba',
        ]);

        $this->postJson('/api/mobile/login', [
            'email' => $pollster->email,
            'password' => 'Password123',
            'device_name' => 'test-device',
        ])->assertStatus(422);
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $pollster = Person::factory()->create(['password' => bcrypt('Password123')]);

        $this->postJson('/api/mobile/login', [
            'email' => $pollster->email,
            'password' => 'WrongPassword',
            'device_name' => 'test-device',
        ])->assertStatus(422);
    }

    public function test_logout_revokes_the_token(): void
    {
        $pollster = Person::factory()->create(['password' => bcrypt('Password123')]);

        $token = $this->postJson('/api/mobile/login', [
            'email' => $pollster->email,
            'password' => 'Password123',
            'device_name' => 'test-device',
        ])->json('token');

        $tokenId = explode('|', $token)[0];

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/mobile/logout')
            ->assertOk();

        // Se verifica el efecto real (la fila del token ya no existe) en vez
        // de encadenar una tercera petición simulada: el guard de Sanctum
        // cachea el usuario resuelto dentro del mismo proceso de PHPUnit,
        // así que una petición HTTP real posterior sí se comportaría bien,
        // pero simularla aquí no reflejaría ese cacheo (inexistente en
        // producción, donde cada request es un proceso nuevo).
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    public function test_batch_instance_creates_a_respondent_and_all_its_results_atomically(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-1',
            'survey_id' => $survey->id,
            'pollster_id' => $pollster->id,
            'respondent' => [
                'sex_id' => $pollster->sex_id,
                'age' => 30,
                'parish_id' => $pollster->parish_id,
            ],
            'answers' => [
                ['question_id' => $question->id, 'answer_id' => $answer->id],
            ],
        ]);

        $response->assertStatus(201)->assertJsonStructure(['message', 'server_person_id']);

        $this->assertDatabaseHas('results', [
            'person_id' => $response->json('server_person_id'),
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $pollster->id,
            'client_instance_uuid' => 'uuid-1',
        ]);
    }

    public function test_retrying_the_same_instance_uuid_does_not_duplicate_results(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $payload = [
            'instance_uuid' => 'uuid-retry',
            'survey_id' => $survey->id,
            'pollster_id' => $pollster->id,
            'respondent' => [
                'sex_id' => $pollster->sex_id,
                'age' => 30,
                'parish_id' => $pollster->parish_id,
            ],
            'answers' => [
                ['question_id' => $question->id, 'answer_id' => $answer->id],
            ],
        ];

        $first = $this->actingAs($pollster)->postJson('/api/result/batch-instance', $payload);
        $first->assertStatus(201);

        $second = $this->actingAs($pollster)->postJson('/api/result/batch-instance', $payload);
        $second->assertOk();

        $this->assertSame(
            $first->json('server_person_id'),
            $second->json('server_person_id'),
        );

        $this->assertSame(1, Result::where('client_instance_uuid', 'uuid-retry')->count());
    }

    public function test_batch_instance_supports_multiple_answers_for_a_multiple_choice_question(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $question = Question::factory()->create(['allows_multiple_answers' => true]);
        $answerA = Answer::factory()->create(['question_id' => $question->id]);
        $answerB = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-multi',
            'survey_id' => $survey->id,
            'pollster_id' => $pollster->id,
            'respondent' => [
                'sex_id' => $pollster->sex_id,
                'age' => 30,
                'parish_id' => $pollster->parish_id,
            ],
            'answers' => [
                ['question_id' => $question->id, 'answer_id' => $answerA->id],
                ['question_id' => $question->id, 'answer_id' => $answerB->id],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertSame(
            2,
            Result::where('client_instance_uuid', 'uuid-multi')->count(),
        );
    }

    public function test_batch_instance_rejects_an_answer_that_does_not_belong_to_its_question(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $questionA = Question::factory()->create();
        $questionB = Question::factory()->create();
        $answerFromB = Answer::factory()->create(['question_id' => $questionB->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-invalid',
            'survey_id' => $survey->id,
            'pollster_id' => $pollster->id,
            'respondent' => [
                'sex_id' => $pollster->sex_id,
                'age' => 30,
                'parish_id' => $pollster->parish_id,
            ],
            'answers' => [
                ['question_id' => $questionA->id, 'answer_id' => $answerFromB->id],
            ],
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('results', ['client_instance_uuid' => 'uuid-invalid']);
        $this->assertDatabaseCount('persons', 1); // solo el pollster — no quedó un respondent huérfano
    }
}

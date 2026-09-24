<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Answer;
use App\Models\Parish;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea una actividad vigente para $survey y asigna activamente a
     * $pollster — precondición para poder subir un batch-instance.
     */
    private function activeActivityFor(Survey $survey, Person $pollster): Activity
    {
        $activity = Activity::factory()->create([
            'survey_id' => $survey->id,
            'init_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
        ]);

        $activity->assignedPollsters()->attach($pollster->id, [
            'assigned_by' => $pollster->id,
            'assigned_at' => now(),
        ]);

        return $activity;
    }

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
            ->getJson('/api/mobile/activities')
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
        $activity = $this->activeActivityFor($survey, $pollster);
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-1',
            'survey_id' => $survey->id,
            'activity_id' => $activity->id,
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
            'activity_id' => $activity->id,
            'client_instance_uuid' => 'uuid-1',
        ]);
    }

    public function test_batch_instance_uses_the_activity_parish_regardless_of_what_the_client_sends(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $activity = $this->activeActivityFor($survey, $pollster);
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $otherParish = Parish::factory()->create();

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-parish',
            'survey_id' => $survey->id,
            'activity_id' => $activity->id,
            'pollster_id' => $pollster->id,
            'respondent' => [
                'sex_id' => $pollster->sex_id,
                'age' => 30,
                // Intencionalmente distinto al de la actividad: el servidor
                // debe ignorarlo y usar el de la actividad.
                'parish_id' => $otherParish->id,
            ],
            'answers' => [
                ['question_id' => $question->id, 'answer_id' => $answer->id],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('persons', [
            'id' => $response->json('server_person_id'),
            'parish_id' => $activity->parish_id,
        ]);
    }

    public function test_batch_instance_rejects_an_activity_not_assigned_to_the_pollster(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $activity = Activity::factory()->create([
            'survey_id' => $survey->id,
            'init_date' => now()->subDay(),
            'finish_date' => now()->addDay(),
        ]);
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-unassigned',
            'survey_id' => $survey->id,
            'activity_id' => $activity->id,
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

        $response->assertStatus(403);
        $this->assertDatabaseMissing('results', ['client_instance_uuid' => 'uuid-unassigned']);
    }

    public function test_batch_instance_rejects_an_expired_activity(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $activity = Activity::factory()->create([
            'survey_id' => $survey->id,
            'init_date' => now()->subMonth(),
            'finish_date' => now()->subDay(),
        ]);
        $activity->assignedPollsters()->attach($pollster->id, [
            'assigned_by' => $pollster->id,
            'assigned_at' => now(),
        ]);
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-expired',
            'survey_id' => $survey->id,
            'activity_id' => $activity->id,
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

        $response->assertStatus(409);
    }

    public function test_retrying_the_same_instance_uuid_does_not_duplicate_results(): void
    {
        $pollster = Person::factory()->create();
        $survey = Survey::factory()->create();
        $activity = $this->activeActivityFor($survey, $pollster);
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $payload = [
            'instance_uuid' => 'uuid-retry',
            'survey_id' => $survey->id,
            'activity_id' => $activity->id,
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
        $activity = $this->activeActivityFor($survey, $pollster);
        $question = Question::factory()->create(['allows_multiple_answers' => true]);
        $answerA = Answer::factory()->create(['question_id' => $question->id]);
        $answerB = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-multi',
            'survey_id' => $survey->id,
            'activity_id' => $activity->id,
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
        $activity = $this->activeActivityFor($survey, $pollster);
        $questionA = Question::factory()->create();
        $questionB = Question::factory()->create();
        $answerFromB = Answer::factory()->create(['question_id' => $questionB->id]);

        $response = $this->actingAs($pollster)->postJson('/api/result/batch-instance', [
            'instance_uuid' => 'uuid-invalid',
            'survey_id' => $survey->id,
            'activity_id' => $activity->id,
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

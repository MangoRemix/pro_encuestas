<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Jobs\ProcessResultBatch;
use App\Models\Activity;
use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use App\Services\ResultReportService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class ResultController extends Controller
{
    use ApiResponds;

    protected $reportService;

    public function __construct(ResultReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public static function rules($id = null)
    {
        return [
            'person_id' => 'required|integer|min:1|exists:persons,id',
            'question_id' => 'required|integer|min:1|exists:questions,id',
            'answer_id' => 'required|integer|min:1|exists:answers,id',
            'pollster_id' => 'required|integer|min:1|exists:persons,id',
        ];
    }

    public static function updateRules($id = null)
    {
        return [
            'person_id' => 'integer|min:1|exists:persons,id',
            'question_id' => 'integer|min:1|exists:questions,id',
            'answer_id' => 'integer|min:1|exists:answers,id',
            'pollster_id' => 'integer|min:1|exists:persons,id',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Result::all(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            $validate_answer = Answer::query()->where('id', $request['answer_id'])
                ->where('question_id', $request['question_id'])->first();
            if (! $validate_answer) {
                throw new Exception('Bad Request respuesta no pertenece a pregunta', 400);
            }

            $allowsMultipleAnswers = (bool) Question::query()
                ->where('id', $request['question_id'])
                ->value('allows_multiple_answers');

            $already_answered = Result::query()
                ->where('person_id', $request['person_id'])
                ->where('question_id', $request['question_id'])
                ->when($allowsMultipleAnswers, fn ($query) => $query->where('answer_id', $request['answer_id']))
                ->first();
            if ($already_answered) {
                $message = $allowsMultipleAnswers
                    ? 'Esta persona ya marcó esta respuesta'
                    : 'Esta persona ya respondió esta pregunta';
                throw new Exception($message, 409);
            }

            Result::create($validator->validated());

            return response()->json([
                'message' => 'Resultado creado exitosamente',
            ], 201);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeBatch(Request $request)
    {
        $results = $request->input('results');

        if (! is_array($results) || empty($results)) {
            return response()->json(['error' => 'Formato de datos inválido o vacío'], 422);
        }

        $batchId = (string) Str::uuid();

        ProcessResultBatch::dispatch($results, $batchId)->delay(now()->addSecond(10));

        return response()->json(['batch_id' => $batchId], 202);
    }

    /**
     * Subida atómica de una encuesta completa desde la app móvil offline:
     * crea el encuestado y todas sus respuestas en una sola transacción.
     * Idempotente por instance_uuid — reintentar una subida cortada por
     * falta de conexión no duplica datos.
     */
    public function batchInstance(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'instance_uuid' => 'required|string|max:255',
                'survey_id' => 'required|integer|exists:surveys,id',
                'activity_id' => 'required|integer|exists:activities,id',
                'pollster_id' => 'required|integer|exists:persons,id',
                'respondent' => 'required|array',
                'respondent.sex_id' => 'required|integer|exists:sexes,id',
                'respondent.age' => 'required|integer|min:0|max:120',
                'respondent.parish_id' => 'required|integer|exists:parishes,id',
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.answer_id' => 'required|integer|exists:answers,id',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $validated = $validator->validated();

            $existing = Result::where('client_instance_uuid', $validated['instance_uuid'])->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Esta instancia ya había sido procesada',
                    'server_person_id' => $existing->person_id,
                ], 200);
            }

            $activity = Activity::query()->find($validated['activity_id']);

            if (! $activity || $activity->survey_id != $validated['survey_id']) {
                throw new Exception('La actividad no pertenece a esta encuesta', 422);
            }

            if (! Activity::query()->whereKey($activity->id)->active()->exists()) {
                throw new Exception('Esta actividad ya no está vigente', 409);
            }

            $pollsterAssigned = $activity->activePollsters()
                ->where('persons.id', $validated['pollster_id'])
                ->exists();

            if (! $pollsterAssigned) {
                throw new Exception('El encuestador no está asignado a esta actividad', 403);
            }

            // La parroquia del encuestado es siempre la de la actividad
            // asignada (el servidor es la fuente de verdad, no lo que
            // reporte el cliente).
            $validated['respondent']['parish_id'] = $activity->parish_id;

            $person = DB::transaction(function () use ($validated, $activity) {
                $person = Person::create([
                    'sex_id' => $validated['respondent']['sex_id'],
                    'age' => $validated['respondent']['age'],
                    'parish_id' => $validated['respondent']['parish_id'],
                ]);

                foreach ($validated['answers'] as $answer) {
                    $belongsToQuestion = Answer::where('id', $answer['answer_id'])
                        ->where('question_id', $answer['question_id'])
                        ->exists();

                    if (! $belongsToQuestion) {
                        throw new Exception(
                            "La respuesta {$answer['answer_id']} no pertenece a la pregunta {$answer['question_id']}",
                            400
                        );
                    }

                    Result::create([
                        'person_id' => $person->id,
                        'question_id' => $answer['question_id'],
                        'answer_id' => $answer['answer_id'],
                        'pollster_id' => $validated['pollster_id'],
                        'activity_id' => $activity->id,
                        'client_instance_uuid' => $validated['instance_uuid'],
                    ]);
                }

                return $person;
            });

            return response()->json([
                'message' => 'Encuesta subida con éxito',
                'server_person_id' => $person->id,
            ], 201);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $result = Result::query()->where('id', $id)->first();
            if (! $result) {
                throw new Exception('Not found result register', 404);
            }

            return response()->json($result, 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(), $this->updateRules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $result = Result::query()->find($id);
            if (! $result) {
                throw new Exception('Not found result register', 404);
            }

            $validate_answer = Answer::query()->where('id', $request['answer_id'])
                ->where('question_id', $request['question_id'])->first();
            if (! $validate_answer) {
                throw new Exception('Bad Request respuesta no pertenece a pregunta', 400);
            }

            $result->update($validator->validated());

            return response()->json([
                'message' => 'Actualización exitosa',
            ], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $result = Result::query()->where('id', $id)->first();
            if (! $result) {
                throw new Exception('Not found result register', 404);
            }

            Result::query()->where('id', $id)->delete();

            return response()->json([
                'message' => 'Eliminación exitosa',
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    public function getBatchStatus($batchId)
    {
        $report = Cache::get("batch_status_{$batchId}");

        return response()->json(['report' => $report, 'finished' => ! is_null($report)]);
    }

    public function getRespondentCountByAgeRange(Request $request, int $surveyId)
    {
        $min = $request->query('min');
        $max = $request->query('max');

        $minVal = ($min !== null && $min !== '' && $min !== '*') ? (int) $min : null;
        $maxVal = ($max !== null && $max !== '' && $max !== '*') ? (int) $max : null;

        $count = $this->reportService->getRespondentCountByAgeRange(
            $surveyId,
            $minVal,
            $maxVal,
            $request->query('activity_id'),
            $request->query('from'),
            $request->query('to')
        );

        return response()->json(['count' => $count]);
    }

    public function reportCountAnswersByQuestion(Request $request, int $surveyId)
    {
        try {
            $results = $this->reportService->reportCountAnswersByQuestion(
                $surveyId,
                $request->query('category_id'),
                $request->query('activity_id'),
                $request->query('from'),
                $request->query('to')
            );

            return response()->json($results, 200);
        } catch (Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function newReportStructure(Request $request, $id)
    {

        try {
            $survey = $this->reportService->getSurveyReportStructure(
                $id,
                $request->query('activity_id'),
                $request->query('from'),
                $request->query('to')
            );

            return response()->json($survey, 200);
        } catch (Throwable $th) {
            return response()->json([
                'error' => 'No se pudo generar el reporte.',
                'details' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Actividades de una encuesta, para poblar el selector de "todas las
     * actividades vs. una específica" en Reportes.
     */
    public function getActivitiesForSurvey(int $surveyId)
    {
        try {
            $activities = $this->reportService->getActivitiesForSurvey($surveyId);

            return response()->json($activities, 200);
        } catch (Throwable $th) {
            return response()->json([
                'error' => 'No se pudieron obtener las actividades de la encuesta.',
                'details' => $th->getMessage(),
            ], 500);
        }
    }

    public function getRespondentCountBySex(Request $request, int $surveyId)
    {
        try {
            $results = $this->reportService->getRespondentCountBySex(
                $surveyId,
                $request->query('sex_id'),
                $request->query('activity_id'),
                $request->query('from'),
                $request->query('to')
            );

            return response()->json($results, 200);
        } catch (Throwable $th) {
            return response()->json([
                'error' => 'No se pudo obtener el conteo de encuestados por sexo.',
                'details' => $th->getMessage(),
            ], 500);
        }
    }

    public function getRespondentCountByParish(Request $request, int $surveyId)
    {
        try {
            $results = $this->reportService->getRespondentCountByParish(
                $surveyId,
                $request->query('parish_id'),
                $request->query('activity_id'),
                $request->query('from'),
                $request->query('to')
            );

            return response()->json($results, 200);
        } catch (Throwable $th) {
            return response()->json([
                'error' => 'No se pudo obtener el conteo de encuestados por parroquia.',
                'details' => $th->getMessage(),
            ], 500);
        }
    }

    public function getTopPollsters(Request $request)
    {
        try {
            $results = $this->reportService->getTopPollsters(
                $request->query('survey_id'),
                $request->query('activity_id'),
                $request->query('from'),
                $request->query('to')
            );

            return response()->json($results, 200);
        } catch (Throwable $th) {
            return response()->json([
                'error' => 'No se pudo obtener el ranking de encuestadores.',
                'details' => $th->getMessage(),
            ], 500);
        }
    }
}

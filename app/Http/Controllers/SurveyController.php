<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Concerns\FiltersAndSorts;
use App\Models\Rol;
use App\Models\Survey;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SurveyController extends Controller
{
    use ApiResponds, FiltersAndSorts;

    //
    /**
     * Listar todas las encuestas (activas).
     */
    public static function rules($id = null): array
    {
        return [
            'name' => 'required|string|max:550',
            'init_date' => 'required|date',
            // Si necesitas validar un campo único que ignore el ID actual en el update, usarías el $id aquí
            'finish_date' => 'required|date|after_or_equal:init_date',
        ];
    }

    public static function updateRules($id = null): array
    {
        return [
            'name' => 'string|max:550',
            'init_date' => 'date',
            // Si necesitas validar un campo único que ignore el ID actual en el update, usarías el $id aquí
            'finish_date' => 'date|after_or_equal:init_date',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);
        $surveys = [];

        if ($request->query('all') == 'true') {
            $surveys = Survey::query()
                ->orderBy('created_at', 'DESC')
                ->get();
        } else {
            $query = Survey::query();

            if ($request->boolean('with_trashed') && $request->user()?->rol?->name === Rol::ADMIN) {
                $query->withTrashed();
            }

            $query->addSelect([
                'results_count' => DB::table('results')
                    ->join('persons', 'persons.id', '=', 'results.person_id')
                    ->join('questions', 'questions.id', '=', 'results.question_id')
                    ->join('categories', 'categories.id', '=', 'questions.category_id')
                    ->whereColumn('categories.survey_id', 'surveys.id')
                    ->selectRaw('count(DISTINCT persons.id)'),
            ]);

            $query = $this->applySearch($query, $request->query('search'), ['name']);
            $query = $this->applySort($query, $request, ['name', 'created_at', 'init_date', 'finish_date'], 'created_at');

            $surveys = $query->paginate($perPage);
        }

        return response()->json($surveys, 200);
    }

    /**
     * Guardar una nueva encuesta.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request['name'] = strtoupper($request->name);
            $request['finish_date'] = $request->finish_date.' 23:59:59';

            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $survey = Survey::create($validator->validated());

            return response()->json([
                'message' => 'Encuesta creada con éxito',
                'data' => $survey,
            ], 201); // 201 Created
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }

    /**
     * Mostrar una encuesta específica.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $survey = Survey::query()->where('id', $id)
                ->addSelect([
                    'results_count' => DB::table('results')
                        ->join('persons', 'persons.id', '=', 'results.person_id')
                        ->join('questions', 'questions.id', '=', 'results.question_id')
                        ->join('categories', 'categories.id', '=', 'questions.category_id')
                        ->whereColumn('categories.survey_id', 'surveys.id')
                        ->selectRaw('count(DISTINCT persons.id)'),
                ])->first();

            if (! $survey) {
                throw new Exception('Not found register', 404);
            }

            return response()->json($survey, 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }

    /**
     * Mostrar encuesta completa con categorías, preguntas y respuestas.
     */
    public function showFull(int $id): JsonResponse
    {
        try {
            $survey = Survey::with([
                'categories' => fn ($query) => $query->orderBy('order', 'asc'),
                'categories.questions' => fn ($query) => $query->orderBy('order', 'asc'),
                'categories.questions.answers' => fn ($query) => $query->orderBy('order', 'asc'),
            ])->findOrFail($id);

            return response()->json($survey, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo cargar la estructura de la encuesta.',
                'details' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Actualizar una encuesta existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Los datos ya vienen validados aquí gracias al UpdateSurveyRequest
            $request['name'] = strtoupper($request->name);
            $request['finish_date'] = $request->finish_date.' 23:59:59';

            $validator = Validator::make($request->all(), $this->updateRules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $survey = Survey::query()->find($id);
            if (! $survey) {
                throw new Exception('Not found register', 404);
            }

            $survey->update($validator->validated());

            return response()->json([
                'message' => 'Encuesta actualizada con éxito',
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Eliminación lógica (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        try {

            // Al usar el trait SoftDeletes en el modelo, esto solo llenará la columna deleted_at
            $delete_status = Survey::query()->where('id', $id)->delete();

            if (! $delete_status) {
                throw new Exception('Not found register', 404);
            }

            return response()->json([
                'message' => 'Encuesta eliminada con éxito',
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }

    /**
     * Des-ocultar (restaurar) una encuesta soft-deleted. Solo ADMIN.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $survey = Survey::withTrashed()->findOrFail($id);
            $survey->restore();

            return response()->json(['message' => 'Encuesta restaurada'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th, 404);
        }
    }

    /**
     * Borrado permanente. Solo ADMIN.
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $survey = Survey::withTrashed()->findOrFail($id);
            $survey->forceDelete();

            return response()->json(['message' => 'Encuesta eliminada permanentemente'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th, 404);
        }
    }

    public function getRecent(Request $request): JsonResponse
    {
        try {
            $surveys = Survey::query()
                ->orderBy('created_at', 'DESC')
                ->limit(5)
                ->get();

            return response()->json($surveys, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar las encuestas recientes.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}

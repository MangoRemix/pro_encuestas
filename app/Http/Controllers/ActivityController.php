<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Models\Activity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ActivityController extends Controller
{
    use ApiResponds;

    public static function rules($id = null): array
    {
        return [
            'survey_id' => 'required|integer|exists:surveys,id',
            'parish_id' => 'required|integer|exists:parishes,id',
            'init_date' => 'required|date',
            'finish_date' => 'required|date|after_or_equal:init_date',
        ];
    }

    public static function updateRules($id = null): array
    {
        return [
            'parish_id' => 'integer|exists:parishes,id',
            'init_date' => 'date',
            'finish_date' => 'date|after_or_equal:init_date',
        ];
    }

    /**
     * Listar actividades, opcionalmente filtradas por encuesta, parroquia,
     * encuestador o rango de fechas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activity::query()->with(['survey', 'parish'])->orderByDesc('init_date');

        if ($request->filled('survey_id')) {
            $query->where('survey_id', $request->query('survey_id'));
        }

        if ($request->filled('parish_id')) {
            $query->where('parish_id', $request->query('parish_id'));
        }

        if ($request->filled('pollster_id')) {
            $pollsterId = $request->query('pollster_id');
            $query->whereHas('activePollsters', fn ($q) => $q->where('persons.id', $pollsterId));
        }

        if ($request->filled('from')) {
            $query->whereDate('init_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('finish_date', '<=', $request->query('to'));
        }

        return response()->json($query->get(), 200);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $activity = Activity::with(['survey', 'parish'])->find($id);

            if (! $activity) {
                throw new Exception('Not found register', 404);
            }

            return response()->json($activity, 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $validated = $validator->validated();
            $validated['created_by'] = $request->user()?->id;

            $activity = Activity::create($validated);

            return response()->json([
                'message' => 'Actividad creada con éxito',
                'data' => $activity,
            ], 201);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Editar parroquia/fechas de una actividad. Bloqueado si ya tiene
     * resultados recolectados (cambiar dónde/cuándo se recolectaron datos
     * ya reales falsearía los reportes).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), $this->updateRules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $activity = Activity::query()->find($id);

            if (! $activity) {
                throw new Exception('Not found register', 404);
            }

            if ($activity->hasResults()) {
                throw new Exception('Esta actividad ya tiene datos recolectados; no puede modificarse.', 409);
            }

            $activity->update($validator->validated());

            return response()->json(['message' => 'Actividad actualizada con éxito'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Solo se puede borrar una actividad que nunca llegó a usarse (0
     * resultados y 0 encuestadores asignados) — para corregir un alta hecha
     * por error. Cualquier otro caso se resuelve dejando que la actividad
     * simplemente venza (finish_date en el pasado).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $activity = Activity::query()->find($id);

            if (! $activity) {
                throw new Exception('Not found register', 404);
            }

            if ($activity->hasResults() || $activity->assignedPollsters()->exists()) {
                throw new Exception('Esta actividad ya tiene datos o encuestadores asignados; no puede eliminarse.', 409);
            }

            $activity->delete();

            return response()->json(['message' => 'Actividad eliminada con éxito'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }
}

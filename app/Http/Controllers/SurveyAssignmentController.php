<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Models\Person;
use App\Models\Rol;
use App\Models\Survey;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SurveyAssignmentController extends Controller
{
    use ApiResponds;

    /**
     * Asigna una encuesta a un encuestador. Nunca reescribe una fila
     * existente: cada ciclo de asignación/desasignación es su propia fila,
     * para conservar el historial completo.
     */
    public function assign(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'person_id' => 'required|integer|exists:persons,id',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $person = Person::find($request->person_id);

            if (! $person || $person->rol?->name !== Rol::POLLSTER) {
                throw new Exception('La persona indicada no es un encuestador', 422);
            }

            $alreadyAssigned = $survey->assignedPollsters()
                ->wherePivotNull('unassigned_at')
                ->where('persons.id', $person->id)
                ->exists();

            if ($alreadyAssigned) {
                throw new Exception('Este encuestador ya está asignado a esta encuesta', 409);
            }

            $survey->assignedPollsters()->attach($person->id, [
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ]);

            return response()->json(['message' => 'Encuestador asignado con éxito'], 201);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Desasigna (nunca borra la fila — queda como historial).
     */
    public function unassign(Survey $survey, Person $person): JsonResponse
    {
        try {
            $pivot = $survey->assignedPollsters()
                ->wherePivotNull('unassigned_at')
                ->where('persons.id', $person->id)
                ->first();

            if (! $pivot) {
                throw new Exception('No hay una asignación activa para este encuestador en esta encuesta', 404);
            }

            $survey->assignedPollsters()->updateExistingPivot($person->id, [
                'unassigned_at' => now(),
                'unassigned_by' => request()->user()->id,
            ]);

            return response()->json(['message' => 'Encuestador desasignado con éxito'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Encuestadores asignados a una encuesta. ?history=true incluye también
     * las asignaciones ya finalizadas.
     */
    public function pollsters(Request $request, Survey $survey): JsonResponse
    {
        $query = $request->boolean('history')
            ? $survey->assignedPollsters()
            : $survey->activePollsters();

        return response()->json($query->orderByDesc('survey_person.assigned_at')->get(), 200);
    }

    /**
     * Encuestas asignadas a un encuestador (vista de staff — sin filtrar por
     * jornada vigente, a diferencia de assignedToMe()).
     */
    public function assignedSurveys(Request $request, Person $person): JsonResponse
    {
        $query = $request->boolean('history')
            ? $person->assignedSurveys()
            : $person->activeAssignedSurveys();

        return response()->json($query->orderByDesc('survey_person.assigned_at')->get(), 200);
    }

    /**
     * Encuestas que el usuario autenticado puede descargar/llenar: asignadas
     * activamente Y dentro de su jornada vigente. Consumido por la app móvil.
     */
    public function assignedToMe(Request $request): JsonResponse
    {
        $surveys = $request->user()
            ->activeAssignedSurveys()
            ->active()
            ->get();

        return response()->json($surveys, 200);
    }
}

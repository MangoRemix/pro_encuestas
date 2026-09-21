<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Models\Activity;
use App\Models\Person;
use App\Models\Rol;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ActivityAssignmentController extends Controller
{
    use ApiResponds;

    /**
     * Asigna una actividad a un encuestador. Nunca reescribe una fila
     * existente: cada ciclo de asignación/desasignación es su propia fila,
     * para conservar el historial completo.
     */
    public function assign(Request $request, Activity $activity): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'person_id' => 'required|integer|exists:persons,id',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            if ($activity->isClosed()) {
                throw new Exception('Esta actividad ya finalizó; no se pueden asignar más encuestadores', 409);
            }

            $person = Person::find($request->person_id);

            if (! $person || $person->rol?->name !== Rol::POLLSTER) {
                throw new Exception('La persona indicada no es un encuestador', 422);
            }

            $alreadyAssigned = $activity->assignedPollsters()
                ->wherePivotNull('unassigned_at')
                ->where('persons.id', $person->id)
                ->exists();

            if ($alreadyAssigned) {
                throw new Exception('Este encuestador ya está asignado a esta actividad', 409);
            }

            $activity->assignedPollsters()->attach($person->id, [
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
    public function unassign(Activity $activity, Person $person): JsonResponse
    {
        try {
            $pivot = $activity->assignedPollsters()
                ->wherePivotNull('unassigned_at')
                ->where('persons.id', $person->id)
                ->first();

            if (! $pivot) {
                throw new Exception('No hay una asignación activa para este encuestador en esta actividad', 404);
            }

            $activity->assignedPollsters()->updateExistingPivot($person->id, [
                'unassigned_at' => now(),
                'unassigned_by' => request()->user()->id,
            ]);

            return response()->json(['message' => 'Encuestador desasignado con éxito'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Encuestadores asignados a una actividad. ?history=true incluye
     * también las asignaciones ya finalizadas.
     */
    public function pollsters(Request $request, Activity $activity): JsonResponse
    {
        $query = $request->boolean('history')
            ? $activity->assignedPollsters()
            : $activity->activePollsters();

        return response()->json($query->orderByDesc('activity_person.assigned_at')->get(), 200);
    }

    /**
     * Actividades que el usuario autenticado puede descargar/llenar:
     * asignadas activamente Y dentro de su rango de fechas vigente.
     * Consumido por la app móvil.
     */
    public function assignedToMe(Request $request): JsonResponse
    {
        $activities = $request->user()
            ->activeAssignedActivities()
            ->with(['survey', 'parish'])
            ->active()
            ->get();

        return response()->json($activities, 200);
    }
}

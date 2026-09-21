<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración de datos (no de esquema): traslada la jornada actual de
     * cada encuesta (surveys.parish_id/init_date/finish_date) y el
     * historial de jornadas (survey_runs) a la nueva tabla `activities`, y
     * las asignaciones encuestador<->encuesta (survey_person) a
     * `activity_person`.
     *
     * Nota: el esquema viejo no registraba a qué jornada específica
     * pertenecía cada asignación de encuestador, así que solo se traslada
     * la asignación "vigente" (la de la jornada actual de la encuesta) de
     * forma directa; el historial de asignación por-jornada no se puede
     * reconstruir con certeza.
     *
     * surveys/survey_runs/survey_person NO se tocan ni se eliminan aquí —
     * quedan como respaldo de auditoría (ver la migración siguiente, que sí
     * quita las columnas de surveys que ya quedaron duplicadas aquí).
     */
    public function up(): void
    {
        $now = now();
        $currentActivityIdBySurvey = [];

        DB::table('surveys')
            ->whereNotNull('parish_id')
            ->whereNotNull('init_date')
            ->whereNotNull('finish_date')
            ->select('id', 'parish_id', 'init_date', 'finish_date')
            ->orderBy('id')
            ->chunkById(200, function ($surveys) use (&$currentActivityIdBySurvey, $now) {
                foreach ($surveys as $survey) {
                    $activityId = DB::table('activities')->insertGetId([
                        'survey_id' => $survey->id,
                        'parish_id' => $survey->parish_id,
                        'init_date' => $survey->init_date,
                        'finish_date' => $survey->finish_date,
                        'created_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $currentActivityIdBySurvey[$survey->id] = $activityId;
                }
            });

        if (Schema::hasTable('survey_runs')) {
            DB::table('survey_runs')->orderBy('id')->chunkById(200, function ($runs) use ($now) {
                foreach ($runs as $run) {
                    DB::table('activities')->insert([
                        'survey_id' => $run->survey_id,
                        'parish_id' => $run->parish_id,
                        'init_date' => $run->init_date,
                        'finish_date' => $run->finish_date,
                        'created_by' => $run->created_by,
                        'created_at' => $run->created_at ?? $now,
                        'updated_at' => $run->updated_at ?? $now,
                    ]);
                }
            });
        }

        if (Schema::hasTable('survey_person')) {
            DB::table('survey_person')->orderBy('id')->chunkById(200, function ($rows) use ($currentActivityIdBySurvey, $now) {
                foreach ($rows as $row) {
                    $activityId = $currentActivityIdBySurvey[$row->survey_id] ?? null;

                    if (! $activityId) {
                        continue;
                    }

                    DB::table('activity_person')->insert([
                        'activity_id' => $activityId,
                        'person_id' => $row->person_id,
                        'assigned_by' => $row->assigned_by,
                        'assigned_at' => $row->assigned_at,
                        'unassigned_at' => $row->unassigned_at,
                        'unassigned_by' => $row->unassigned_by,
                        'created_at' => $row->created_at ?? $now,
                        'updated_at' => $row->updated_at ?? $now,
                    ]);
                }
            });
        }
    }

    /**
     * Best-effort: una migración de datos de una sola vía no es realmente
     * reversible. Se usa delete() (no truncate()) para respetar los
     * nullOnDelete de results.activity_id en vez de fallar por FKs.
     */
    public function down(): void
    {
        DB::table('activity_person')->delete();
        DB::table('activities')->delete();
    }
};

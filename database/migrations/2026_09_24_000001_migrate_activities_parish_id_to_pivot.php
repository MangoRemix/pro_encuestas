<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Traslada la parroquia única de cada actividad ya existente a
     * activity_parish, antes de quitar la columna (ver migración
     * siguiente). No se pierde ninguna asignación existente.
     */
    public function up(): void
    {
        $now = now();

        DB::table('activities')
            ->whereNotNull('parish_id')
            ->select('id', 'parish_id')
            ->orderBy('id')
            ->chunkById(200, function ($activities) use ($now) {
                foreach ($activities as $activity) {
                    DB::table('activity_parish')->insert([
                        'activity_id' => $activity->id,
                        'parish_id' => $activity->parish_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    /**
     * Best-effort: una migración de datos de una sola vía no es realmente
     * reversible.
     */
    public function down(): void
    {
        DB::table('activity_parish')->delete();
    }
};

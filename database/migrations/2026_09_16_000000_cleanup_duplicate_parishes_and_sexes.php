<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Limpia registros duplicados generados en parishes y sexes por seeders
     * previos, reasignando primero las referencias en persons al ID canónico.
     */
    public function up(): void
    {
        // 1. Limpieza de Parroquias duplicadas
        $parishGroups = DB::table('parishes')
            ->select(DB::raw('MIN(id) as min_id'), DB::raw('UPPER(TRIM(name)) as clean_name'))
            ->groupBy(DB::raw('UPPER(TRIM(name))'))
            ->get();

        foreach ($parishGroups as $group) {
            $duplicateIds = DB::table('parishes')
                ->whereRaw('UPPER(TRIM(name)) = ?', [$group->clean_name])
                ->where('id', '<>', $group->min_id)
                ->pluck('id');

            if ($duplicateIds->isNotEmpty()) {
                DB::table('persons')
                    ->whereIn('parish_id', $duplicateIds)
                    ->update(['parish_id' => $group->min_id]);

                DB::table('parishes')
                    ->whereIn('id', $duplicateIds)
                    ->delete();
            }
        }

        // 2. Limpieza de Sexos duplicados
        $sexGroups = DB::table('sexes')
            ->select(DB::raw('MIN(id) as min_id'), DB::raw('UPPER(TRIM(abbreviation)) as clean_abbr'))
            ->groupBy(DB::raw('UPPER(TRIM(abbreviation))'))
            ->get();

        foreach ($sexGroups as $group) {
            $duplicateIds = DB::table('sexes')
                ->whereRaw('UPPER(TRIM(abbreviation)) = ?', [$group->clean_abbr])
                ->where('id', '<>', $group->min_id)
                ->pluck('id');

            if ($duplicateIds->isNotEmpty()) {
                DB::table('persons')
                    ->whereIn('sex_id', $duplicateIds)
                    ->update(['sex_id' => $group->min_id]);

                DB::table('sexes')
                    ->whereIn('id', $duplicateIds)
                    ->delete();
            }
        }
    }

    public function down(): void
    {
        // No se restauran duplicados en rollback
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La parroquia de una actividad ya no es un valor único — vive en
     * activity_parish (ver migraciones anteriores, que ya copiaron estos
     * datos antes de llegar aquí).
     */
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // El índice explícito de la migración original (aparte del que
            // trae la propia foreign key) no lo borra dropConstrainedForeignId
            // — hay que quitarlo aparte o SQLite falla al reconstruir la
            // tabla intentando recrear un índice sobre una columna ya
            // eliminada.
            $table->dropIndex('activities_parish_id_index');
            $table->dropConstrainedForeignId('parish_id');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('parish_id')->nullable()->after('survey_id')->constrained()->cascadeOnDelete();
            $table->index('parish_id');
        });
    }
};

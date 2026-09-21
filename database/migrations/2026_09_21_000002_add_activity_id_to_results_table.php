<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A qué actividad de campo pertenece este resultado. Nullable porque
     * los resultados ya existentes no tienen actividad asociada (no se
     * puede reconstruir con certeza a cuál pertenecía cada uno); los nuevos
     * resultados siempre la traen (ver ResultController::batchInstance).
     * Permite a los reportes filtrar por actividad puntual y por rango de
     * fechas de recolección.
     */
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->foreignId('activity_id')->nullable()->after('pollster_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('activity_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distingue preguntas de una sola respuesta (radio) de preguntas de
     * selección múltiple (checkbox) — antes toda pregunta era de una sola
     * respuesta por restricción de base de datos.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('allows_multiple_answers')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('allows_multiple_answers');
        });
    }
};

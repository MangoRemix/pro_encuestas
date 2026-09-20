<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asignación de encuestas a encuestadores. No es un pivot de estado
     * simple: nunca se borra físicamente una fila (unassigned_at marca el
     * fin de una asignación) para conservar el historial completo de quién
     * fue asignado a qué encuesta y cuándo, a través de las distintas
     * jornadas de una misma encuesta.
     */
    public function up(): void
    {
        Schema::create('survey_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('persons')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->foreignId('unassigned_by')->nullable()->constrained('persons')->nullOnDelete();
            $table->timestamps();

            $table->index(['survey_id', 'person_id', 'unassigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_person');
    }
};

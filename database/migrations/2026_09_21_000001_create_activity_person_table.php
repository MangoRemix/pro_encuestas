<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asignación de encuestadores a una actividad concreta (no a la
     * encuesta completa). Mismo patrón que la extinta survey_person: nunca
     * se borra físicamente una fila (unassigned_at marca el fin de una
     * asignación) para conservar el historial completo. Una actividad puede
     * tener varios encuestadores asignados, y un encuestador puede estar
     * asignado a varias actividades (misma encuesta o distintas).
     */
    public function up(): void
    {
        Schema::create('activity_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('persons')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->foreignId('unassigned_by')->nullable()->constrained('persons')->nullOnDelete();
            $table->timestamps();

            $table->index(['activity_id', 'person_id', 'unassigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_person');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historial de jornadas pasadas de una encuesta: cada vez que se
     * reactiva con un nuevo periodo/parroquia, la jornada saliente queda
     * registrada aquí antes de sobreescribir surveys.parish_id/init_date/
     * finish_date (que representan siempre la jornada ACTUAL).
     */
    public function up(): void
    {
        Schema::create('survey_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parish_id')->constrained()->cascadeOnDelete();
            $table->timestamp('init_date');
            $table->timestamp('finish_date');
            $table->foreignId('created_by')->nullable()->constrained('persons')->nullOnDelete();
            $table->timestamps();

            $table->index(['survey_id', 'init_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_runs');
    }
};

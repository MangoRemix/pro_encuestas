<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una "actividad" es la aplicación de UNA encuesta, en UNA parroquia,
     * durante un rango de fechas, por uno o varios encuestadores. La
     * encuesta en sí ya no expira ni tiene parroquia propia (ver migración
     * drop_parish_and_dates_from_surveys_table) — lo que expira es la
     * actividad. Una misma encuesta puede tener muchas actividades (mismo
     * día en distintas parroquias, o distintos días).
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parish_id')->constrained()->cascadeOnDelete();
            $table->timestamp('init_date');
            $table->timestamp('finish_date');
            $table->foreignId('created_by')->nullable()->constrained('persons')->nullOnDelete();
            $table->timestamps();

            $table->index(['survey_id', 'init_date']);
            $table->index('parish_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};

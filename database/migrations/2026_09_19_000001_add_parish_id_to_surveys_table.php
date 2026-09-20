<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Parroquia donde se ejecuta la jornada actual de la encuesta. Nullable:
     * las encuestas ya existentes no tienen parroquia asignada y no se les
     * fuerza un backfill; el formulario sí la exige para encuestas nuevas.
     */
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->foreignId('parish_id')->nullable()->after('name')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parish_id');
        });
    }
};

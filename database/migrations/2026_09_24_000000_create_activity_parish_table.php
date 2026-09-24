<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una actividad puede aplicarse en varias parroquias a la vez (o en
     * "todas" — el frontend resuelve "todas" adjuntando el id de cada
     * parroquia existente en el momento de crear la actividad, así que acá
     * no hace falta un valor especial para "todas"). Reemplaza a
     * activities.parish_id (FK simple) — ver migraciones siguientes.
     */
    public function up(): void
    {
        Schema::create('activity_parish', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parish_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['activity_id', 'parish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_parish');
    }
};

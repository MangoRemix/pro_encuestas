<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            //

            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('parish_id');
            $table->unsignedBigInteger('sex_id');

            $table->foreign('rol_id')->references('id')->on('roles')->default('');
            $table->foreign('parish_id')->references('id')->on('parishes');
            $table->foreign('sex_id')->references('id')->on('sexs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            //
            // 1. Eliminamos las llaves foráneas primero
        $table->dropForeign(['rol_id']);
        $table->dropForeign(['parish_id']);
        $table->dropForeign(['sex_id']);

        // 2. Ahora sí podemos borrar las columnas
        $table->dropColumn(['rol_id', 'parish_id', 'sex_id']);
        });
    }
};

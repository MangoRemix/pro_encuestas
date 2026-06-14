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
        // Renombrar tabla si aún existe como 'sexs'
        if (Schema::hasTable('sexs')) {
        Schema::rename('sexs', 'sexes');
        }

        // Añadir sex_id a persons si no existe
        Schema::table('persons', function (Blueprint $table) {
            if (!Schema::hasColumn('persons', 'sex_id')) {
            $table->foreignId('sex_id')->nullable()->constrained('sexes')->onDelete('set null');
    }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropForeign(['sex_id']);
            $table->dropColumn('sex_id');
        });
        Schema::rename('sexes', 'sexs');
    }
};


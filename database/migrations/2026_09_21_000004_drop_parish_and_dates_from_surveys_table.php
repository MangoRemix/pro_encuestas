<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La encuesta ya no expira ni tiene parroquia propia: eso ahora vive en
     * `activities` (ver migraciones anteriores, que ya copiaron estos datos
     * antes de llegar aquí).
     */
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parish_id');
            $table->dropColumn(['init_date', 'finish_date']);
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->timestamp('init_date')->nullable();
            $table->timestamp('finish_date')->nullable();
            $table->foreignId('parish_id')->nullable()->after('name')->constrained()->nullOnDelete();
        });
    }
};

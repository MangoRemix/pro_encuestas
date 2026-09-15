<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Evita votos duplicados (misma persona respondiendo dos veces la misma
     * pregunta) sin bloquear el histórico soft-deleted.
     */
    public function up(): void
    {
        DB::statement(
            'CREATE UNIQUE INDEX results_person_question_unique ON results (person_id, question_id) WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS results_person_question_unique');
    }
};

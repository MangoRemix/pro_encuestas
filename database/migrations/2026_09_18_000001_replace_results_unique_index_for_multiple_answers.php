<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El índice anterior (person_id, question_id) solo permitía una
     * respuesta por pregunta. Con preguntas de selección múltiple una misma
     * persona puede tener varias filas para la misma pregunta (una por cada
     * respuesta marcada) — el nuevo índice evita marcar la misma respuesta
     * dos veces sin bloquear varias respuestas distintas.
     */
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS results_person_question_unique');

        DB::statement(
            'CREATE UNIQUE INDEX results_person_question_answer_unique ON results (person_id, question_id, answer_id) WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS results_person_question_answer_unique');

        DB::statement(
            'CREATE UNIQUE INDEX results_person_question_unique ON results (person_id, question_id) WHERE deleted_at IS NULL'
        );
    }
};

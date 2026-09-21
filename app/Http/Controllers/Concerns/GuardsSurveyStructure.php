<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Survey;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Una encuesta que ya tiene datos recolectados (Result) no debe permitir
 * que se modifique su estructura (categorías/preguntas/respuestas): eso
 * rompería la interpretación de los reportes históricos ya generados.
 */
trait GuardsSurveyStructure
{
    protected function assertSurveyStructureEditable(?int $surveyId): void
    {
        if (! $surveyId) {
            return;
        }

        if (Survey::query()->find($surveyId)?->hasResults()) {
            throw new Exception(
                'Esta encuesta ya tiene datos recolectados; su estructura no puede modificarse.',
                409
            );
        }
    }

    protected function surveyIdOfCategory(int $categoryId): ?int
    {
        return DB::table('categories')->where('id', $categoryId)->value('survey_id');
    }

    protected function surveyIdOfQuestion(int $questionId): ?int
    {
        return DB::table('questions')
            ->join('categories', 'categories.id', '=', 'questions.category_id')
            ->where('questions.id', $questionId)
            ->value('categories.survey_id');
    }

    protected function surveyIdOfAnswer(int $answerId): ?int
    {
        return DB::table('answers')
            ->join('questions', 'questions.id', '=', 'answers.question_id')
            ->join('categories', 'categories.id', '=', 'questions.category_id')
            ->where('answers.id', $answerId)
            ->value('categories.survey_id');
    }
}

<?php

namespace App\Http\Controllers;

use App\Jobs\ImportSurveyExcelJob;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SurveyImportController extends Controller
{
    public function importFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'init_date' => 'required|date',
            'finish_date' => 'required|date|after:init_date',
        ]);

        $batchId = (string) Str::uuid();
        $path = $request->file('file')->store('temp_imports');

        ImportSurveyExcelJob::dispatch($path, $batchId, $request->init_date, $request->finish_date);

        return response()->json(['batch_id' => $batchId], 202);
    }

    public function processExcelFile(string $fullPath, string $initDate, string $finishDate)
    {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows)) {
            throw new \Exception('El archivo Excel está vacío');
        }

        return DB::transaction(function () use ($rows, $initDate, $finishDate) {
            $surveyName = trim((string) ($rows[0][0] ?? 'Encuesta Importada'));

            $survey = Survey::create([
                'name' => $surveyName !== '' ? $surveyName : 'Encuesta Importada',
                'init_date' => $initDate,
                'finish_date' => $finishDate,
            ]);

            $categoryNames = $rows[2] ?? [];
            $orderCategory = 1;

            foreach ($categoryNames as $colIndex => $categoryName) {
                $categoryName = trim((string) $categoryName);
                if ($categoryName === '') continue;
                $category = Category::create([
                    'name' => $categoryName,
                    'survey_id' => $survey->id,
                    'order' => $orderCategory++,
                ]);

                $this->processCategoryColumn($rows, $colIndex, $category);
            }

            return $survey;
        });
    }

    private function processCategoryColumn(array $rows, int|string $colIndex, Category $category): void
    {
        $currentQuestion = null;
        $orderQuestion = 1;
        $orderAnswer = 1;

        for ($i = 3; $i < count($rows); $i++) {
            $cell = trim((string) ($rows[$i][$colIndex] ?? ''));
            if ($cell === '') continue;

            if (preg_match('/^\d+[\.\s\-]+/', $cell)) {
                $questionText = preg_replace('/^\d+[\.\s\-]+/', '', $cell);
                $currentQuestion = Question::create([
                    'name' => $questionText !== '' ? $questionText : $cell,
                    'category_id' => $category->id,
                    'order' => $orderQuestion++,
                ]);
                $orderAnswer = 1;
            } elseif ($currentQuestion) {
                Answer::create([
                    'name' => preg_replace('/^[•\-\*\s]+/', '', $cell),
                    'question_id' => $currentQuestion->id,
                    'order' => $orderAnswer++,
                ]);
            }
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SurveyImportController extends Controller
{
    public function importFromExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $path = $request->file('file')->store('temp');
        $fullPath = Storage::path($path);

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows)) {
                return response()->json(['error' => 'El archivo Excel está vacío'], 400);
            }

            $result = DB::transaction(function () use ($rows) {
                $surveyName = trim((string) ($rows[0][0] ?? 'Encuesta Importada'));
                
                $survey = Survey::create([
                    'name' => $surveyName !== '' ? $surveyName : 'Encuesta Importada',
                    'init_date' => now(),
                    'finish_date' => now()->addMonth(),
                ]);

                $categoryNames = $rows[2] ?? [];
                $categoriesData = [];
                $orderCategory = 1;

                foreach ($categoryNames as $colIndex => $categoryName) {
                    $categoryName = trim((string) $categoryName);
                    
                    if ($categoryName === '') continue;

                    $category = Category::create([
                        'name' => $categoryName,
                        'survey_id' => $survey->id,
                        'order' => $orderCategory++,
                    ]);

                    $questionsData = $this->processCategoryColumn($rows, $colIndex, $category);

                    $categoriesData[] = [
                        'id' => $category->id,
                        'name' => $category->name,
                        'order' => $category->order,
                        'questions' => $questionsData
                    ];
                }

                return [
                    'id' => $survey->id,
                    'name' => $survey->name,
                    'init_date' => $survey->init_date,
                    'finish_date' => $survey->finish_date,
                    'categories' => $categoriesData
                ];
            });

            return response()->json([
                'message' => 'Encuesta importada exitosamente',
                'result' => $result
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al procesar el archivo',
                'details' => $e->getMessage(),
            ], 500);
        } finally {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    private function processCategoryColumn(array $rows, int|string $colIndex, Category $category): array
    {
        $questions = [];
        $currentQuestion = null;
        $orderQuestion = 1;
        $orderAnswer = 1;

        for ($i = 3; $i < count($rows); $i++) {
            $cell = trim((string) ($rows[$i][$colIndex] ?? ''));
            if ($cell === '') continue;

            $isQuestion = preg_match('/^\d+[\.\s\-]+/', $cell);

            if ($isQuestion) {
                $questionText = preg_replace('/^\d+[\.\s\-]+/', '', $cell);

                $currentQuestion = Question::create([
                    'name' => $questionText !== '' ? $questionText : $cell,
                    'category_id' => $category->id,
                    'order' => $orderQuestion++,
                ]);

                $orderAnswer = 1;

                $questions[] = [
                    'id' => $currentQuestion->id,
                    'name' => $currentQuestion->name,
                    'order' => $currentQuestion->order,
                    'answers' => []
                ];
                continue;
            }

            if ($currentQuestion) {
                $answerName = preg_replace('/^[•\-\*\s]+/', '', $cell);

                $answer = Answer::create([
                    'name' => $answerName,
                    'question_id' => $currentQuestion->id,
                    'order' => $orderAnswer++,
                ]);

                $lastIdx = count($questions) - 1;
                $questions[$lastIdx]['answers'][] = [
                    'id' => $answer->id,
                    'name' => $answer->name,
                    'order' => $answer->order
                ];
            }
        }

        return $questions;
    }
}
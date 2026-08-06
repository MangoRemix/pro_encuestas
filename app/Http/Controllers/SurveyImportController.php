<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SurveyImportController extends Controller
{
    public function importFromExcel(): JsonResponse
    {
        $path = public_path('excel_examples/Encuesta_Percepcion.xlsx');

        if (!file_exists($path)) {
            return response()->json(['error' => 'Archivo no encontrado en la ruta especificada'], 404);
        }

        try {
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows)) {
                return response()->json(['error' => 'El archivo Excel está vacío'], 400);
            }

            $result = DB::transaction(function () use ($rows) {
                // 1. Título de la encuesta (Fila 0, Columna 0)
                $surveyName = trim((string) ($rows[0][0] ?? 'Encuesta Importada'));
                
                $survey = Survey::create([
                    'name' => $surveyName !== '' ? strtoupper($surveyName) : 'Encuesta Importada',
                    'init_date' => now(),
                    'finish_date' => now()->addMonth(),
                ]);

                // 2. Las categorías están exactamente en la Fila 2 (índice 2)
                $categoryNames = $rows[2] ?? [];
                $categoriesData = [];
                $orderCategory = 1;

                foreach ($categoryNames as $colIndex => $categoryName) {
                    $categoryName = trim((string) $categoryName);
                    
                    // Si la celda de la categoría está vacía, saltamos
                    if ($categoryName === '') continue;

                    $category = Category::create([
                        'name' => strtoupper($categoryName),
                        'survey_id' => $survey->id,
                        'order' => $orderCategory++,
                    ]);

                    // Procesar las preguntas y respuestas debajo de esta columna de categoría (desde la fila 3)
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
                'line' => $e->getLine()
            ], 500);
        }
    }

    private function processCategoryColumn(array $rows, int|string $colIndex, Category $category): array
    {
        $questions = [];
        $currentQuestion = null;
        $orderQuestion = 1;
        $orderAnswer = 1;

        // Comenzamos a leer desde la Fila 3 (índice 3) en adelante
        for ($i = 3; $i < count($rows); $i++) {
            $cell = trim((string) ($rows[$i][$colIndex] ?? ''));
            if ($cell === '') continue;

            // En tu Excel, las preguntas inician con un número y un punto (ej: "1. En su opinión...")
            $isQuestion = preg_match('/^\d+[\.\s\-]+/', $cell);

            if ($isQuestion) {
                // Limpiar la numeración inicial para almacenar únicamente el texto de la pregunta
                $questionText = preg_replace('/^\d+[\.\s\-]+/', '', $cell);

                $currentQuestion = Question::create([
                    'name' => $questionText !== '' ? strtoupper($questionText) : $cell,
                    'category_id' => $category->id,
                    'order' => $orderQuestion++,
                ]);

                // Reiniciamos el contador de respuestas para la nueva pregunta
                $orderAnswer = 1;

                $questions[] = [
                    'id' => $currentQuestion->id,
                    'name' => $currentQuestion->name,
                    'order' => $currentQuestion->order,
                    'answers' => []
                ];
                continue;
            }

            // Si ya hay una pregunta activa, las filas subsiguientes son las opciones de respuesta
            if ($currentQuestion) {
                $answerName = preg_replace('/^[•\-\*\s]+/', '', $cell);

                $answer = Answer::create([
                    'name' => strtoupper($answerName),
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
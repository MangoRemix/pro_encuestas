<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessResultBatch;
use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use App\Models\Survey;
use App\Services\ResultReportService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ResultController extends Controller
{
    protected $reportService;

    public function __construct(ResultReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public static function rules($id = null){
        return [
            "person_id" => "required|integer|min:1|exists:persons,id",
            "question_id" => "required|integer|min:1|exists:questions,id",
            "answer_id" => "required|integer|min:1|exists:answers,id",
            "pollster_id" => "required|integer|min:1|exists:persons,id",
        ];
    }

    public static function updateRules($id = null){
        return [
            "person_id" => "integer|min:1|exists:persons,id",
            "question_id" => "integer|min:1|exists:questions,id",
            "answer_id" => "integer|min:1|exists:answers,id",
            "pollster_id" => "integer|min:1|exists:persons,id",
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Result::all(),200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),$this->rules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }
            $validate_answer = Answer::query()->where('id',$request['answer_id'])
            ->where('question_id',$request['question_id'])->first();
            if(!$validate_answer)
                throw new Exception("Bad Request respuesta no pertenece a pregunta", 400);
                
            Result::create($validator->validated());

            return response()->json([
                "message" => "Resultado creado exitosamente"
            ],201);

        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeBatch(Request $request)
    {
            $results = $request->input('results');

            if (!is_array($results) || empty($results)) {
                return response()->json(['error' => 'Formato de datos inválido o vacío'], 422);
            }
            
            $batchId = (string) Str::uuid();

            ProcessResultBatch::dispatch($results, $batchId)->delay(now()->addSecond(10));

            return response()->json(['batch_id' => $batchId], 202);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $result = Result::query()->where('id',$id)->first();
            if(!$result)
                throw new Exception("Not found result register", 404);
                
            return response()->json($result,200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Result $result)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        try {
            $validator = Validator::make($request->all(),$this->updateRules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }
            $validate_answer = Answer::query()->where('id',$request['answer_id'])
            ->where('question_id',$request['question_id'])->first();
            if(!$validate_answer)
                throw new Exception("Bad Request respuesta no pertenece a pregunta", 400);

            Result::query()->where('id',$id)->update($validator->validated());

            return response()->json([
                "message" => "Actualización exitosa"
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $result = Result::query()->where('id',$id)->first();
            if(!$result)
                throw new Exception("Not found result register", 404);

            Result::query()->where('id',$id)->delete();

            return response()->json([
                "message" => "Eliminación exitosa"
            ],200);

        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    public function getBatchStatus($batchId)
    {
        $report = Cache::get("batch_status_{$batchId}");
        return response()->json(['report' => $report, 'finished' => !is_null($report)]);
    }

    public function getRespondentCountByAgeRange(Request $request, int $surveyId)
    {
        $min = $request->query('min');
        $max = $request->query('max');
        
        $minVal = ($min !== null && $min !== '' && $min !== '*') ? (int)$min : null;
        $maxVal = ($max !== null && $max !== '' && $max !== '*') ? (int)$max : null;

        $count = $this->reportService->getRespondentCountByAgeRange($surveyId, $minVal, $maxVal);

        return response()->json(['count' => $count]);
    }

    public function reportCountAnswersByQuestion(Request $request, int $surveyId)
    {
        try {
            $results = $this->reportService->reportCountAnswersByQuestion($surveyId, $request->query('category_id'));
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 500);
        }
    }

    public function newReportStructure($id){
        
        try {
            $survey = $this->reportService->getSurveyReportStructure($id);
            return response()->json($survey, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "No se pudo generar el reporte.",
                "details" => $th->getMessage()
            ], 500);
        }
    }

    public function getRespondentCountBySex(Request $request, int $surveyId)
    {
        try {
            $results = $this->reportService->getRespondentCountBySex($surveyId, $request->query('sex_id'));
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "No se pudo obtener el conteo de encuestados por sexo.",
                "details" => $th->getMessage()
            ], 500);
        }
    }

    public function getRespondentCountByParish(Request $request, int $surveyId)
    {
        try {
            $results = $this->reportService->getRespondentCountByParish($surveyId, $request->query('parish_id'));
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "No se pudo obtener el conteo de encuestados por parroquia.",
                "details" => $th->getMessage()
            ], 500);
        }
    }

    public function getTopPollsters(Request $request)
    {
        try {
            $results = $this->reportService->getTopPollsters($request->query('survey_id'));
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "No se pudo obtener el ranking de encuestadores.",
                "details" => $th->getMessage()
            ], 500);
        }
    }
}


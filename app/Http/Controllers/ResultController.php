<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessResultBatch;
use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use App\Models\Survey;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ResultController extends Controller
{
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
        //
        return response()->json(Result::all(),200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        try {
            //code...

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
            //throw $th;
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
        try {
            //code...
            $result = Result::query()->where('id',$id)->first();
            if(!$result)
                throw new Exception("Not found result register", 404);
                
            return response()->json($result,200);
        } catch (\Throwable $th) {
            //throw $th;
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
        //
        try {
            //code...
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
            //throw $th;
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
        //
        try {
            //code...
            $result = Result::query()->where('id',$id)->first();
            if(!$result)
                throw new Exception("Not found result register", 404);

            Result::query()->where('id',$id)->delete();

            return response()->json([
                "message" => "Eliminación exitosa"
            ],200);

        } catch (\Throwable $th) {
            //throw $th;
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

    public function reportCountAnswersByQuestion(Request $request, int $surveyId)
    {
        try {
            $categoryId = $request->query('category_id');

            $query = Result::query()
                ->join('questions as q', 'q.id', '=', 'results.question_id')
                ->join('categories as c', 'c.id', '=', 'q.category_id')
                ->join('surveys as s', 's.id', '=', 'c.survey_id')
                ->join('answers as a', 'a.id', '=', 'results.answer_id')
                ->select([
                    'results.question_id',
                    'q.name as question_name',
                    'results.answer_id',
                    'a.name as answer_name',
                    'c.name as category_name',
                    DB::raw('count(results.answer_id) as total')
                ])
                ->orderBy('c.id','ASC')
                ->orderBy('total','DESC')
                ->where('s.id', $surveyId);

            if ($categoryId) {
                $query->where('c.id', $categoryId);
            }

            $results = $query->groupBy([
                'results.question_id',
                'q.name',
                'results.answer_id',
                'a.name',
                'c.id'
            ])->get();

            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json(["error" => $th->getMessage()], 500);
        }
    }

    public function newReportStructure($id){
        
        try {
            $survey = Survey::with([
                'categories' => fn($query) => $query->orderBy('order', 'asc'),
                'categories.questions' => fn($query) => $query->orderBy('order', 'asc'),
                'categories.questions.answers' => fn($query) => $query->orderBy('order', 'asc'),
            ])->findOrFail($id);

            // Consulta usando Eloquent con conteo de votos agrupado por respuesta
            $answersCount = Result::query()
                ->join('questions as q', 'q.id', '=', 'results.question_id')
                ->join('categories as c', 'c.id', '=', 'q.category_id')
                ->join('surveys as s', 's.id', '=', 'c.survey_id')
                ->rightJoin('answers as a', 'a.id', '=', 'results.answer_id')
                ->where('s.id', $id)
                ->select([
                    'results.question_id',
                    'results.answer_id',
                    DB::raw('count(results.id) as total_votes')
                ])
                ->groupBy('results.question_id', 'results.answer_id')
                ->get()
                ->keyBy(fn($item) => $item->question_id . '-' . $item->answer_id);

            foreach ($survey->categories as $category) {
                foreach ($category->questions as $question) {
                    foreach ($question->answers as $answer) {
                        $key = $question->id . '-' . $answer->id;
                        $answer->total_votes = isset($answersCount[$key]) ? (int) $answersCount[$key]->total_votes : 0;
                    }
                }
            }
            return response()->json($survey, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "No se pudo generar el reporte.",
                "details" => $th->getMessage()
            ], 500);
        }
    }
}


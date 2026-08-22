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

        $query = Result::query()
            ->join('persons as p', 'results.person_id', '=', 'p.id')
            ->join('age_ranges as ar', 'p.age_range_id', '=', 'ar.id')
            ->join('questions as q', 'results.question_id', '=', 'q.id')
            ->join('categories as c', 'q.category_id', '=', 'c.id')
            ->where('c.survey_id', $surveyId)
            ->distinct('results.person_id');

        if ($min !== null && $min !== '' && $min !== '*') {
            $query->where('ar.init_range', '>=', (int)$min);
        }
        if ($max !== null && $max !== '' && $max !== '*') {
            $query->where('ar.finish_range', '<=', (int)$max);
        }

        return response()->json(['count' => $query->count('results.person_id')]);
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

            $totalRespondent = DB::query()
                ->fromSub(function ($query) use ($id) {
                    $query->from('results as r')
                        ->leftJoin('questions as q', 'q.id', '=', 'r.question_id')
                        ->leftJoin('categories as c', 'c.id', '=', 'q.category_id')
                        ->leftJoin('surveys as s', 's.id', '=', 'c.survey_id')
                        ->where('s.id', $id)
                        ->select('r.person_id')
                        ->groupBy('r.person_id');
                }, 'sub')
                ->count();

            $survey->total_respondent = $totalRespondent;

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

    public function getRespondentCountBySex(Request $request, int $surveyId)
    {
        try {
            $sexId = $request->query('sex_id');

            $sql = "
                SELECT
                    p.sex_id,
                    COUNT(DISTINCT r.person_id) as total_respondents
                FROM results r
                JOIN questions q ON q.id = r.question_id
                JOIN categories c ON c.id = q.category_id
                JOIN persons p ON p.id = r.person_id
                WHERE c.survey_id = :survey_id
            ";

            $bindings = ['survey_id' => $surveyId];

            if (!empty($sexId)) {
                $sql .= " AND p.sex_id = :sex_id";
                $bindings['sex_id'] = (int) $sexId;
            }

            $sql .= " GROUP BY p.sex_id ORDER BY p.sex_id ASC";

            $results = DB::select($sql, $bindings);

            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "error" => "No se pudo obtener el conteo de encuestados por sexo.",
                "details" => $th->getMessage()
            ], 500);
        }
    }
}


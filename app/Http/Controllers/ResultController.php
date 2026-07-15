<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public static function rules($id = null){
        return [
            "person_id" => "required|integer|min:1|exists:persons,id",
            "question_id" => "required|integer|min:1|exists:questions,id",
            "answer_id" => "required|integer|min:1|exists:answers,id",
        ];
    }

    public static function updateRules($id = null){
        return [
            "person_id" => "integer|min:1|exists:persons,id",
            "question_id" => "integer|min:1|exists:questions,id",
            "answer_id" => "integer|min:1|exists:answers,id",
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
            $report = [];
            $now = now();
        foreach ($results as $index => $item) {
            try {
                $item['created_at'] = $now;
                Result::insert($item);
                $report[$index] = 'GUARDADA';
            } catch (\Throwable) {
                $report[$index] = 'FALLIDO';
            }
        }

        return response()->json(['report' => $report], 200);
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
}


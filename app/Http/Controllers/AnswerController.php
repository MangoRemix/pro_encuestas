<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
    public static function rules($id = null){
        return [
            "name"=> "required|string|max:350",
            "order" => "required|integer|min:1",
            "question_id" => "required|integer|min:1",
        ];

    }

    public static function updateRules($id = null){
        return [
            "name"=> "string|max:350",
            "order" => "integer|min:1",
            "question_id" => "integer|min:1",
        ];
        
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $answer = Answer::all();
        return response()->json($answer,200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        try {
            //code...
            $request['name'] = strtoupper($request['name']);
            
            $validator = Validator::make($request->all(),$this->rules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }

            $question = Question::query()->where('id',$request['question_id'])->first();
            
            if(!$question)
                throw new Exception("Not found question register", 404);


            $answer = Answer::create($validator->validate());

            return response()->json([
                "message:" => "Creacion de respuesta exitosa",
                "data" => $answer
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
     * Display the specified resource.
     */
    public function show(int $id)
    {
        //
        try {
            //code...
            $answer = Answer::query()->where('id',$id)->first();

            if(!$answer)
                throw new Exception("Not found answer register", 404);
            
            return response()->json(["answer"=>$answer],200);

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
    public function edit(Answer $answer)
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
            $request['name'] = strtoupper($request['name']);
            
            $validator = Validator::make($request->all(),$this->updateRules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }

            $answer = Answer::query()->where('id',$id)->first();

            if(!$answer)
                throw new Exception("Not found answer register", 404);

            Answer::query()->where('id',$id)->update($validator->validated());
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

            $answer = Answer::query()->where('id',$id)->first();
            if(!$answer)
                throw new Exception("Not found answer register", 404);
                
            Answer::query()->where('id',$id)->delete();

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

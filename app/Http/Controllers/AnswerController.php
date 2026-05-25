<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AnswerController extends Controller
{
    public static function rules($id = null){
        return [
            "name"=> "required|string|max:350",
            "order" => "required|integer|min:1",
            "question_id" => "required|integer|min:1|exists:questions,id",
        ];

    }

    public static function updateRules($id = null){
        return [
            "name"=> "string|max:350",
            "order" => "integer|min:1",
            "question_id" => "integer|min:1|exists:questions,id",
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

            // $question = Question::query()->where('id',$request['question_id'])->first();
            
            // if(!$question)
            //     throw new Exception("Not found question register", 404);

            $exist_answer_order = Answer::query()->where('order',$request['order'])->join('questions','answers.question_id','=','questions.id')->first();
            if($exist_answer_order)
                 throw new Exception("orden de respuesta ya existe", 404);

            $name_answer_question_exist = Answer::query()->where('name',$request->name)->where('question_id',$request->question_id)->first();
            
            if($name_answer_question_exist)
                throw new Exception("Error nombre de categoria en encuesta ya existe", 400);

            Answer::create($validator->validate());

            return response()->json([
                "message:" => "Creacion de respuesta exitosa",
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

            $exist_answer_order = Answer::query()->where('order',$request['order'])->join('questions','answers.question_id','=','questions.id')->first();
            if($exist_answer_order)
                 throw new Exception("orden de respuesta ya existe", 404);

            $name_answer_question_exist = Answer::query()->where('name',$request->name)->where('question_id',$request->question_id)->first();
            
            if($name_answer_question_exist)
                throw new Exception("Error nombre de categoria en encuesta ya existe", 400);

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

    /** FINAL METODOS CRUD */

    public function createMany(Request $request){
        try {
            //code...
            $question_id = $request[0]['question_id'];

            // $question = Question::query()->where('id',$question_id)->first();
            
            // if(!$question)
            //     throw new Exception("Error not found question register", 404);

            $data = [];

            foreach ($request->all() as $categories => $value) {
                # code...
                $value['name'] = strtoupper($value['name']);
                $value['created_at'] = now();
                $value['updated_at'] = now();
                //return response()->json($value);
                array_push($data,$value);
            }
            //return response()->json($data);

            $validator = Validator::make($data, [
                '*.name'      => ['required','string','distinct',Rule::unique('answers','name')->where(function ($query) use ($question_id){
                    $query->where('question_id',$question_id);
                })],
                '*.question_id' => 'required|integer|exists:questions,id|in:'.$question_id,
                '*.order'     => ['required','integer','distinct',Rule::unique('answers','order')->where(function ($query) use ($question_id){
                    $query->where('question_id',$question_id);
                })], // <--- "distinct" hace la magia
                "*.created_at" => 'date',
                "*.updated_at" => 'date',
            ],[
                "*.name.in" => "los name deben ser diferentes",
                "*.question_id.in" => "los question_id's son diferentes"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            Answer::insert($validator->validated());

            return response()->json([
                "message" => "se han creado exitosamente los ".count($validator->validated())
            ],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode(),
                'line' => $th->getLine()
            ]);
        }

    }
}

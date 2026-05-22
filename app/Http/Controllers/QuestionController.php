<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public static function rules($id = null){
        return [
            "name" => 'required|string|max:250',
            "order" => 'required|integer|min:1',
            "category_id" => 'required|integer|min:1'
        ];
    }

    public static function updateRules($id = null){
        return [
            "name" => 'string|max:250',
            "order" => 'integer|min:1',
            "category_id" => 'integer|min:1'
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(Question::all(),200);
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            //code...

            $request['name'] = strtoupper($request['name']);

            $validator = Validator::make($request->all(),$this->rules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }

            $category = Category::query()->where('id',$request['category_id'])->first();

            if(!$category)
                throw new Exception("Not found category register", 404);

            $exist_question_order = Question::query()->where('questions.order',$request['order'])->join('categories','questions.category_id','=','categories.id')->first();
            if($exist_question_order)
                throw new Exception("orden de pregunta ya existe", 404);
            
            Question::create($validator->validated());

            return response()->json([
                "message" => 'Pregunta ha sido creada exitosamente'
            ],201);
                

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question,int $id)
    {
        //
        try {
            //code...
            $question = Question::query()->where('id',$id)->first();
            if(!$question)
                throw new Exception("Not found register", 404);

            return response()->json($question,200);
                
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Question $question)
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
            if($request['category_id']){
                $category = Category::query()->where('id',$request['category_id'])->first();

                if(!$category)
                    throw new Exception("Not found category register", 404);
            }
            $question = Question::query()->where('id',$id)->first();
            
            if(!$question)
                throw new Exception("Not found question register", 404);
                
            $category_id = $request['category_id']?$request['category_id']:$question->category_id;
            
            $exist_question_order = Question::query()->where('questions.order',$category_id)->join('categories','questions.category_id','=','categories.id')->first();
            if($exist_question_order)
                throw new Exception("orden de pregunta ya existe", 404);
            
            $question->update($validator->validated());

            return response()->json([
                "message" => 'Actualización exitosa'
            ],201);
                

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question,int $id)
    {
        //
        try {
            //code...
            $question = Question::query()->where('id',$id)->first();
            if(!$question)
                throw new Exception("Not found register", 404);

            Question::query()->where('id',$id)->delete();

            return response()->json([
                "message" => "Eliminación exitosa"
            ],200);
                
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ]);
        }
    }
}

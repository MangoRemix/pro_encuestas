<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Output\ConsoleOutput;

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
            "name" => 'required|string|max:250|min:5',
            "order" => 'required|integer|min:1',
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

            $name_question_category_exist = Question::query()->where('name',$request->name)->where('category_id',$request->category_id)->first();
            
            if($name_question_category_exist)
                throw new Exception("Error nombre de pregunta en categoría ya existe", 400);
            
            Question::create($validator->validated());

            return response()->json([
                "message" => 'Pregunta ha sido creada exitosamente'
            ],201);
                

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ],$th->getCode());
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

            return response()->json(['question'=>$question],200);
                
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ],$th->getCode());
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

            $request['name'] = strtoupper($request->name);

            $validator = Validator::make($request->all(),$this->updateRules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }
            if($request['category_id']){
                $category = Category::query()->where('id',$request->category_id)->first();

                if(!$category)
                    throw new Exception("Not found category register", 404);
            }
            
            $name_question_category_exist = Question::query()->where('name',$request->name)->where('category_id',$request->category_id)->first();
            
            if($name_question_category_exist)
                throw new Exception("Error nombre de pregunta en categoría ya existe", 400);

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
            ],200);
                

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ],$th->getCode());
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
            $question = Question::select('id','name')->find($id,'id');
            if(!$question)
                throw new Exception("Not found question register", 404);

            $new_name = $question->name . '-delete-' . date('Y-m-d_H-i-s');
            
            $question->update([
                'name' => $new_name
            ]);

            $question->delete();

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

    /** FINAL METODOS CRUD */

    public function createMany(Request $request){
        try {
            //code...
            $category_id = $request[0]['category_id'];

            $category = Category::query()->where('id',$category_id)->first();
            
            if(!$category)
                throw new Exception("Error not found category register", 404);

            $data = [];

            foreach ($request->all() as $questions => $value) {
                # code...
                $value['name'] = strtoupper($value['name']);
                $value['created_at'] = now();
                $value['updated_at'] = now();
                //return response()->json($value);
                array_push($data,$value);
            }

            $validator = Validator::make($data, [
                '*.name'      => ['required','string','distinct',Rule::unique('questions','name')->where(function ($query) use ($category_id){
                    $query->where('category_id',$category_id);
                })],
                '*.category_id' => 'required|integer|in:'.$category_id,
                '*.order'     => ['required','integer','distinct',Rule::unique('questions','order')->where(function ($query) use ($category_id){
                    $query->where('category_id',$category_id)->where('deleted_at',null);
                })], // <--- "distinct" hace la magia
                "*.created_at" => 'date',
                "*.updated_at" => 'date',
            ],[
                "*.name.in" => "los name deben ser diferentes",
                "*.category_id.in" => "los category_id's son diferentes"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El orden de las categorías no puede repetirse.',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            Question::insert($validator->validated());

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

    public function showByCategory(int $id){
        try {
            //code...
            $questions = Question::query()->where('category_id',$id)->orderBy('order','asc')->get();

            return response()->json([
                "questions" => $questions
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

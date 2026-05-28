<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Survey;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Output\ConsoleOutput;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public static function rules($id = null): array {

        return [
            "name"=> 'required|string|max:350',
            "order"=> 'required|integer|min:1',
            "survey_id"=> 'required|integer|exists:surveys,id'
        ];
        
    }

    public static function updateRules($id = null): array {

        return [
            "name"=> 'string|max:350',
            "order"=> 'integer|min:1',
            "survey_id"=> 'integer|exists:surveys,id'
        ];
        
    }

    public function index(): JsonResponse
    {
        //
        $categories = Category::all();
        return response()->json($categories, 200);
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
    public function store(Request $request): JsonResponse
    {

        try {
            $request['name'] = strtoupper($request['name']);

            $validator = Validator::make($request->all(),$this->rules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }

            // $survey = Survey::query()->where('id',$request['survey_id'])->first();

            // if(!$survey)
            //     throw new Exception("Not found survey_id", 404);
            
            
            $exist_category_order = Category::query()->where('order',$request->order)->where('survey_id',$request->survey_id)
            ->join('surveys','categories.survey_id','=','surveys.id')
            ->first();
            
            if($exist_category_order){
                
                throw new Exception("orden de categoria ya existe", 404);
            }

            $name_category_survey_exist = Category::query()->where('name',$request->name)->where('survey_id',$request->survey_id)->first();
            
            if($name_category_survey_exist)
                throw new Exception("Error nombre de categoria en encuesta ya existe", 400);
            
            $category = Category::create($validator->validated());
            //$ordered_categories = Category::query()->where('survey_id',$request->survey_id)->orderBy('categories.order','asc')->get();
            
            return response()->json([
                "category"=> $category,
                "message" => "Categoria creada exitosamente",
                
            ],201);

        } catch (\Throwable $th) {
            $statusCode = ($th->getCode() >= 400 && $th->getCode() < 600) ? $th->getCode() : 500;
            return response()->json([
                
                "error" => $th->getMessage(),
                "code" => $statusCode
            ],$statusCode);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        //
        try {
            //code...
            $category = Category::query()->where('id',$id)->first();
            if(!$category)
                throw new Exception("Not found register", 404);
                
            return response()->json([
                'category' => $category
            ],200);

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
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,int $id): JsonResponse
    {
        //
        try {

            $validator = Validator::make($request->all(),$this->updateRules());

            if($validator->fails()){
                return response()->json($validator->errors(), 422);
            }
            $category = Category::query()->where('id',$id)->first();
            if($category->order != $request->order){
                $exist_category_order = Category::query()->where('order',$request['order'])->join('surveys','categories.survey_id','=','surveys.id')->first();
                if($exist_category_order)
                    throw new Exception("orden de categoría ya existe", 404);
            }

            $name_category_survey_exist = Category::query()->where('name',$request->name)->where('survey_id',$request->survey_id)->first();
            
            if($name_category_survey_exist)
                throw new Exception("Error nombre de categoría en encuesta ya existe", 400);

            $category = Category::query()->where('id',$id)->update($validator->validated());

            if(!$category)
                throw new Exception("Error durante actualización", 404);

            return response()->json([
                "message"=> "Actualización exitosa"
            ],200);

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
    public function destroy(int $id)
    {
        //
        try {
            //code...
            $category = Category::query()->where('id',$id)->first();
            
            if(!$category)
                throw new Exception("Not found register", 404);
                
            Category::query()->where('id',$id)->delete();

            return response()->json([
                "message" => "eliminación exitosa"
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
            $survey_id = $request[0]['survey_id'];

            $survey = Survey::query()->where('id',$survey_id)->first();
            
            if(!$survey)
                throw new Exception("Error not found survey register", 404);

            $data = [];

            foreach ($request->all() as $categories => $value) {
                # code...
                $value['name'] = strtoupper($value['name']);
                $value['created_at'] = now();
                $value['updated_at'] = now();
                //return response()->json($value);
                array_push($data,$value);
            }

            $validator = Validator::make($data, [
                '*.name'      => ['required','string','distinct',Rule::unique('categories','name')->where(function ($query) use ($survey_id){
                    $query->where('survey_id',$survey_id);
                })],
                '*.survey_id' => 'required|integer|in:'.$survey_id,
                '*.order'     => ['required','integer','distinct',Rule::unique('categories','order')->where(function ($query) use ($survey_id){
                    $query->where('survey_id',$survey_id);
                })], // <--- "distinct" hace la magia
                "*.created_at" => 'date',
                "*.updated_at" => 'date',
            ],[
                "*.name.in" => "los name deben ser diferentes",
                "*.survey_id.in" => "los survey_id's son diferentes"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            Category::insert($validator->validated());

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

    public function showBySurvey(int $id){

        try {
            //code...
            $categories = Category::query()->where('survey_id',$id)->get();

            return response()->json($categories);
            
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

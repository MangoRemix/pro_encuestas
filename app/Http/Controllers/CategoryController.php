<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Survey;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public static function rules($id = null): array {

        return [
            "name"=> 'required|string|max:350',
            "order"=> 'required|integer|min:1',
            "survey_id"=> 'required|integer'
        ];
        
    }

    public static function updateRules($id = null): array {

        return [
            "name"=> 'string|max:350',
            "order"=> 'integer|min:1',
            "survey_id"=> 'integer'
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

            $survey = Survey::query()->where('id',$request['survey_id'])->first();

            if(!$survey)
                throw new Exception("Not found survey_id", 404);
            
            
            $exist_category_order = Category::query()->where('order',$request['order'])->join('surveys','categories.survey_id','=','surveys.id')->first();
            if($exist_category_order)
                 throw new Exception("orden de categoria ya existe", 404);
            
            Category::create($validator->validated());
            //$ordered_categories = Category::query()->where('survey_id',$request->survey_id)->orderBy('categories.order','asc')->get();
            
            
            return response()->json([
                "message" => "Categoria creada exitosamente",
                "request" => $validator->validated()
                //"exist" => $exist_category_order
                //"ordered_categories" => $ordered_categories,
            ],201);

        } catch (\Throwable $th) {

            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
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

            $exist_category_order = Category::query()->where('order',$request['order'])->join('surveys','categories.survey_id','=','surveys.id')->first();
            if($exist_category_order)
                 throw new Exception("orden de categoria ya existe", 404);

            $category = Category::query()->where('id',$id)->update($validator->validated());

            if(!$category)
                throw new Exception("Error durante actualización", 404);

            return response()->json([
                "message"=> "Actualización exitosa"
            ]);

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
}

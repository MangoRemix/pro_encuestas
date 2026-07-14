<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Exception;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    //
    /**
     * Listar todas las encuestas (activas).
     */

    public static function rules($id = null): array
    {
        return [
            'name'        => 'required|string|max:550',
            'init_date'   => 'required|date',
            // Si necesitas validar un campo único que ignore el ID actual en el update, usarías el $id aquí
            'finish_date' => 'required|date|after_or_equal:init_date',
        ];
    }

    public static function updateRules($id = null): array
    {
        return [
            'name'        => 'string|max:550',
            'init_date'   => 'date',
            // Si necesitas validar un campo único que ignore el ID actual en el update, usarías el $id aquí
            'finish_date' => 'date|after_or_equal:init_date',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        //$age_range = intval($request->query('age_range'));
        
        $perPage = $request->query('per_page', 10);
        $surveys = [];

        if($request->query('all')=='true'){
            $surveys = Survey::query()
            ->orderBy('created_at', 'DESC')->get();
        }else{
            
            $surveys = Survey::query()
            ->orderBy('created_at', 'DESC')
            ->addSelect([
                'results_count' => DB::table('results')
                    ->join('persons', 'persons.id', '=', 'results.person_id')
                    ->join('age_ranges', 'persons.age_range_id', '=', 'age_ranges.id')
                    ->join('questions', 'questions.id', '=', 'results.question_id')
                    ->join('categories', 'categories.id', '=', 'questions.category_id')
                    ->whereColumn('categories.survey_id', 'surveys.id')
                    //->when($age_range, fn($query) => $query->where('age_ranges.id', $age_range))
                    ->selectRaw('count(DISTINCT persons.id)')
            ])
            ->paginate($perPage);
        }
        
        
        return response()->json($surveys, 200);
    }

    /**
     * Guardar una nueva encuesta.
     */
    public function store(Request $request): JsonResponse
    {
        try{
            
            $request['name'] = strtoupper($request->name);
            $request['finish_date'] = $request->finish_date.' 23:59:59';

            $validator = Validator::make($request->all(), $this->rules());
            
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $survey = Survey::create($validator->validated());

            return response()->json([
                'message' => 'Encuesta creada con éxito',
                'data'    => $survey
            ], 201); // 201 Created
        }
        catch(Exception $e){
            return response()->json([
                "Error" => $e->getMessage()
            ]);

        }
        
    }

    /**
     * Mostrar una encuesta específica.
     */
    public function show(int $id, Request $request): JsonResponse
    {   
        try {
            $age_range = intval($request->query('age_range'));
            //code...
            $survey = Survey::query()->where('id',$id)
            ->addSelect([
            'results_count' => DB::table('results')
                ->join('persons', 'persons.id', '=', 'results.person_id')
                ->join('age_ranges', 'persons.age_range_id', '=', 'age_ranges.id')
                ->join('questions', 'questions.id', '=', 'results.question_id')
                ->join('categories', 'categories.id', '=', 'questions.category_id')
                ->whereColumn('categories.survey_id', 'surveys.id')
                ->when($age_range, fn($query) => $query->where('age_ranges.id', $age_range))
                ->selectRaw('count(DISTINCT persons.id)')
            ])->first();

            if(!$survey){
                throw new Exception("Not found register", 404);    
            }
            
            return response()->json($survey, 200);

        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
        
    }

    /**
     * Mostrar encuesta completa con categorías, preguntas y respuestas.
     */
    public function showFull(int $id): JsonResponse
    {
        try {
            $survey = Survey::with([
                'categories' => fn($query) => $query->orderBy('order', 'asc'),
                'categories.questions' => fn($query) => $query->orderBy('order', 'asc'),
                'categories.questions.answers' => fn($query) => $query->orderBy('order', 'asc'),
            ])->findOrFail($id);
            return response()->json($survey, 200);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "No se pudo cargar la estructura de la encuesta.",
                "details" => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar una encuesta existente.
     */
    public function update(Request $request,int $id): JsonResponse
    {
        try {
            // Los datos ya vienen validados aquí gracias al UpdateSurveyRequest    
            $request['name'] = strtoupper($request->name);
            $request['finish_date'] = $request->finish_date.' 23:59:59';
            
            $validator = Validator::make($request->all(), $this->updateRules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $update = Survey::query()->where('id',$id)->update($validator->validated());
            if(!$update)
                throw new Exception("Not found register", 404);
                
            return response()->json([
                'message' => 'Encuesta actualizada con éxito',
            ], 200);

        } catch (Exception $e) {
            // Code to handle the error
            return response()->json([
                "error" => $e->getMessage(),
                "code" => $e->getCode()
            ]);
            
        }
    }

    /**
     * Eliminación lógica (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        try {

            // Al usar el trait SoftDeletes en el modelo, esto solo llenará la columna deleted_at
            $delete_status = Survey::query()->where('id',$id)->delete();
            
            if(!$delete_status)
                throw new Exception("Not found register", 404);

            return response()->json([
                'message' => 'Encuesta eliminada con éxito',
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
        
    }
}


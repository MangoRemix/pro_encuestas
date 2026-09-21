<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Concerns\FiltersAndSorts;
use App\Http\Controllers\Concerns\GuardsSurveyStructure;
use App\Models\Category;
use App\Models\Rol;
use App\Models\Survey;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class CategoryController extends Controller
{
    use ApiResponds, FiltersAndSorts, GuardsSurveyStructure;

    /**
     * Display a listing of the resource.
     */
    public static function rules($id = null): array
    {

        return [
            'name' => 'required|string|max:350',
            'order' => 'required|integer|min:1',
            'survey_id' => 'required|integer|exists:surveys,id',
        ];

    }

    public static function updateRules($id = null): array
    {

        return [
            'name' => 'string|max:350',
            'order' => 'integer|min:1',
            'survey_id' => 'integer|exists:surveys,id',
        ];

    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);

        $query = Category::query();

        if ($request->boolean('with_trashed') && $request->user()?->rol?->name === Rol::ADMIN) {
            $query->withTrashed();
        }

        $query = $this->applySearch($query, $request->query('search'), ['name']);
        $query = $this->applySort($query, $request, ['name', 'order', 'created_at'], 'created_at');

        $categories = $query->paginate($perPage);

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

            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $this->assertSurveyStructureEditable($request->survey_id);

            $exist_category_order = Category::query()->where('order', $request->order)->where('survey_id', $request->survey_id)
                ->join('surveys', 'categories.survey_id', '=', 'surveys.id')
                ->first();

            if ($exist_category_order) {

                throw new Exception('orden de categoria ya existe', 404);
            }

            $name_category_survey_exist = Category::query()->where('name', $request->name)->where('survey_id', $request->survey_id)->first();

            if ($name_category_survey_exist) {
                throw new Exception('Error nombre de categoria en encuesta ya existe', 400);
            }

            $category = Category::create($validator->validated());
            // $ordered_categories = Category::query()->where('survey_id',$request->survey_id)->orderBy('categories.order','asc')->get();

            return response()->json([
                'category' => $category,
                'message' => 'Categoria creada exitosamente',

            ], 201);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        //
        try {
            // code...
            $category = Category::query()->where('id', $id)->first();
            if (! $category) {
                throw new Exception('Not found register', 404);
            }

            return response()->json([
                'category' => $category,
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
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
    public function update(Request $request, int $id): JsonResponse
    {
        //
        try {

            $validator = Validator::make($request->all(), $this->updateRules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $category = Category::query()->where('id', $id)->first();
            if (! $category) {
                throw new Exception('Not found register', 404);
            }

            $surveyId = $request->survey_id ?? $category->survey_id;

            $this->assertSurveyStructureEditable($surveyId);

            if ($request->filled('order') && $category->order != $request->order) {
                $exist_category_order = Category::query()
                    ->where('order', $request['order'])
                    ->where('survey_id', $surveyId)
                    ->where('id', '!=', $id)
                    ->first();
                if ($exist_category_order) {
                    throw new Exception('orden de categoría ya existe', 409);
                }
            }

            if ($request->filled('name')) {
                $name_category_survey_exist = Category::query()
                    ->where('name', $request->name)
                    ->where('survey_id', $surveyId)
                    ->where('id', '!=', $id)
                    ->first();

                if ($name_category_survey_exist) {
                    throw new Exception('Error nombre de categoría en encuesta ya existe', 400);
                }
            }

            $category->update($validator->validated());

            return response()->json([
                'message' => 'Actualización exitosa',
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        //
        try {
            // code...
            $category = Category::query()->where('id', $id)->first();

            if (! $category) {
                throw new Exception('Not found register', 404);
            }

            $this->assertSurveyStructureEditable($category->survey_id);

            Category::query()->where('id', $id)->delete();

            return response()->json([
                'message' => 'eliminación exitosa',
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }

    /** FINAL METODOS CRUD */
    public function createMany(Request $request)
    {
        try {
            // code...
            $survey_id = $request[0]['survey_id'];

            $survey = Survey::query()->where('id', $survey_id)->first();

            if (! $survey) {
                throw new Exception('Error not found survey register', 404);
            }

            $this->assertSurveyStructureEditable($survey_id);

            $data = [];

            foreach ($request->all() as $categories => $value) {
                // code...
                $value['name'] = strtoupper($value['name']);
                $value['created_at'] = now();
                $value['updated_at'] = now();
                // return response()->json($value);
                array_push($data, $value);
            }

            $validator = Validator::make($data, [
                '*.name' => ['required', 'string', 'distinct', Rule::unique('categories', 'name')->where(function ($query) use ($survey_id) {
                    $query->where('survey_id', $survey_id)->whereNull('deleted_at');
                })],
                '*.survey_id' => 'required|integer|in:'.$survey_id,
                '*.order' => ['required', 'integer', 'distinct', Rule::unique('categories', 'order')->where(function ($query) use ($survey_id) {
                    $query->where('survey_id', $survey_id)->where('deleted_at', null);
                })], // <--- "distinct" hace la magia
                '*.created_at' => 'date',
                '*.updated_at' => 'date',
            ], [
                '*.name.in' => 'los name deben ser diferentes',
                '*.survey_id.in' => "los survey_id's son diferentes",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            Category::insert($validator->validated());

            return response()->json([
                'message' => 'se han creado exitosamente los '.count($validator->validated()),
            ], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }

    /**
     * Des-ocultar (restaurar) una categoría soft-deleted. Solo ADMIN.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $category = Category::withTrashed()->findOrFail($id);
            $category->restore();

            return response()->json(['message' => 'Categoría restaurada'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th, 404);
        }
    }

    /**
     * Borrado permanente. Solo ADMIN.
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $category = Category::withTrashed()->findOrFail($id);
            $category->forceDelete();

            return response()->json(['message' => 'Categoría eliminada permanentemente'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th, 404);
        }
    }

    /**
     * Reordenamiento masivo (drag-and-drop). Todas las categorías enviadas
     * deben pertenecer a la misma encuesta, para no corromper el orden de
     * otra encuesta con una petición equivocada.
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer|exists:categories,id',
                'items.*.order' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $items = $validator->validated()['items'];
            $ids = array_column($items, 'id');

            $surveyIdsCount = Category::query()->whereIn('id', $ids)->distinct('survey_id')->count('survey_id');

            if ($surveyIdsCount > 1) {
                throw new Exception('Todas las categorías a reordenar deben pertenecer a la misma encuesta', 422);
            }

            $this->assertSurveyStructureEditable(
                Category::query()->whereIn('id', $ids)->value('survey_id')
            );

            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    Category::query()->where('id', $item['id'])->update(['order' => $item['order']]);
                }
            });

            return response()->json(['message' => 'Orden actualizado con éxito'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    public function showBySurvey(int $id, Request $request)
    {

        try {
            // code...
            $query = Category::query()->where('survey_id', $id);

            if ($request->boolean('with_trashed') && $request->user()?->rol?->name === Rol::ADMIN) {
                $query->withTrashed();
            }

            $categories = $query->get();

            return response()->json($categories);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }
}

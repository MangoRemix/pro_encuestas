<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Concerns\FiltersAndSorts;
use App\Http\Controllers\Concerns\GuardsSurveyStructure;
use App\Models\Category;
use App\Models\Question;
use App\Models\Rol;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class QuestionController extends Controller
{
    use ApiResponds, FiltersAndSorts, GuardsSurveyStructure;

    public static function rules($id = null)
    {
        return [
            'name' => 'required|string|max:250',
            'order' => 'required|integer|min:1',
            'category_id' => 'required|integer|min:1',
            'allows_multiple_answers' => 'boolean',
        ];
    }

    public static function updateRules($id = null)
    {
        return [
            'name' => 'required|string|max:250|min:5',
            'order' => 'required|integer|min:1',
            'category_id' => 'integer|min:1',
            'allows_multiple_answers' => 'boolean',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 10);

        $query = Question::query();

        if ($request->boolean('with_trashed') && $request->user()?->rol?->name === Rol::ADMIN) {
            $query->withTrashed();
        }

        $query = $this->applySearch($query, $request->query('search'), ['name']);
        $query = $this->applySort($query, $request, ['name', 'order', 'created_at'], 'created_at');

        $questions = $query->paginate($perPage);

        return response()->json($questions, 200);
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
            // code...

            $request['name'] = strtoupper($request['name']);

            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $category = Category::query()->where('id', $request['category_id'])->first();

            if (! $category) {
                throw new Exception('Not found category register', 404);
            }

            $this->assertSurveyStructureEditable($category->survey_id);

            $exist_question_order = Question::query()
                ->where('order', $request['order'])
                ->where('category_id', $request['category_id'])
                ->first();
            if ($exist_question_order) {
                throw new Exception('orden de pregunta ya existe', 409);
            }

            $name_question_category_exist = Question::query()->where('name', $request->name)->where('category_id', $request->category_id)->first();

            if ($name_question_category_exist) {
                throw new Exception('Error nombre de pregunta en categoría ya existe', 400);
            }

            Question::create($validator->validated());

            return response()->json([
                'message' => 'Pregunta ha sido creada exitosamente',
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
            $question = Question::query()->where('id', $id)->first();
            if (! $question) {
                throw new Exception('Not found register', 404);
            }

            return response()->json(['question' => $question], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        //
        try {
            // code...

            $request['name'] = strtoupper($request->name);

            $validator = Validator::make($request->all(), $this->updateRules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $question = Question::query()->where('id', $id)->first();

            if (! $question) {
                throw new Exception('Not found question register', 404);
            }

            $categoryId = $request->filled('category_id') ? $request->category_id : $question->category_id;

            if ($request->filled('category_id')) {
                $category = Category::query()->where('id', $categoryId)->first();

                if (! $category) {
                    throw new Exception('Not found category register', 404);
                }
            }

            $this->assertSurveyStructureEditable($this->surveyIdOfCategory($categoryId));

            $name_question_category_exist = Question::query()
                ->where('name', $request->name)
                ->where('category_id', $categoryId)
                ->where('id', '!=', $id)
                ->first();

            if ($name_question_category_exist) {
                throw new Exception('Error nombre de pregunta en categoría ya existe', 400);
            }

            if ($question->order != $request->order) {
                $exist_question_order = Question::query()
                    ->where('order', $request->order)
                    ->where('category_id', $categoryId)
                    ->where('id', '!=', $id)
                    ->first();
                if ($exist_question_order) {
                    throw new Exception('orden de pregunta ya existe', 409);
                }
            }

            $question->update($validator->validated());

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
            $question = Question::select('id', 'name', 'category_id')->find($id);
            if (! $question) {
                throw new Exception('Not found question register', 404);
            }

            $this->assertSurveyStructureEditable($this->surveyIdOfCategory($question->category_id));

            $question->delete();

            return response()->json([
                'message' => 'Eliminación exitosa',
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
            $category_id = $request[0]['category_id'];

            $category = Category::query()->where('id', $category_id)->first();

            if (! $category) {
                throw new Exception('Error not found category register', 404);
            }

            $this->assertSurveyStructureEditable($category->survey_id);

            $data = [];

            foreach ($request->all() as $questions => $value) {
                // code...
                $value['name'] = strtoupper($value['name']);
                $value['allows_multiple_answers'] = $value['allows_multiple_answers'] ?? false;
                $value['created_at'] = now();
                $value['updated_at'] = now();
                // return response()->json($value);
                array_push($data, $value);
            }

            $validator = Validator::make($data, [
                '*.name' => ['required', 'string', 'distinct', Rule::unique('questions', 'name')->where(function ($query) use ($category_id) {
                    $query->where('category_id', $category_id)->whereNull('deleted_at');
                })],
                '*.category_id' => 'required|integer|in:'.$category_id,
                '*.order' => ['required', 'integer', 'distinct', Rule::unique('questions', 'order')->where(function ($query) use ($category_id) {
                    $query->where('category_id', $category_id)->where('deleted_at', null);
                })], // <--- "distinct" hace la magia
                '*.allows_multiple_answers' => 'boolean',
                '*.created_at' => 'date',
                '*.updated_at' => 'date',
            ], [
                '*.name.in' => 'los name deben ser diferentes',
                '*.category_id.in' => "los category_id's son diferentes",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El orden de las categorías no puede repetirse.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            Question::insert($validator->validated());

            return response()->json([
                'message' => 'se han creado exitosamente los '.count($validator->validated()),
            ], 201);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }

    /**
     * Des-ocultar (restaurar) una pregunta soft-deleted. Solo ADMIN.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $question = Question::withTrashed()->findOrFail($id);
            $question->restore();

            return response()->json(['message' => 'Pregunta restaurada'], 200);
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
            $question = Question::withTrashed()->findOrFail($id);
            $question->forceDelete();

            return response()->json(['message' => 'Pregunta eliminada permanentemente'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th, 404);
        }
    }

    /**
     * Reordenamiento masivo (drag-and-drop), scoped por category_id para que
     * un request equivocado no corrompa el orden de otra categoría.
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer|exists:questions,id',
                'items.*.order' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $items = $validator->validated()['items'];
            $ids = array_column($items, 'id');

            $categoryIdsCount = Question::query()->whereIn('id', $ids)->distinct('category_id')->count('category_id');

            if ($categoryIdsCount > 1) {
                throw new Exception('Todas las preguntas a reordenar deben pertenecer a la misma categoría', 422);
            }

            $this->assertSurveyStructureEditable(
                $this->surveyIdOfCategory(Question::query()->whereIn('id', $ids)->value('category_id'))
            );

            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    Question::query()->where('id', $item['id'])->update(['order' => $item['order']]);
                }
            });

            return response()->json(['message' => 'Orden actualizado con éxito'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    public function showByCategory(int $id, Request $request)
    {
        try {
            // code...
            $query = Question::query()->where('category_id', $id);

            if ($request->boolean('with_trashed') && $request->user()?->rol?->name === Rol::ADMIN) {
                $query->withTrashed();
            }

            $questions = $query->orderBy('order', 'asc')->get();

            return response()->json([
                'questions' => $questions,
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Models\Answer;
use App\Models\Question;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class AnswerController extends Controller
{
    use ApiResponds;

    public static function rules($id = null)
    {
        return [
            'name' => 'required|string|max:350',
            'order' => 'required|integer|min:1',
            'question_id' => 'required|integer|min:1|exists:questions,id',
        ];

    }

    public static function updateRules($id = null)
    {
        return [
            'name' => 'required|string|max:350|min:5',
            'order' => 'required|integer|min:1',
            'question_id' => 'integer|min:1|exists:questions,id',
        ];

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $answer = Answer::all();

        return response()->json($answer, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        try {
            // code...
            $request['name'] = strtoupper($request->name);

            $validator = Validator::make($request->all(), $this->rules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $question = Question::query()->where('id', $request['question_id'])->first();

            if (! $question) {
                throw new Exception('Not found question register', 404);
            }

            $exist_answer_order = Answer::query()
                ->where('order', $request['order'])
                ->where('question_id', $request['question_id'])
                ->first();
            if ($exist_answer_order) {
                throw new Exception('orden de respuesta ya existe', 409);
            }

            $name_answer_question_exist = Answer::query()->where('name', $request->name)->where('question_id', $request->question_id)->first();

            if ($name_answer_question_exist) {
                throw new Exception('Error nombre de pregunta en encuesta ya existe', 400);
            }

            Answer::create($validator->validated());

            return response()->json([
                'message' => 'Creación de respuesta exitosa',
            ], 201);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
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
            // code...
            $answer = Answer::query()->where('id', $id)->first();

            if (! $answer) {
                throw new Exception('Not found answer register', 404);
            }

            return response()->json(['answer' => $answer], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    public function showByQuestion(int $id)
    {
        try {
            // code...
            $answers = Answer::query()->where('question_id', $id)->get();

            return response()->json(['answers' => $answers ? $answers : []], 200);
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
            $request['name'] = strtoupper($request['name']);

            $validator = Validator::make($request->all(), $this->updateRules());

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $answer = Answer::query()->where('id', $id)->first();

            if (! $answer) {
                throw new Exception('Not found answer register', 404);
            }

            $questionId = $request->filled('question_id') ? $request->question_id : $answer->question_id;

            if ($answer->order != $request->order) {
                $exist_answer_order = Answer::query()
                    ->where('order', $request['order'])
                    ->where('question_id', $questionId)
                    ->where('id', '!=', $id)
                    ->first();
                if ($exist_answer_order) {
                    throw new Exception('orden de respuesta ya existe', 409);
                }
            }

            $name_answer_question_exist = Answer::query()
                ->where('name', $request->name)
                ->where('question_id', $questionId)
                ->where('id', '!=', $id)
                ->first();

            if ($name_answer_question_exist) {
                throw new Exception('Error nombre de categoria en encuesta ya existe', 400);
            }

            $answer->update($validator->validated());

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

            $answer = Answer::select('id', 'name')->find($id);
            if (! $answer) {
                throw new Exception('Not found answer register', 404);
            }

            $answer->delete();

            return response()->json([
                'message' => 'Eliminación exitosa',
            ], 200);

        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Restaurar una respuesta previamente ocultada (soft-delete). Solo ADMIN.
     */
    public function restore(int $id)
    {
        try {
            $answer = Answer::withTrashed()->findOrFail($id);
            $answer->restore();

            return response()->json(['message' => 'Respuesta restaurada'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Eliminación permanente. Solo ADMIN.
     */
    public function forceDelete(int $id)
    {
        try {
            $answer = Answer::withTrashed()->findOrFail($id);
            $answer->forceDelete();

            return response()->json(['message' => 'Respuesta eliminada permanentemente'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /**
     * Reordenar las respuestas de una pregunta (arrastrar y soltar).
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer|exists:answers,id',
                'items.*.order' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $items = $validator->validated()['items'];
            $ids = array_column($items, 'id');

            $questionIdsCount = Answer::query()->whereIn('id', $ids)->distinct('question_id')->count('question_id');

            if ($questionIdsCount > 1) {
                throw new Exception('Todas las respuestas a reordenar deben pertenecer a la misma pregunta', 422);
            }

            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    Answer::query()->where('id', $item['id'])->update(['order' => $item['order']]);
                }
            });

            return response()->json(['message' => 'Orden actualizado con éxito'], 200);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }
    }

    /** FINAL METODOS CRUD */
    public function createMany(Request $request)
    {
        try {
            // code...
            $question_id = $request[0]['question_id'];

            $question = Question::query()->where('id', $question_id)->first();

            if (! $question) {
                throw new Exception('Error not found question register', 404);
            }

            $data = [];

            foreach ($request->all() as $categories => $value) {
                // code...
                $value['name'] = strtoupper($value['name']);
                $value['created_at'] = now();
                $value['updated_at'] = now();
                // return response()->json($value);
                array_push($data, $value);
            }
            // return response()->json($data);

            $validator = Validator::make($data, [
                '*.name' => ['required', 'string', 'distinct', Rule::unique('answers', 'name')->where(function ($query) use ($question_id) {
                    $query->where('question_id', $question_id)->whereNull('deleted_at');
                })],
                '*.question_id' => 'required|integer|exists:questions,id|in:'.$question_id,
                '*.order' => ['required', 'integer', 'distinct', Rule::unique('answers', 'order')->where(function ($query) use ($question_id) {
                    $query->where('question_id', $question_id)->where('deleted_at', null);
                })], // <--- "distinct" hace la magia
                '*.created_at' => 'date',
                '*.updated_at' => 'date',
            ], [
                '*.name.in' => 'los name deben ser diferentes',
                '*.question_id.in' => "los question_id's son diferentes",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            Answer::insert($validator->validated());

            return response()->json([
                'message' => 'se han creado exitosamente los '.count($validator->validated()),
            ], 201);
        } catch (Throwable $th) {
            return $this->errorResponse($th);
        }

    }
}

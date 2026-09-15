<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PersonController extends Controller
{
    public function preCreate(Request $request)
    {

        $new_respondent = Person::create([]);

        return response()->json($new_respondent, 201);
    }

    public function update(int $id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sex_id' => 'required|integer',
            'age' => 'required|integer|min:0|max:120',
            'parish_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $person = Person::find($id);
        if (! $person) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }

        $person->update($validator->validated());

        return response()->json([
            'message' => 'Actualización exitosa',
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:persons,email',
            'password' => 'required|string|min:8',
            'sex_id' => 'required|integer',
            'rol_id' => 'required|integer|in:1,3',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $person = Person::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sex_id' => $request->sex_id,
            'rol_id' => $request->rol_id,
        ]);

        return response()->json([
            'message' => 'Usuario creado con éxito',
            'person' => $person,
        ], 201);
    }

    public function show($id)
    {
        $person = Person::find($id);
        if (! $person) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }

        return response()->json($person);
    }

    public function getStaff(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $staff = Person::whereIn('rol_id', [1, 3])->paginate($perPage);

        return response()->json($staff, 200);
    }

    public function destroy(Request $request, int $id)
    {
        $person = Person::findOrFail($id);

        if ($request->user()?->id === $person->id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta'], 422);
        }

        if ($person->rol?->name === Rol::ADMIN) {
            $remainingAdmins = Person::whereHas('rol', fn ($q) => $q->where('name', Rol::ADMIN))
                ->where('id', '!=', $person->id)
                ->count();

            if ($remainingAdmins === 0) {
                return response()->json(['message' => 'No puedes eliminar al último administrador'], 422);
            }
        }

        $person->delete();

        return response()->json(['message' => 'Usuario marcado como eliminado']);
    }
}

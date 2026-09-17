<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    /**
     * IDs de los roles considerados "personal" (encuestador/admin), sin asumir
     * que el orden de inserción de la tabla roles sea siempre el mismo.
     */
    private function staffRoleIds(): array
    {
        return Rol::whereIn('name', [Rol::POLLSTER, Rol::ADMIN])->pluck('id')->all();
    }

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
            'rol_id' => ['required', 'integer', Rule::in($this->staffRoleIds())],
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

    public function updateStaff(Request $request, int $id)
    {
        $person = Person::whereIn('rol_id', $this->staffRoleIds())->find($id);

        if (! $person) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('persons', 'email')->ignore($person->id)],
            'password' => 'nullable|string|min:8',
            'sex_id' => 'required|integer',
            'rol_id' => ['required', 'integer', Rule::in($this->staffRoleIds())],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        $adminRoleId = Rol::where('name', Rol::ADMIN)->value('id');

        if ($person->rol?->name === Rol::ADMIN && (int) $data['rol_id'] !== $adminRoleId) {
            $remainingAdmins = Person::whereHas('rol', fn ($q) => $q->where('name', Rol::ADMIN))
                ->where('id', '!=', $person->id)
                ->count();

            if ($remainingAdmins === 0) {
                return response()->json(['message' => 'No puedes quitarle el rol de administrador al último administrador'], 422);
            }
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $person->update($data);

        return response()->json([
            'message' => 'Usuario actualizado con éxito',
            'person' => $person,
        ], 200);
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
        $staff = Person::with('rol')->whereIn('rol_id', $this->staffRoleIds())->paginate($perPage);

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

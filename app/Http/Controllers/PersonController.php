<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FiltersAndSorts;
use App\Models\Person;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersonController extends Controller
{
    use FiltersAndSorts;

    /**
     * IDs de los roles considerados "personal" (encuestador/admin), sin asumir
     * que el orden de inserción de la tabla roles sea siempre el mismo.
     */
    private function staffRoleIds(): array
    {
        return Rol::whereIn('name', [Rol::POLLSTER, Rol::ADMIN, Rol::GESTOR_ENCUESTAS])->pluck('id')->all();
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
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');

        $query = Person::with('rol')->whereIn('rol_id', $this->staffRoleIds());

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereLike('name', "%{$search}%")
                    ->orWhereLike('email', "%{$search}%")
                    ->orWhereHas('rol', fn ($r) => $r->whereLike('name', "%{$search}%"));
            });
        }

        $query = $this->applySort($query, $request, ['name', 'email', 'created_at'], 'created_at');

        $staff = $query->paginate($perPage);

        return response()->json($staff, 200);
    }

    /**
     * Deshabilita a un miembro del personal (no se elimina: un hard-delete
     * violaría el RESTRICT de results.person_id, y es intencional que un
     * Person nunca se borre físicamente).
     */
    public function disable(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $person = Person::findOrFail($id);

        if ($request->user()?->id === $person->id) {
            return response()->json(['message' => 'No puedes deshabilitar tu propia cuenta'], 422);
        }

        if ($person->rol?->name === Rol::ADMIN) {
            // Un admin ya deshabilitado no cuenta para mantener "activo" a este.
            $remainingActiveAdmins = Person::whereHas('rol', fn ($q) => $q->where('name', Rol::ADMIN))
                ->whereNull('disabled_at')
                ->where('id', '!=', $person->id)
                ->count();

            if ($remainingActiveAdmins === 0) {
                return response()->json(['message' => 'No puedes deshabilitar al último administrador activo'], 422);
            }
        }

        $person->update([
            'disabled_at' => now(),
            'disabled_reason' => $validator->validated()['reason'],
        ]);

        return response()->json(['message' => 'Usuario deshabilitado con éxito'], 200);
    }

    public function enable(int $id)
    {
        $person = Person::findOrFail($id);

        $person->update([
            'disabled_at' => null,
            'disabled_reason' => null,
        ]);

        return response()->json(['message' => 'Usuario habilitado con éxito'], 200);
    }
}

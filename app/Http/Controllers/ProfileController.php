<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $person = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('persons', 'email')->ignore($person->id)],
            'current_password' => 'required_with:password|string',
            'password' => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();

        if (! empty($data['password'])) {
            if (! Hash::check($data['current_password'], $person->password)) {
                return response()->json(['message' => 'La contraseña actual no es correcta'], 422);
            }

            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['current_password']);

        $person->update($data);

        return response()->json([
            'message' => 'Perfil actualizado con éxito',
            'person' => $person->fresh(),
        ], 200);
    }
}

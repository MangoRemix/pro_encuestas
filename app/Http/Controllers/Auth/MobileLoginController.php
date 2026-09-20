<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Login para la app móvil (Flutter): a diferencia de LoginController, no usa
 * sesión/cookie (stateless) — emite un token de Sanctum persistente para
 * que el encuestador pueda seguir autenticado sin conexión. Ruta separada de
 * LoginController::store en vez de una rama condicional ahí, para no mezclar
 * dos mecánicas de auth distintas (sesión con CSRF vs. token) en un mismo
 * método.
 */
class MobileLoginController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $user = Person::where('email', $credentials['email'])->first();

        // Se valida la contraseña directamente (en vez de Auth::attempt/once)
        // para no tocar el guard de sesión 'web' en absoluto: este endpoint
        // es puramente stateless y no debe interactuar con la sesión.
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas son incorrectas.',
            ]);
        }

        if ($user->disabled_at !== null) {
            throw ValidationException::withMessages([
                'email' => "Tu cuenta ha sido deshabilitada: {$user->disabled_reason}",
            ]);
        }

        if ($user->rol?->name !== Rol::POLLSTER) {
            throw ValidationException::withMessages([
                'email' => 'Solo los encuestadores pueden usar la aplicación móvil.',
            ]);
        }

        $token = $user->createToken($credentials['device_name'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->rol?->name,
                'parish_id' => $user->parish_id,
            ],
        ], 200);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente'], 200);
    }
}

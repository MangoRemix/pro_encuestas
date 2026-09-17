<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->disabled_at !== null) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = "Tu cuenta ha sido deshabilitada: {$user->disabled_reason}";

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 403);
                }

                throw ValidationException::withMessages([
                    'email' => $message,
                ]);
            }

            $request->session()->regenerate();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Login exitoso', 'user' => $user]);
            }

            return redirect()->intended('/');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Las credenciales proporcionadas son incorrectas.'], 422);
        }

        throw ValidationException::withMessages([
            'email' => 'Las credenciales proporcionadas son incorrectas.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Rol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageSurveys
{
    public function handle(Request $request, Closure $next): Response
    {
        $rol = $request->user()?->rol?->name;

        if (in_array($rol, [Rol::ADMIN, Rol::GESTOR_ENCUESTAS], true)) {
            return $next($request);
        }

        abort(403, 'No tienes permisos para gestionar encuestas.');
    }
}

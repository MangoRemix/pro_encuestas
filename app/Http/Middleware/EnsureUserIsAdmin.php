<?php

namespace App\Http\Middleware;

use App\Models\Rol;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->rol?->name === Rol::ADMIN) {
            return $next($request);
        }

        abort(403, 'No tienes permisos de administrador.');
    }
}

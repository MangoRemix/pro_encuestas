<?php

namespace App\Http\Controllers;

use App\Models\Rol;

class RolController extends Controller
{
    /**
     * Roles asignables a personal (encuestador/admin) desde el formulario de
     * usuarios — evita que el frontend adivine los IDs (no siempre son 1/3).
     */
    public function staffRoles()
    {
        $roles = Rol::whereIn('name', [Rol::POLLSTER, Rol::ADMIN])->get(['id', 'name']);

        return response()->json($roles, 200);
    }
}

<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Throwable;

trait ApiResponds
{
    /**
     * Respuesta de error uniforme: siempre incluye un código HTTP real,
     * en vez de depender de $th->getCode() (que casi nunca es un status HTTP válido).
     */
    protected function errorResponse(Throwable $th, int $default = 500, array $extra = []): JsonResponse
    {
        $code = $th->getCode();
        $status = ($code >= 400 && $code < 600) ? $code : $default;

        return response()->json(array_merge([
            'error' => $th->getMessage(),
            'code' => $status,
        ], $extra), $status);
    }
}

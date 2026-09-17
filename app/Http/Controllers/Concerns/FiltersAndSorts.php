<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersAndSorts
{
    /**
     * Aplica una búsqueda "contiene, sin distinguir mayúsculas" sobre varias
     * columnas dentro de un único where agrupado, para que componga bien con
     * otros where ya aplicados a la query. Usa whereLike/orWhereLike (Laravel
     * 11+) en vez de ILIKE crudo: ILIKE es exclusivo de Postgres y rompe bajo
     * SQLite (el driver que usan los tests), mientras que whereLike traduce
     * automáticamente a ILIKE en Postgres y a LOWER(...) LIKE en los demás.
     */
    protected function applySearch(Builder $query, ?string $search, array $columns): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search, $columns) {
            foreach ($columns as $column) {
                $q->orWhereLike($column, "%{$search}%");
            }
        });
    }

    /**
     * Aplica orden solo sobre columnas permitidas en $sortable, para evitar
     * inyectar nombres de columna arbitrarios desde la query string.
     */
    protected function applySort(Builder $query, Request $request, array $sortable, string $defaultSort, string $defaultDir = 'desc'): Builder
    {
        $sort = $request->query('sort');
        $sort = in_array($sort, $sortable, true) ? $sort : $defaultSort;

        $direction = strtolower((string) $request->query('direction'));
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : $defaultDir;

        return $query->orderBy($sort, $direction);
    }
}

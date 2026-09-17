<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Result;
use App\Models\Rol;

class DashboardController extends Controller
{
    public function summary()
    {
        return response()->json([
            'respondents' => Person::whereHas('rol', fn ($q) => $q->where('name', Rol::RESPONDENT))->count(),
            'pollsters' => Person::whereHas('rol', fn ($q) => $q->where('name', Rol::POLLSTER))->count(),
            'results' => Result::count(),
        ], 200);
    }
}

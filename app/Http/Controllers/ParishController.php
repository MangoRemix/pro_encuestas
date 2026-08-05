<?php

namespace App\Http\Controllers;

use App\Models\Parish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParishController extends Controller
{
    public function index()
    {
        return response()->json(Parish::all(), 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:300|unique:parishes,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parish = Parish::create($validator->validated());

        return response()->json($parish, 201);
    }
}


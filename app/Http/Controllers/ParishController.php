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
        $request['name'] = strtoupper($request->name); 
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:300|unique:parishes,name',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parish = Parish::create($validator->validated());

        return response()->json($parish, 201);
    }

    public function update(Request $request, Parish $parish)
    {
        $request['name'] = strtoupper($request->name); 
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:300|unique:parishes,name,' . $parish->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parish->update($validator->validated());
        return response()->json($parish, 200);
    }

    public function destroy($id)
    {
        Parish::query()->where('id',$id)->delete();
        return response()->json('Registro eliminado', 204);
    }
}


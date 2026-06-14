<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Person;

class PersonController extends Controller
{
    //
    public function preCreate(){
        $new_respondent = new Person();

        $new_respondent->save();

        return response()->json($new_respondent,201);
    }

    public function update(int $id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sex_id' => 'required|integer',
            'age_range_id' => 'required|integer',
            'parish_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $person = Person::where('id',$id)->update($request->all());
        return response()->json([
            "message" => 'Actualización exitosa'
        ], 200);
    }

    public function show($id)
    {
        $person = Person::find($id);
        if (!$person) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }
        return response()->json($person);
    }
}
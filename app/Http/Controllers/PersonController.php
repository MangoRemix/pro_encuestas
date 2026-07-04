<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Person;
use Illuminate\Support\Facades\Hash;
use App\Services\PersonService;

class PersonController extends Controller
{

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

        Person::where('id',$id)->update($request->all());
        return response()->json([
            "message" => 'Actualización exitosa'
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:persons,email',
            'password' => 'required|string|min:8',
            'sex_id'   => 'required|integer',
            'rol_id'   => 'required|integer|in:1,3',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $person = Person::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'sex_id'   => $request->sex_id,
            'rol_id'   => $request->rol_id,
        ]);

        return response()->json([
            'message' => 'Usuario creado con éxito',
            'person'  => $person
        ], 201);
    }

    public function show($id)
    {
        $person = Person::find($id);
        if (!$person) {
            return response()->json(['message' => 'Persona no encontrada'], 404);
        }
        return response()->json($person);
    }

    public function getStaff()
    {
        $staff = Person::whereIn('rol_id', [1, 3])->get();
        return response()->json($staff, 200);
    }
}


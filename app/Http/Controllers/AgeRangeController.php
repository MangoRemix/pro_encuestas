<?php

namespace App\Http\Controllers;

use App\Models\AgeRange;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgeRangeController extends Controller
{
    //
    public static function rules($id = null){
        return [
            "init_range" => "required|integer|min:7",
            "finish_range" => "required|integer|min:7"
        ];
    }
    public static function updateRules($id = null){
        return [
            "init_range" => "integer|min:7",
            "finish_range" => "integer|min:7"
        ];
    }

    public function index () {
        return response()->json(AgeRange::all(),200);
    }

    public function create(Request $request){

        try {
            //code...
            $validator = Validator::make($request->all(),$this->rules());

            if($validator->fails()){
                return response()->json($validator->errors(),422);
            }
            
            if($request->init_range>$request->finish_range){
                throw new Exception("Error finish_date debe ser mayor que init_date", 400);
            }                

            $range = $request->init_range.' - '.$request->finish_range;

            $exist_range = AgeRange::query()->where("range",$range)->first();

            if($exist_range)
                throw new Exception("Error rango ya existe", 400);
            
            AgeRange::create([
                "range" => $range
            ]);

            return response()->json([
                "message" => "Creación exitosa"
            ],201);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    public function show (int $id){
        try {
            //code...
            $ageRange = AgeRange::query()->where('id',$id)->first();

            if(!$ageRange)
                throw new Exception("Error ageRange not found register", 404);
            
            return response()->json($ageRange,200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    public function update (Request $request, int $id){
        try {
            //code...
            $ageRange = AgeRange::query()->where('id',$id)->first();
            
            if(!$ageRange)
                throw new Exception("Error ageRange not found register", 404);

            $validator = Validator::make($request->all(),$this->rules());

            if($validator->fails()){
                return response()->json($validator->errors(),422);
            }
            
            if($request->init_range>$request->finish_range){
                throw new Exception("Error finish_date debe ser mayor que init_date", 400);
            }                

            $range = $request->init_range.' - '.$request->finish_range;

            $exist_range = AgeRange::query()->where("range",$range)->first();

            if($exist_range)
                throw new Exception("Error rango ya existe", 400);
            
            AgeRange::query()->where('id',$id)->update([
                "range" => $range
            ]);

            return response()->json([
                "message" => "Actualización exitosa"
            ],200);
            
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }

    public function destroy(int $id){
        try {
            //code...
            $ageRange = AgeRange::query()->where('id',$id)->first();
            if(!$ageRange)
                throw new Exception("Error ageRange not found register", 404);

            AgeRange::query()->where('id',$id)->delete();

            return response()->json([
                "message" => "Eliminación Exitosa"
            ],200);
                
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                "error" => $th->getMessage(),
                "code" => $th->getCode()
            ]);
        }
    }
}

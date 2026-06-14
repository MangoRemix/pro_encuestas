<?php

namespace App\Http\Controllers;

use App\Models\Sex;
use Illuminate\Http\Request;

class SexController extends Controller
{
    //
    public function index(){
        try {
            //code...
            $sex = Sex::all();

            return response()->json($sex,200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => $th->getMessage(),
                "code" => $th->getCode()    
            ]);
        }
    }
}

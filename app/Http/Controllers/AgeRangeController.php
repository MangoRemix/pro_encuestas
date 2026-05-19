<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgeRangeController extends Controller
{
    //

    public function create(){
        return 'AgeRange Created';
    }

    public function delete(string $id){
        return 'delete '.$id;
    }
}

<?php

use App\Http\Controllers\AgeRangeController;
use App\Http\Controllers\PersonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

/** PERSON RESOURCES  **/
Route::prefix('person')->group(function (){

    /** ENCUESTADO RESOURCES */
    Route::prefix('respondent')->group(function () {
        Route::post('create',[PersonController::class, 'create']);
    });
    
});


/** AGE RANGE RESOURCES  **/
Route::prefix('age-range')->group(function (){
    Route::post('create',[AgeRangeController::class, 'create']);
    Route::delete('delete/{id}',[AgeRangeController::class, 'delete']);
});

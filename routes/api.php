<?php

use App\Http\Controllers\AgeRangeController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\SurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

/** PERSON RESOURCES  **/
Route::prefix('person')->group(function (){

    /** ENCUESTADO RESOURCES */
    Route::prefix('respondent')->group(function () {
        Route::post('create',[PersonController::class, 'store']);
    });
    
});


/** AGE RANGE RESOURCES  **/
Route::prefix('age-range')->group(function (){
    Route::post('create',[AgeRangeController::class, 'store']);
    Route::delete('delete/{id}',[AgeRangeController::class, 'destroy']);
});

/** SURVEY RESOURCES */
Route::prefix('survey')->group(function (){
    Route::post('create',[SurveyController::class, 'store']);
    Route::get('show-all',[SurveyController::class, 'show_all']);
    Route::get('show-one/{id}',[SurveyController::class, 'show_one']);
    Route::put('update/{id}',[SurveyController::class, 'update']);
    Route::delete('delete/{id}',[SurveyController::class, 'destroy']);
});
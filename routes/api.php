<?php

use App\Http\Controllers\AgeRangeController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\QuestionController;
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
    Route::get('show-all',[SurveyController::class, 'index']);
    Route::get('show-one/{id}',[SurveyController::class, 'show']);
    Route::put('update/{id}',[SurveyController::class, 'update']);
    Route::delete('delete/{id}',[SurveyController::class, 'destroy']);
});

/** CATEGORIES RESOURES */
Route::prefix('category')->group(function (){
    Route::post('create',[CategoryController::class, 'store']);
    Route::get('show-all',[CategoryController::class, 'index']);
    Route::get('show-one/{id}',[CategoryController::class, 'show']);
    Route::put('update/{id}',[CategoryController::class, 'update']);
    Route::delete('delete/{id}',[CategoryController::class, 'destroy']);
});

/** QUESTIONS RESOURES */
Route::prefix('question')->group(function (){
    Route::post('create',[QuestionController::class, 'store']);
    Route::get('show-all',[QuestionController::class, 'index']);
    Route::get('show-one/{id}',[QuestionController::class, 'show']);
    Route::put('update/{id}',[QuestionController::class, 'update']);
    Route::delete('delete/{id}',[QuestionController::class, 'destroy']);
});
<?php

use App\Http\Controllers\AgeRangeController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ParishController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SexController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SurveyImportController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Lectura de catálogos necesarios para formularios y registro)
Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');

Route::prefix('sex')->group(function(){
    Route::get('show-all', [SexController::class, 'index']);
});

Route::prefix('parish')->group(function(){
    Route::get('show-all', [ParishController::class, 'index']);
});
Route::prefix('age-range')->group(function(){
    Route::get('show-all', [AgeRangeController::class, 'index']);
    Route::get('show-one/{id}', [AgeRangeController::class, 'show']);
});

// Rutas Protegidas por autenticación (Sanctum) y Rate Limiting
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

    /** PERSON RESOURCES  **/
    Route::prefix('person')->group(function (){
        Route::prefix('respondent')->group(function () {
            Route::get('pre-create', [PersonController::class, 'preCreate']);
            Route::patch('update/{id}', [PersonController::class, 'update']);
            Route::get('show/{id}', [PersonController::class, 'show']);
        });
        Route::prefix('pollster-admin')->group(function () {
            Route::post('create', [PersonController::class, 'store']);
            Route::get('list', [PersonController::class, 'getStaff']);
        });
    });

    /** AGE RANGE (Escritura protegida) **/
    Route::prefix('age-range')->group(function (){
        Route::post('create', [AgeRangeController::class, 'create']);
        Route::put('update/{id}', [AgeRangeController::class, 'update']);
        Route::delete('delete/{id}', [AgeRangeController::class, 'destroy']);
    });

    /** SURVEY RESOURCES */
    Route::prefix('survey')->group(function (){
        Route::post('create', [SurveyController::class, 'store']);
        Route::post('import-excel', [SurveyImportController::class, 'importFromExcel']);
        Route::get('show-all', [SurveyController::class, 'index']);
        Route::get('show-one/{id}', [SurveyController::class, 'show']);
        Route::get('show-full/{id}', [SurveyController::class, 'showFull']);
        Route::put('update/{id}', [SurveyController::class, 'update']);
        Route::delete('delete/{id}', [SurveyController::class, 'destroy']);
    });

    /** CATEGORIES RESOURCES */
    Route::prefix('category')->group(function (){
        Route::post('create', [CategoryController::class, 'store']);
        Route::post('create-many', [CategoryController::class, 'createMany']);
        Route::get('show-all', [CategoryController::class, 'index']);
        Route::get('show-one/{id}', [CategoryController::class, 'show']);
        Route::get('show-by-survey/{id}', [CategoryController::class, 'showBySurvey']);
        Route::put('update/{id}', [CategoryController::class, 'update']);
        Route::delete('delete/{id}', [CategoryController::class, 'destroy']);
    });

    /**QUESTIONS RESOURCES */
    Route::prefix('question')->group(function (){
        Route::post('create', [QuestionController::class, 'store']);
        Route::post('create-many', [QuestionController::class, 'createMany']);
        /** CREAR UN METODO PARA CREAR ARRAY DE  QUESTIONS FALTANTE*/
        Route::get('show-all', [QuestionController::class, 'index']);
        Route::get('show-one/{id}', [QuestionController::class, 'show']);
        Route::get('show-by-category/{id}', [QuestionController::class, 'showByCategory']);
        Route::put('update/{id}', [QuestionController::class, 'update']);
        Route::delete('delete/{id}', [QuestionController::class, 'destroy']);
    });

    /** ANSWERS RESOURCES */
    Route::prefix('answer')->group(function (){
        Route::post('create', [AnswerController::class, 'create']);
        Route::post('create-many', [AnswerController::class, 'createMany']);
        /** CREAR UN METODO PARA CREAR ARRAY DE  ANSWERS FALTANTE*/
        Route::get('show-all', [AnswerController::class, 'index']);
        Route::get('show-one/{id}', [AnswerController::class, 'show']);
        Route::get('show-by-question/{id}', [AnswerController::class, 'showByQuestion']);
        Route::put('update/{id}', [AnswerController::class, 'update']);
        Route::delete('delete/{id}', [AnswerController::class, 'destroy']);
    });

    /** RESULTS RESOURCES */
    Route::prefix('result')->group(function (){
        Route::post('create', [ResultController::class, 'create']);
        Route::post('batch', [ResultController::class, 'storeBatch']);
        Route::get('batch-status/{batchId}', [ResultController::class, 'getBatchStatus']);
        Route::get('report/{surveyId}', [ResultController::class, 'reportCountAnswersByQuestion']);
        Route::get('age-range/{surveyId}', [ResultController::class, 'getRespondentCountByAgeRange']);
        Route::get('show-all', [ResultController::class, 'index']);
        Route::get('show-one/{id}', [ResultController::class, 'show']);
        Route::put('update/{id}', [ResultController::class, 'update']);
        Route::delete('delete/{id}', [ResultController::class, 'destroy']);
        Route::get('newReportStructure/{id}', [ResultController::class, 'newReportStructure']);
        
    });

    /** PARISH (Escritura protegida) */
    Route::prefix('parish')->group(function(){
            Route::post('create', [ParishController::class, 'store']);
            Route::put('{id}', [ParishController::class, 'update']);
            Route::delete('{id}', [ParishController::class, 'destroy']);
    });
    
});



<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParishController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SexController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SurveyImportController;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Lectura de catálogos necesarios para formularios y registro)
Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1');

Route::prefix('sex')->group(function () {
    Route::get('show-all', [SexController::class, 'index']);
});

Route::prefix('parish')->group(function () {
    Route::get('show-all', [ParishController::class, 'index']);
});

// Rutas Protegidas por autenticación (Sanctum) y Rate Limiting
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

    /** DASHBOARD */
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);

    /** PERSON RESOURCES  **/
    Route::prefix('person')->group(function () {
        // Usadas por el flujo de encuestador (poll-users) para registrar respondents
        Route::prefix('respondent')->group(function () {
            Route::get('pre-create', [PersonController::class, 'preCreate']);
            Route::patch('update/{id}', [PersonController::class, 'update']);
            Route::get('show/{id}', [PersonController::class, 'show']);
        });

        // Gestión de personal (encuestadores/administradores) — solo ADMIN
        Route::middleware('admin')->group(function () {
            Route::prefix('pollster-admin')->group(function () {
                Route::post('create', [PersonController::class, 'store']);
                Route::get('list', [PersonController::class, 'getStaff']);
                Route::put('update/{id}', [PersonController::class, 'updateStaff']);
            });
            Route::delete('delete/{id}', [PersonController::class, 'destroy']);
        });
    });

    /** SURVEY RESOURCES */
    Route::prefix('survey')->group(function () {
        // Lectura: necesaria para que el encuestador seleccione/administre encuestas
        Route::get('show-recent', [SurveyController::class, 'getRecent']);
        Route::get('show-all', [SurveyController::class, 'index']);
        Route::get('show-one/{id}', [SurveyController::class, 'show']);
        Route::get('show-full/{id}', [SurveyController::class, 'showFull']);

        // Escritura: solo ADMIN (gestión de encuestas)
        Route::middleware('admin')->group(function () {
            Route::post('create', [SurveyController::class, 'store']);
            Route::post('import-excel', [SurveyImportController::class, 'importFromExcel']);
            Route::put('update/{id}', [SurveyController::class, 'update']);
            Route::delete('delete/{id}', [SurveyController::class, 'destroy']);
        });
    });

    /** CATEGORIES RESOURCES */
    Route::prefix('category')->group(function () {
        Route::get('show-all', [CategoryController::class, 'index']);
        Route::get('show-one/{id}', [CategoryController::class, 'show']);
        Route::get('show-by-survey/{id}', [CategoryController::class, 'showBySurvey']);

        Route::middleware('admin')->group(function () {
            Route::post('create', [CategoryController::class, 'store']);
            Route::post('create-many', [CategoryController::class, 'createMany']);
            Route::put('update/{id}', [CategoryController::class, 'update']);
            Route::delete('delete/{id}', [CategoryController::class, 'destroy']);
        });
    });

    /**QUESTIONS RESOURCES */
    Route::prefix('question')->group(function () {
        Route::get('show-all', [QuestionController::class, 'index']);
        Route::get('show-one/{id}', [QuestionController::class, 'show']);
        Route::get('show-by-category/{id}', [QuestionController::class, 'showByCategory']);

        Route::middleware('admin')->group(function () {
            Route::post('create', [QuestionController::class, 'store']);
            Route::post('create-many', [QuestionController::class, 'createMany']);
            Route::put('update/{id}', [QuestionController::class, 'update']);
            Route::delete('delete/{id}', [QuestionController::class, 'destroy']);
        });
    });

    /** ANSWERS RESOURCES */
    Route::prefix('answer')->group(function () {
        Route::get('show-all', [AnswerController::class, 'index']);
        Route::get('show-one/{id}', [AnswerController::class, 'show']);
        Route::get('show-by-question/{id}', [AnswerController::class, 'showByQuestion']);

        Route::middleware('admin')->group(function () {
            Route::post('create', [AnswerController::class, 'create']);
            Route::post('create-many', [AnswerController::class, 'createMany']);
            Route::put('update/{id}', [AnswerController::class, 'update']);
            Route::delete('delete/{id}', [AnswerController::class, 'destroy']);
        });
    });

    /** RESULTS RESOURCES */
    Route::prefix('result')->group(function () {
        // El encuestador registra resultados en campo
        Route::post('create', [ResultController::class, 'create']);
        Route::post('batch', [ResultController::class, 'storeBatch']);
        Route::get('batch-status/{batchId}', [ResultController::class, 'getBatchStatus']);

        // Reportes y administración de resultados — solo ADMIN
        Route::middleware('admin')->group(function () {
            Route::get('report/{surveyId}', [ResultController::class, 'reportCountAnswersByQuestion']);
            Route::get('age-range/{surveyId}', [ResultController::class, 'getRespondentCountByAgeRange']);
            Route::get('sex/{surveyId}', [ResultController::class, 'getRespondentCountBySex']);
            Route::get('show-all', [ResultController::class, 'index']);
            Route::get('show-one/{id}', [ResultController::class, 'show']);
            Route::put('update/{id}', [ResultController::class, 'update']);
            Route::delete('delete/{id}', [ResultController::class, 'destroy']);
            Route::get('newReportStructure/{id}', [ResultController::class, 'newReportStructure']);
            Route::get('parish/{surveyId}', [ResultController::class, 'getRespondentCountByParish']);
            Route::get('reports/top-pollsters', [ResultController::class, 'getTopPollsters']);
        });
    });

    /** PARISH (Escritura protegida) */
    Route::middleware('admin')->prefix('parish')->group(function () {
        Route::post('create', [ParishController::class, 'store']);
        Route::put('{id}', [ParishController::class, 'update']);
        Route::delete('{id}', [ParishController::class, 'destroy']);
    });

});

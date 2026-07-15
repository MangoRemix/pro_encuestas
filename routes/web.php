<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;

Route::inertia('/', 'index')->name('home');

Route::prefix('surveys')->group(function (){
    Route::prefix('/create-survey')->group(function(){
        Route::inertia('/step-1', 'create-survey/step-1')->name('survey-create-step1');

        Route::get('/step-2',function (Request $request){
            return Inertia::render('create-survey/step-2',[
                "surveyId" => $request->query("surveyId")
            ]);
        })->name('survey-create-step2');

        Route::get('/step-3',function (Request $request){
            return Inertia::render('create-survey/step-3',[
                "surveyId" => $request->query("surveyId"),
                "categoryId" => $request->query("categoryId")
            ]);
        })->name('survey-create-step3');

        Route::get('/step-4',function (Request $request){
            return Inertia::render('create-survey/step-4',[
                "surveyId" => $request->query("surveyId"),
            ]);
        })->name('survey-create-step4');
    });
    
    Route::get('/',function (Request $request){
        return Inertia::render('surveys/index',[
            "page" => $request->query("page"),
        ]);
    })->name('survey-index');
    
    Route::get('/details/{id}', function(Request $request,$id){
        return Inertia::render('surveys/details',[
            'id' => $id,
            'categoryId' => $request->query('categoryId')
        ]);
    } )->name('survey-details');
});

Route::prefix('categories')->group(function (){
    
    Route::get('/details/{id}', function($id){
        return Inertia::render('categories/details',[
            'id' => $id
        ]);
    } )->name('category-details');

    Route::get('/',function (Request $request) {
        return Inertia::render('categories/index',[
            'surveyId' => $request->query('surveyId'),
            'categoryId' => $request->query('categoryId'),
        ]);
    })->name('categories');

    Route::get('/create',function (Request $request){
        return Inertia::render('categories/create',[
            "surveyId" => $request->query("surveyId")
        ]);
    })->name('category-create');
});

Route::prefix('questions')->group(function (){
    Route::get('/details/{id}', function($id){
        return Inertia::render('questions/details',[
            'id' => $id
        ]);
    } )->name('questions-details');
});

Route::prefix('poll-users')->group(function (){

    Route::inertia('/','poll-users/index')->name('poll-users-home');

    Route::inertia('step-1','poll-users/step-1')->name('step-1');

    Route::get('step-2', function (Request $request) {
        return Inertia::render('poll-users/new-user-respondent',[
            "id" => $request->query('id'),
            "surveyId" => $request->query('surveyId')
        ]);
    })->name('new-user-respondent');

    Route::get('step-3/{userId}/survey/{id}', function ($userId,$id, Request $request) {
        return Inertia::render('poll-users/step-3',[
            'id' => $id,
            'userId' => $userId,
            'category' => $request->query('category'),
            'question' => $request->query('question')
        ]);
    })->name('poll-user');   
});

Route::prefix('users')->group(function () {
    Route::inertia('/create', 'users/create')->name('users-create');
    Route::inertia('/', 'users/index')->name('users-all');
});

Route::inertia('login','login/index')->name('login');
Route::post('/login', [LoginController::class, 'store']);


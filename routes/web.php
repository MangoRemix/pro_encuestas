<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'index')->name('home');


Route::prefix('surveys')->group(function (){
    Route::inertia('create', 'surveys/create')->name('survey-create');
    Route::inertia('/', 'surveys/index')->name('survey-index');
    
    Route::get('/details/{id}', function($id){
        return Inertia::render('surveys/details',[
            'id' => $id
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
    
    //Route::inertia('/', 'categories/index')->name('categories');
});

Route::prefix('questions')->group(function (){
    
    Route::get('/details/{id}', function($id){
        return Inertia::render('questions/details',[
            'id' => $id
        ]);
    } )->name('questions-details');

});

Route::prefix('poll-users')->group(function (){

    
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







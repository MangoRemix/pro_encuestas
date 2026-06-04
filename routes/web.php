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
    //Route::inertia('/', 'categories/index')->name('categories');
});

Route::prefix('questions')->group(function (){
    
    Route::get('/details/{id}', function($id){
        return Inertia::render('questions/details',[
            'id' => $id
        ]);
    } )->name('questions-details');

});

// Route::prefix('categories')->group(function (){
//     Route::inertia('create', 'categories/create')->name('category-create');
    
// });



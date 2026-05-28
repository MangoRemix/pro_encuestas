<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');


Route::prefix('surveys')->group(function (){
    Route::inertia('create', 'surveys/create')->name('survey-create');
    Route::inertia('/', 'surveys/index')->name('survey-index');
    
    Route::get('/details/{id}', function($id){
        return Inertia::render('surveys/details',[
            'id' => $id
        ]);
    } )->name('survey-details');
});

// Route::prefix('categories')->group(function (){
//     Route::inertia('create', 'categories/create')->name('category-create');
    
// });



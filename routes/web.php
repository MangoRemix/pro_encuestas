<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SurveyImportController;

Route::inertia('/', 'index')->middleware(['auth'])->name('home');

Route::prefix('surveys')->group(function (){
    Route::get('/import-excel', [SurveyImportController::class, 'importFromExcel'])->middleware(['auth', 'admin']);

    Route::prefix('/create-survey')->middleware(['auth', 'admin'])->group(function(){
        Route::inertia('/step-1', 'create-survey/step-1')->name('survey-create-step1');

        Route::get('/step-2',function (Request $request){
            $validated = $request->validate([
                'surveyId' => ['required', 'integer'],
            ]);
            return Inertia::render('create-survey/step-2',[
                "surveyId" => $validated["surveyId"]
            ]);
        })->name('survey-create-step2');

        Route::get('/step-3',function (Request $request){
            $validated = $request->validate([
                'surveyId' => ['required', 'integer'],
                'categoryId' => ['nullable', 'integer'],
            ]);
            return Inertia::render('create-survey/step-3',[
                "surveyId" => $validated["surveyId"],
                "categoryId" => $validated["categoryId"] ?? null,
            ]);
        })->name('survey-create-step3');
        Route::get('/step-4',function (Request $request){
            $validated = $request->validate([
                'surveyId' => ['required', 'integer'],
            ]);
            return Inertia::render('create-survey/step-4',[
                "surveyId" => $validated["surveyId"],
            ]);
        })->name('survey-create-step4');
    });
    Route::get('/',function (Request $request){
        $validated = $request->validate([
            'page' => ['nullable', 'integer'],
        ]);
        return Inertia::render('surveys/index',[
            "page" => $validated["page"] ?? null,
        ]);
    })->middleware(['auth', 'admin'])->name('survey-index');

    Route::get('/details/{id}', function(Request $request, $id){
        $validated = $request->validate([
            'categoryId' => ['nullable', 'integer'],
        ]);
        return Inertia::render('surveys/details',[
            'id' => $id,
            'categoryId' => $validated['categoryId'] ?? null
        ]);
    })->name('survey-details');
});

// Route::prefix('age-ranges')->middleware(['auth', 'admin'])->group(function (){
//     Route::inertia('/', 'age-ranges/index')->name('age-ranges-index');
// });
    
Route::prefix('categories')->group(function (){

    Route::get('/details/{id}', function($id){
        return Inertia::render('categories/details',[
            'id' => $id
        ]);
    })->name('category-details');

    Route::get('/',function (Request $request) {
        $validated = $request->validate([
            'surveyId' => ['nullable', 'integer'],
            'categoryId' => ['nullable', 'integer'],
        ]);
        return Inertia::render('categories/index',[
            'surveyId' => $validated['surveyId'] ?? null,
            'categoryId' => $validated['categoryId'] ?? null,
        ]);
    })->name('categories');

    Route::get('/create',function (Request $request){
        $validated = $request->validate([
            'surveyId' => ['required', 'integer'],
        ]);
        return Inertia::render('categories/create',[
            "surveyId" => $validated["surveyId"]
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

Route::prefix('poll-users')->middleware(['auth'])->group(function (){
    Route::inertia('/','poll-users/index')->name('poll-users-home');

    Route::inertia('step-1','poll-users/step-1')->name('step-1');

    Route::inertia('finished-list', 'poll-users/finished-list')->name('poll-users-finished-list');

    Route::get('step-2', function (Request $request) {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'surveyId' => ['required', 'integer'],
        ]);
        return Inertia::render('poll-users/new-user-respondent',[
            "id" => $validated['id'],
            "surveyId" => $validated['surveyId']
        ]);
    })->name('new-user-respondent');

    Route::get('step-3/{userId}/survey/{id}', function ($userId, $id, Request $request) {
        $validated = $request->validate([
            'category' => ['nullable', 'string'],
            'question' => ['nullable', 'string'],
        ]);
        return Inertia::render('poll-users/step-3',[
            'id' => $id,
            'userId' => $userId,
            'category' => $validated['category'] ?? null,
            'question' => $validated['question'] ?? null
        ]);
    })->name('poll-user');   
});

Route::prefix('users')->middleware(['auth', 'admin'])->group(function () {
    Route::inertia('/create', 'users/create')->name('users-create');
    Route::inertia('/', 'users/index')->name('users-all');
});

Route::prefix('reports')->middleware(['auth', 'admin'])->group(function (){
    Route::get('/', function (Request $request){
        $validated = $request->validate([
            'surveyId' => ['nullable', 'integer'],
            'categoryId' => ['nullable', 'integer'],
        ]);
        return Inertia::render('reports/reports-layout', [
            'surveyId' => $validated['surveyId'] ?? null,
            'categoryId' => $validated['categoryId'] ?? null,
        ]);
    })->name('reports-index');
});

// ... existing code ...
Route::get('/parishes', function () {
    return Inertia::render('parishes/index');
})->name('parishes.index');

Route::middleware('guest')->group(function () {
    Route::inertia('login', 'login/index')->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');


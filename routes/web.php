<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\SurveyImportController;

Route::middleware(['auth'])->group(function () {
    Route::inertia('/', 'index')->name('home');

    Route::middleware(['admin'])->group(function () {
        Route::prefix('surveys')->name('surveys.')->group(function () {
            Route::get('/import-excel', [SurveyImportController::class, 'importFromExcel'])->name('import');
            
            Route::get('/', function (Request $request) {
                $validated = $request->validate(['page' => ['nullable', 'integer']]);
                return Inertia::render('surveys/index', ['page' => $validated['page'] ?? null]);
            })->name('index');

            Route::get('/details/{id}', function (Request $request, int $id) {
                $validated = $request->validate(['categoryId' => ['nullable', 'integer']]);
                return Inertia::render('surveys/details', [
                    'id' => $id,
                    'categoryId' => $validated['categoryId'] ?? null,
                ]);
            })->whereNumber('id')->name('show');

            Route::prefix('create')->name('create.')->group(function () {
                Route::inertia('/step-1', 'create-survey/step-1')->name('step-1');
                Route::get('/step-2', function (Request $request) {
                    $validated = $request->validate(['surveyId' => ['required', 'integer']]);
                    return Inertia::render('create-survey/step-2', ['surveyId' => $validated['surveyId']]);
                })->name('step-2');
                Route::get('/step-3', function (Request $request) {
                    $validated = $request->validate(['surveyId' => ['required', 'integer'], 'categoryId' => ['nullable', 'integer']]);
                    return Inertia::render('create-survey/step-3', [
                        'surveyId' => $validated['surveyId'],
                        'categoryId' => $validated['categoryId'] ?? null,
                    ]);
                })->name('step-3');
                Route::get('/step-4', function (Request $request) {
                    $validated = $request->validate(['surveyId' => ['required', 'integer']]);
                    return Inertia::render('create-survey/step-4', ['surveyId' => $validated['surveyId']]);
                })->name('step-4');
            });
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', function (Request $request) {
                $validated = $request->validate(['surveyId' => ['nullable', 'integer'], 'categoryId' => ['nullable', 'integer']]);
                return Inertia::render('categories/index', [
                    'surveyId' => $validated['surveyId'] ?? null,
                    'categoryId' => $validated['categoryId'] ?? null,
                ]);
            })->name('index');
            Route::get('/create', function (Request $request) {
                $validated = $request->validate(['surveyId' => ['required', 'integer']]);
                return Inertia::render('categories/create', ['surveyId' => $validated['surveyId']]);
            })->name('create');
            Route::get('/details/{id}', fn(int $id) => Inertia::render('categories/details', ['id' => $id]))->whereNumber('id')->name('show');
        });

        Route::prefix('questions')->name('questions.')->group(function () {
            Route::get('/details/{id}', fn(int $id) => Inertia::render('questions/details', ['id' => $id]))->whereNumber('id')->name('show');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', function (Request $request) {
                $validated = $request->validate(['surveyId' => ['nullable', 'integer'], 'categoryId' => ['nullable', 'integer']]);
                return Inertia::render('reports/reports-layout', [
                    'surveyId' => $validated['surveyId'] ?? null,
                    'categoryId' => $validated['categoryId'] ?? null,
                ]);
            })->name('index');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::inertia('/create', 'users/create')->name('create');
            Route::inertia('/', 'users/index')->name('index');
        });

        Route::get('/parishes', fn() => Inertia::render('parishes/index'))->name('parishes.index');
    });

    Route::prefix('poll-users')->name('poll-users.')->group(function () {
        Route::inertia('/', 'poll-users/index')->name('index');
        Route::inertia('/step-1', 'poll-users/step-1')->name('step-1');
        Route::inertia('/finished-list', 'poll-users/finished-list')->name('finished-list');
        Route::get('/step-2', function (Request $request) {
            $validated = $request->validate(['id' => ['required', 'integer'], 'surveyId' => ['required', 'integer']]);
            return Inertia::render('poll-users/new-user-respondent', [
                'id' => $validated['id'],
                'surveyId' => $validated['surveyId']
            ]);
        })->name('step-2');
        Route::get('/step-3/{userId}/survey/{id}', function ($userId, $id, Request $request) {
            $validated = $request->validate(['category' => ['nullable', 'string'], 'question' => ['nullable', 'string']]);
            return Inertia::render('poll-users/step-3', [
                'id' => $id,
                'userId' => $userId,
                'category' => $validated['category'] ?? null,
                'question' => $validated['question'] ?? null
            ]);
        })->name('step-3');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'login/index')->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

// Password Reset Routes
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store']);
});

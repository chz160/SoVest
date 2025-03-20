<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate;
use App\Controllers\HomeController;

// Authentication routes group
Route::get('/', [HomeController::class, 'index']);
Route::get('/login', [AuthController::class, 'loginForm']);
Route::post('/login/submit', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registerForm']);
Route::post('/register/submit', [AuthController::class, 'register']);
Route::get('/logout', [AuthController::class, 'logout']);

// User routes group
Route::get('/home', [HomeController::class, 'home'])->middleware('auth');;
Route::get('/account', [UserController::class, 'account'])->middleware('auth');;
Route::get('/leaderboard', [UserController::class, 'leaderboard'])->middleware('auth');

// Prediction routes group
Route::controller(PredictionController::class)->group(function () {
    Route::get('/predictions', 'index');
    Route::get('/predictions/view/{id}', 'view');
    Route::get('/predictions/trending', 'trending');
    Route::get('/predictions/create', 'create')->middleware('auth');
    Route::post('/predictions/store', 'store')->middleware('auth');
    Route::get('/predictions/edit/{id}', 'edit')->middleware('auth');
    Route::post('/update/{id}', 'update')->middleware('auth');
    Route::post('/delete/{id}', 'delete')->middleware('auth');
    Route::post('/vote/{id}', 'vote')->middleware('auth');
});



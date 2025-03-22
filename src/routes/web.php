<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\SearchController;

// We're not really using routes here we just want to redirect to main.php

Route::get('/', [MainController::class, 'index'])->name('landing');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register.form');
Route::post('/register/submit', [AuthController::class, 'register'])->name('register.submit');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login/submit', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/home', [UserController::class, 'home'])->name('user.home')->middleware('auth');
Route::get('/account', [UserController::class, 'account'])->name('user.account')->middleware('auth');
Route::get('/leaderboard', [UserController::class, 'leaderboard'])->name('user.leaderboard')->middleware('auth');

Route::controller(PredictionController::class)->group(function () {
    Route::get('/predictions', 'index')->name('predictions.index');
    Route::get('/predictions/view/{id}', 'view')->name('predictions.view');
    Route::get('/predictions/trending', 'trending')->name('predictions.trending');
    Route::get('/predictions/create', 'create')->name('predictions.create')->middleware('auth');
    Route::post('/predictions/store', 'store')->name('predictions.store')->middleware('auth');
    Route::get('/predictions/edit/{id}', 'edit')->name('predictions.edit')->middleware('auth')->middleware('prediction.owner');
    Route::post('/update/{id}', 'update')->name('predictions.update')->middleware('auth')->middleware('prediction.owner');
    Route::post('/delete/{id}', 'delete')->name('predictions.delete')->middleware('auth')->middleware('prediction.owner');
    Route::post('/vote/{id}', 'vote')->name('predictions.vote')->middleware('auth');
});

Route::get('/about', [MainController::class, 'about'])->name('about');
Route::get('/search', [SearchController::class, 'index'])->name('search');
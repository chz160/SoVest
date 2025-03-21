<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate;
use App\Controllers\Admin\DashboardController;
use App\Controllers\ApiController;
use App\Controllers\AuthController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\PredictionController;
use App\Controllers\SearchController;
use App\Controllers\UserController;

// Authentication routes group
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login.form');
Route::post('/login/submit', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register.form');
Route::post('/register/submit', [AuthController::class, 'register'])->name('register.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User routes group
Route::get('/home', [HomeController::class, 'home'])->name('user.home')->middleware('auth');
Route::get('/account', [UserController::class, 'account'])->name('user.account')->middleware('auth');
Route::get('/leaderboard', [UserController::class, 'leaderboard'])->name('user.leaderboard')->middleware('auth');

// Prediction routes group
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

// Page routes
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// API routes
Route::prefix('api')->middleware('api')->name('api.')->group(function () {
    Route::match(['GET', 'POST'], '/predictions', [PredictionController::class, 'apiHandler'])->name('predictions');
    Route::post('/predictions/create', [PredictionController::class, 'store'])->name('predictions.create');
    Route::post('/predictions/update', [PredictionController::class, 'update'])->name('predictions.update');
    Route::post('/predictions/delete', [PredictionController::class, 'delete'])->name('predictions.delete');
    Route::get('/predictions/get', [PredictionController::class, 'apiGetPrediction'])->name('predictions.get');
    Route::get('/search', [ApiController::class, 'search'])->name('search');
    Route::get('/search_stocks', [ApiController::class, 'searchStocks'])->name('search_stocks');
    Route::get('/stocks', [ApiController::class, 'stocks'])->name('stocks');
    Route::get('/stocks/{symbol}', [ApiController::class, 'getStock'])->name('stocks.get')
        ->where('symbol', '[A-Z]{1,5}');
    Route::get('/stocks/{symbol}/price', [ApiController::class, 'getStockPrice'])->name('stocks.price')
        ->where('symbol', '[A-Z]{1,5}');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{id}', [UserController::class, 'view'])->name('users.view');
});

// Test route for API configuration
Route::get('/test-api-config', [ApiController::class, 'testApiConfig']);

// Error routes
Route::get('/404', [ErrorController::class, 'notFound']);
Route::get('/403', [ErrorController::class, 'forbidden']);
Route::get('/500', [ErrorController::class, 'serverError']);
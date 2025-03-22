<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// We're not really using routes here we just want to redirect to main.php

Route::get("/", function() { return Redirect::to("main.php"); });
Route::post('/register/submit', [AuthController::class, 'register'])->name('register.submit');
Route::post('/login/submit', [AuthController::class, 'login'])->name('login.submit');
<?php

use Illuminate\Support\Facades\Route;

// We're not really using routes here we just want to redirect to main.php

Route::get("/", function() { return Redirect::to("main.php"); });
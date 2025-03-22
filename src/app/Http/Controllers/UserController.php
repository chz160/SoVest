<?php

namespace App\Http\Controllers;


class UserController extends Controller
{
    public function home()
    {
        return view('home');
    }
}
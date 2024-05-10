<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::redirect('/', '/dashboard');

// Fix no login route
Route::redirect('/login-redirect', '/login')->name('login');

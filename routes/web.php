<?php

use Illuminate\Support\Facades\Route;

// Route untuk Halaman Login (Default Tampilan Pertama)
Route::get('/', function () {
    return view('auth.login');
})->name('login');

// Route Login Gambar 2 (Split Screen)
Route::get('/login-b', function () {
    return view('auth.login_b');
})->name('login.b');

// Route untuk Halaman Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
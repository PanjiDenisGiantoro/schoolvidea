<?php

use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('home');

//auth
Route::prefix('auth')->group(function () {
    Route::get('/login', function () {
        return view('pages.login');
    })->name('login');
    Route::get('portalcode', function () {
        return view('pages.portalcode');
    });
});

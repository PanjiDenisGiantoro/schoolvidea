<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LembagaunitController;
use App\Http\Controllers\UnitController;


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


Route::prefix('officer')->group(function () {
    Route::get('/', function () {
        return view('pages.data_master.officer.officer');
    })->name('petugas');
});

Route::prefix('lembagaunit')->group(function () {
    Route::get('/', [LembagaunitController::class, 'index'])->name('lembagaunit.index');
    Route::get('/create', [LembagaunitController::class, 'create'])->name('lembagaunit.create');
    Route::post('/store', [LembagaunitController::class, 'store'])->name('lembagaunit.store');
    Route::get('/edit/{id}', [LembagaunitController::class, 'edit'])->name('lembagaunit.edit');
    Route::put('lembagaunit/update/{id}', [LembagaUnitController::class, 'update'])->name('lembagaunit.update');
    Route::get('/delete/{id}', [LembagaunitController::class, 'destroy'])->name('lembagaunit.destroy');
    Route::get('/show/{id}', [LembagaunitController::class, 'show'])->name('lembagaunit.show');
});

Route::prefix('unit')->group(function () {
    Route::get('/', [UnitController::class, 'index'])->name('unit.index');
    Route::get('/create', [UnitController::class, 'create'])->name('unit.create');
    Route::post('/store', [UnitController::class, 'store'])->name('unit.store');
    Route::get('/edit/{id}', [UnitController::class, 'edit'])->name('unit.edit');
    Route::put('unit/update/{id}', [UnitController::class, 'update'])->name('unit.update');
    Route::get('/delete/{id}', [UnitController::class, 'destroy'])->name('unit.destroy');
    Route::get('/show/{id}', [UnitController::class, 'show'])->name('unit.show');
});

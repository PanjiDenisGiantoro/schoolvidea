<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LembagaunitController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TahunajaranController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;


Route::get('/portal', [AuthController::class, 'portalCode'])->name('portal.form');
Route::post('/portalpost', [AuthController::class, 'checkPortalCode'])->name('portal.check');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::prefix('officer')->group(function () {
    Route::get('/', [OfficerController::class, 'index'])->name('officer.index');
    Route::get('/create', [OfficerController::class, 'create'])->name('officer.create');
    Route::post('/store', [OfficerController::class, 'store'])->name('officer.store');
    Route::get('/edit/{id}', [OfficerController::class, 'edit'])->name('officer.edit');
    Route::put('officer/update/{id}', [OfficerController::class, 'update'])->name('officer.update');
    Route::get('/delete/{id}', [OfficerController::class, 'destroy'])->name('officer.destroy');
    Route::get('/show/{id}', [OfficerController::class, 'show'])->name('officer.show');
});
Route::prefix('roles')->group(function () {
    Route::get('/', [RolesController::class, 'index'])->name('roles.index');
    Route::get('/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/store', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/edit/{id}', [RolesController::class, 'edit'])->name('roles.edit');
    Route::put('roles/update/{id}', [RolesController::class, 'update'])->name('roles.update');
    Route::get('/delete/{id}', [RolesController::class, 'destroy'])->name('roles.destroy');
    Route::get('/show/{id}', [RolesController::class, 'show'])->name('roles.show');
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

Route::prefix('tahun_ajaran')->group(function () {
    Route::get('/', [TahunajaranController::class, 'index'])->name('tahun_ajaran.index');
    Route::get('/create', [TahunajaranController::class, 'create'])->name('tahun_ajaran.create');
    Route::post('/store', [TahunajaranController::class, 'store'])->name('tahun_ajaran.store');
    Route::get('/edit/{id}', [TahunajaranController::class, 'edit'])->name('tahun_ajaran.edit');
    Route::put('tahun_ajaran/update/{id}', [TahunajaranController::class, 'update'])->name('tahun_ajaran.update');
    Route::get('/delete/{id}', [TahunajaranController::class, 'destroy'])->name('tahun_ajaran.destroy');
    Route::get('/show/{id}', [TahunajaranController::class, 'show'])->name('tahun_ajaran.show');
});


    Route::prefix('kelas')->group(function () {
        Route::get('/', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/create', [KelasController::class, 'create'])->name('kelas.create');
        Route::post('/store', [KelasController::class, 'store'])->name('kelas.store');
        Route::get('/edit/{id}', [KelasController::class, 'edit'])->name('kelas.edit');
        Route::put('kelas/update/{id}', [KelasController::class, 'update'])->name('kelas.update');
        Route::get('/delete/{id}', [KelasController::class, 'destroy'])->name('kelas.destroy');
        Route::get('/show/{id}', [KelasController::class, 'show'])->name('kelas.show');
    });




});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LembagaunitController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TahunajaranController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KategoritagihanController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\TabunganController;


Route::get('/portal', [AuthController::class, 'portalCode'])->name('portal.form');
Route::get('/portal', [AuthController::class, 'portalCode'])->name('login');
Route::post('/portalpost', [AuthController::class, 'checkPortalCode'])->name('portal.check');
Route::get('/login', [AuthController::class, 'loginForm'])->name('login.form');
Route::get('/login-central', [AuthController::class, 'logincentral'])->name('logincentral.form');
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
    Route::get('/permissions/{id}', [RolesController::class, 'permissions'])->name('roles.permissions');
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

    Route::prefix('jurusan')->group(function () {
        Route::get('/', [JurusanController::class, 'index'])->name('jurusan.index');
        Route::get('/create', [JurusanController::class, 'create'])->name('jurusan.create');
        Route::post('/store', [JurusanController::class, 'store'])->name('jurusan.store');
        Route::get('/edit/{id}', [JurusanController::class, 'edit'])->name('jurusan.edit');
        Route::put('jurusan/update/{id}', [JurusanController::class, 'update'])->name('jurusan.update');
        Route::get('/delete/{id}', [JurusanController::class, 'destroy'])->name('jurusan.destroy');
        Route::get('/show/{id}', [JurusanController::class, 'show'])->name('jurusan.show');
    });
    Route::prefix('siswa')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('/create', [SiswaController::class, 'create'])->name('siswa.create');
        Route::post('/store', [SiswaController::class, 'store'])->name('siswa.store');
        Route::get('/edit/{id}', [SiswaController::class, 'edit'])->name('siswa.edit');
        Route::put('siswa/update/{id}', [SiswaController::class, 'update'])->name('siswa.update');
        Route::get('/delete/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
        Route::get('/show/{id}', [SiswaController::class, 'show'])->name('siswa.show');
        Route::get('/by-kelas/{kelasId}', [App\Http\Controllers\SiswaController::class, 'getByKelas']);
        Route::get('/siswadetail/{id}', [App\Http\Controllers\SiswaController::class, 'showdetail']);

    });
    Route::prefix('kategoritagihan')->group(function () {
        Route::get('/', [KategoritagihanController::class, 'index'])->name('kategoritagihan.index');
        Route::get('/create', [KategoritagihanController::class, 'create'])->name('kategoritagihan.create');
        Route::post('/store', [KategoritagihanController::class, 'store'])->name('kategoritagihan.store');
        Route::get('/edit/{id}', [KategoritagihanController::class, 'edit'])->name('kategoritagihan.edit');
        Route::put('kategoritagihan/update/{id}', [KategoritagihanController::class, 'update'])->name('kategoritagihan.update');
        Route::get('/delete/{id}', [KategoritagihanController::class, 'destroy'])->name('kategoritagihan.destroy');
        Route::get('/show/{id}', [KategoritagihanController::class, 'show'])->name('kategoritagihan.show');
    });

    Route::prefix('tabungan')->group(function () {
        Route::get('/', [TabunganController::class, 'index'])->name('tabungan.index');
        Route::get('/create', [TabunganController::class, 'create'])->name('tabungan.create');
        Route::post('/store', [TabunganController::class, 'store'])->name('tabungan.store');
        Route::get('/{id}', [TabunganController::class, 'show'])->name('tabungan.show'); // detail transaksi
        Route::get('/{id}/edit', [TabunganController::class, 'edit'])->name('tabungan.edit');
        Route::put('/{id}', [TabunganController::class, 'update'])->name('tabungan.update');
        Route::delete('/{id}', [TabunganController::class, 'destroy'])->name('tabungan.destroy');
    });
    Route::prefix('akun')->group(function () {
        Route::get('/', [\App\Http\Controllers\AkunController::class, 'index'])->name('akun.index');
        Route::get('/create', [\App\Http\Controllers\AkunController::class, 'create'])->name('akun.create');
        Route::post('/store', [\App\Http\Controllers\AkunController::class, 'store'])->name('akun.store');
        Route::get('/{id}', [\App\Http\Controllers\AkunController::class, 'show'])->name('akun.show'); // detail transaksi
        Route::get('/{id}/edit', [\App\Http\Controllers\AkunController::class, 'edit'])->name('akun.edit');
        Route::put('/{id}', [\App\Http\Controllers\AkunController::class, 'update'])->name('akun.update');
        Route::delete('/{id}', [\App\Http\Controllers\AkunController::class, 'destroy'])->name('akun.destroy');
    });

});

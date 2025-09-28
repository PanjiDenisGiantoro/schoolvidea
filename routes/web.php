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
use App\Http\Controllers\SettingAkunController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;


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
        Route::get('/{id}/siswa', [KelasController::class, 'getSiswa']);

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
        Route::get('/tarik', [TabunganController::class, 'tarik'])->name('tabungan.tarik');
        Route::post('/store-tarik', [TabunganController::class, 'tarikStore'])->name('tabungan.tarik.store');
        Route::get('/show/{id}', [TabunganController::class, 'show'])->name('tabungan.show');
        Route::get('/status/{id}', [TabunganController::class, 'status'])->name('tabungan.status');
        Route::get('/report', [TabunganController::class, 'report'])->name('tabungan.report');
        Route::get('/report-all', [TabunganController::class, 'reportAll'])->name('tabungan.report-all');

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
    Route::prefix('setting_akun')->group(function () {
        Route::get('/', [SettingAkunController::class, 'index'])->name('setting_akun.index');
        Route::get('/create', [SettingAkunController::class, 'create'])->name('setting_akun.create');
        Route::post('/store', [SettingAkunController::class, 'store'])->name('setting_akun.store');
        Route::get('/{id}', [SettingAkunController::class, 'show'])->name('setting_akun.show'); // detail transaksi
        Route::get('/{id}/edit', [SettingAkunController::class, 'edit'])->name('setting_akun.edit');
        Route::put('/{id}', [SettingAkunController::class, 'update'])->name('setting_akun.update');
        Route::delete('/{id}', [SettingAkunController::class, 'destroy'])->name('setting_akun.destroy');
    });


    Route::prefix('report')->group(function () {
        Route::get('/arus-kas', [JurnalController::class, 'aruskas'])->name('report.arus-kas');
        Route::get('/jurnal', [JurnalController::class, 'jurnal'])->name('report.jurnal');
        Route::get('/buku_besar', [JurnalController::class, 'buku_besar'])->name('report.buku_besar');
        Route::get('/neraca_saldo', [JurnalController::class, 'neraca_saldo'])->name('report.neraca_saldo');
        Route::get('/neraca', [JurnalController::class, 'neraca'])->name('report.neraca');
        Route::get('/labarugi', [JurnalController::class, 'labarugi'])->name('report.labarugi');
    });

    Route::prefix('tagihan')->group(function () {
        Route::get('/', [TagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/create', [TagihanController::class, 'create'])->name('tagihan.create');
        Route::post('/store', [TagihanController::class, 'store'])->name('tagihan.store');
        Route::get('/show/{tagihanId}/{siswaId}', [TagihanController::class, 'show'])->name('tagihan.show');
        Route::get('/bayar/{id}', [TagihanController::class, 'bayar'])->name('tagihan.bayar');
        Route::get('/perbulan/{id}',[TagihanController::class,'perbulan'])->name('tagihan.perbulan');
        Route::get('/daftarTagihan/{id}',[TagihanController::class,'daftarTagihan'])->name('tagihan.daftarTagihan');
        Route::get('/daftarTagihanBebas/{id}',[TagihanController::class,'daftarTagihanBebas'])->name('tagihan.daftarTagihanBebas');
        Route::get('/perbulan/{siswaId}/{tagihanId}', [TagihanController::class, 'perbulan'])->name('tagihan.perbulan');
        Route::get('/bebas/{siswaId}', [TagihanController::class, 'tagihanBebas']);


    });
    Route::prefix('pembayaran')->group(function () {
        Route::get('/', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::post('/store', [PembayaranController::class, 'bayar'])->name('pembayaran.store');
    });


});

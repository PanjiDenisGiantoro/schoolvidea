<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SiswaController;
use App\Http\Controllers\Api\V1\KelasController;
use App\Http\Controllers\Api\V1\OfficerController;
use App\Http\Controllers\Api\V1\TagihanController;
use App\Http\Controllers\Api\V1\DashboardTagihanController;
use App\Http\Controllers\Api\V1\TabunganController;
use App\Http\Controllers\Api\V1\TagihanSiswaController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\TahunAjaranController;
use App\Http\Controllers\Api\V1\JurusanController;
use App\Http\Controllers\Api\V1\KategoriTagihanController;
use App\Http\Controllers\Api\V1\PotonganController;
use App\Http\Controllers\Api\V1\PembayaranController;
use App\Http\Controllers\Api\V1\TagihanSiswaMutasiController;
use App\Http\Controllers\Api\V1\RolesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);

        Route::middleware('auth:api')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    // Protected routes
    Route::middleware('auth:api')->group(function () {

        // Siswa routes
        Route::get('siswa', [SiswaController::class, 'index']);
        Route::get('siswa/{id}', [SiswaController::class, 'show']);
        Route::post('siswa', [SiswaController::class, 'store']);
        Route::put('siswa/{id}', [SiswaController::class, 'update']);
        Route::delete('siswa/{id}', [SiswaController::class, 'destroy']);
        Route::get('siswa/kelas/{kelasId}', [SiswaController::class, 'getByKelas']);

        // Kelas routes
        Route::get('kelas', [KelasController::class, 'index']);
        Route::get('kelas/{id}', [KelasController::class, 'show']);
        Route::post('kelas', [KelasController::class, 'store']);
        Route::put('kelas/{id}', [KelasController::class, 'update']);
        Route::delete('kelas/{id}', [KelasController::class, 'destroy']);
        Route::get('kelas/{id}/siswa', [KelasController::class, 'getSiswa']);

        // Officer routes
        Route::get('officer', [OfficerController::class, 'index']);
        Route::get('officer/{id}', [OfficerController::class, 'show']);
        Route::post('officer', [OfficerController::class, 'store']);
        Route::put('officer/{id}', [OfficerController::class, 'update']);
        Route::delete('officer/{id}', [OfficerController::class, 'destroy']);

        // Tagihan routes
        Route::get('tagihan', [TagihanController::class, 'index']);
        Route::get('tagihan/{id}', [TagihanController::class, 'show']);
        Route::post('tagihan', [TagihanController::class, 'store']);
        Route::put('tagihan/{id}', [TagihanController::class, 'update']);
        Route::delete('tagihan/{id}', [TagihanController::class, 'destroy']);
        Route::get('tagihan/siswa/{siswaId}', [TagihanController::class, 'getBySiswa']);

        // Tagihan Siswa routes
        Route::get('tagihan-siswa/siswa/{siswaId}', [TagihanSiswaController::class, 'getBySiswa']);
        Route::get('tagihan-siswa/kelas/{kelasId}', [TagihanSiswaController::class, 'getByKelas']);
        Route::get('tagihan-siswa/unpaid/{siswaId}', [TagihanSiswaController::class, 'getUnpaid']);
        Route::get('tagihan-siswa', [TagihanSiswaController::class, 'index']);
        Route::get('tagihan-siswa/{id}', [TagihanSiswaController::class, 'show']);

        // Unit routes
        Route::get('unit', [UnitController::class, 'index']);
        Route::get('unit/{id}', [UnitController::class, 'show']);
        Route::post('unit', [UnitController::class, 'store']);
        Route::put('unit/{id}', [UnitController::class, 'update']);
        Route::delete('unit/{id}', [UnitController::class, 'destroy']);

        // Tahun Ajaran routes
        Route::get('tahun-ajaran', [TahunAjaranController::class, 'index']);
        Route::get('tahun-ajaran/{id}', [TahunAjaranController::class, 'show']);
        Route::post('tahun-ajaran', [TahunAjaranController::class, 'store']);
        Route::put('tahun-ajaran/{id}', [TahunAjaranController::class, 'update']);
        Route::delete('tahun-ajaran/{id}', [TahunAjaranController::class, 'destroy']);

        // Jurusan routes
        Route::get('jurusan', [JurusanController::class, 'index']);
        Route::get('jurusan/{id}', [JurusanController::class, 'show']);
        Route::post('jurusan', [JurusanController::class, 'store']);
        Route::put('jurusan/{id}', [JurusanController::class, 'update']);
        Route::delete('jurusan/{id}', [JurusanController::class, 'destroy']);

        // Kategori Tagihan routes
        Route::get('kategori-tagihan', [KategoriTagihanController::class, 'index']);
        Route::get('kategori-tagihan/{id}', [KategoriTagihanController::class, 'show']);
        Route::post('kategori-tagihan', [KategoriTagihanController::class, 'store']);
        Route::put('kategori-tagihan/{id}', [KategoriTagihanController::class, 'update']);
        Route::delete('kategori-tagihan/{id}', [KategoriTagihanController::class, 'destroy']);

        // Potongan routes
        Route::get('potongan', [PotonganController::class, 'index']);
        Route::get('potongan/{id}', [PotonganController::class, 'show']);
        Route::post('potongan', [PotonganController::class, 'store']);
        Route::put('potongan/{id}', [PotonganController::class, 'update']);
        Route::delete('potongan/{id}', [PotonganController::class, 'destroy']);

        // Pembayaran routes
        Route::get('pembayaran/siswa/{siswaId}', [PembayaranController::class, 'getBySiswa']);
        Route::get('pembayaran/kelas/{kelasId}', [PembayaranController::class, 'getByKelas']);
        Route::get('pembayaran/tagihan-siswa/{tagihanSiswaId}', [PembayaranController::class, 'getByTagihanSiswa']);
        Route::get('pembayaran/receipt/{id}', [PembayaranController::class, 'getReceipt']);
        Route::get('pembayaran', [PembayaranController::class, 'index']);
        Route::get('pembayaran/{id}', [PembayaranController::class, 'show']);
        Route::post('pembayaran', [PembayaranController::class, 'store']);
        Route::put('pembayaran/{id}', [PembayaranController::class, 'update']);
        Route::delete('pembayaran/{id}', [PembayaranController::class, 'destroy']);

        // Tagihan Siswa Mutasi routes
        Route::get('tagihan-siswa-mutasi/siswa/{siswaId}', [TagihanSiswaMutasiController::class, 'getBySiswa']);
        Route::post('tagihan-siswa-mutasi/{id}/approve', [TagihanSiswaMutasiController::class, 'approve']);
        Route::post('tagihan-siswa-mutasi/{id}/reject', [TagihanSiswaMutasiController::class, 'reject']);
        Route::get('tagihan-siswa-mutasi', [TagihanSiswaMutasiController::class, 'index']);
        Route::get('tagihan-siswa-mutasi/{id}', [TagihanSiswaMutasiController::class, 'show']);
        Route::post('tagihan-siswa-mutasi', [TagihanSiswaMutasiController::class, 'store']);
        Route::put('tagihan-siswa-mutasi/{id}', [TagihanSiswaMutasiController::class, 'update']);
        Route::delete('tagihan-siswa-mutasi/{id}', [TagihanSiswaMutasiController::class, 'destroy']);

        // Roles routes
        Route::get('roles', [RolesController::class, 'index']);
        Route::get('roles/{id}', [RolesController::class, 'show']);
        Route::post('roles', [RolesController::class, 'store']);
        Route::put('roles/{id}', [RolesController::class, 'update']);
        Route::delete('roles/{id}', [RolesController::class, 'destroy']);
        Route::get('roles/{id}/permissions', [RolesController::class, 'permissions']);
        Route::post('roles/{id}/permissions', [RolesController::class, 'updatePermissions']);

        // Dashboard Tagihan routes
        Route::prefix('dashboard-tagihan')->group(function () {
            Route::get('/', [DashboardTagihanController::class, 'dashboard']);
            Route::get('/list', [DashboardTagihanController::class, 'listTagihan']);
            Route::get('/detail/{id}', [DashboardTagihanController::class, 'detailTagihan']);
        });

        // Tabungan routes
        Route::prefix('tabungan')->group(function () {
            Route::get('/dashboard', [TabunganController::class, 'dashboard']);
            Route::get('/transaksi', [TabunganController::class, 'transaksi']);
            Route::get('/transaksi/{id}', [TabunganController::class, 'detailTransaksi']);
            Route::get('/mutasi', [TabunganController::class, 'mutasiRekening']);
            Route::post('/setor', [TabunganController::class, 'setor']);
            Route::post('/tarik', [TabunganController::class, 'tarik']);
        });
    });
});
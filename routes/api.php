<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SiswaController;
use App\Http\Controllers\Api\V1\KelasController;
use App\Http\Controllers\Api\V1\OfficerController;
use App\Http\Controllers\Api\V1\TagihanController;
use App\Http\Controllers\Api\V1\DashboardTagihanController;
use App\Http\Controllers\Api\V1\TabunganController;

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
Route::prefix("v1")->group(function () {
    // Authentication routes
    Route::prefix("auth")->group(function () {
        Route::post("login", [AuthController::class, "login"]);
        Route::post("register", [AuthController::class, "register"]);

        Route::middleware("auth:api")->group(function () {
            Route::post("logout", [AuthController::class, "logout"]);
            Route::post("refresh", [AuthController::class, "refresh"]);
            Route::get("me", [AuthController::class, "me"]);
        });
    });

    // Protected routes
    Route::middleware("auth:api")->group(function () {
        // Siswa routes
        // Route::apiResource("siswa", SiswaController::class);
        Route::get("siswa/kelas/{kelasId}", [
            SiswaController::class,
            "getByKelas",
        ]);

        // Kelas routes
        // Route::apiResource('kelas', KelasController::class);
        // Route::get('kelas/{id}/siswa', [KelasController::class, 'getSiswa']);

        // Officer routes
        // Route::apiResource('officer', OfficerController::class);

        // Tagihan routes
        // Route::apiResource('tagihan', TagihanController::class);
        Route::get("tagihan/siswa/{siswaId}", [
            TagihanController::class,
            "getBySiswa",
        ]);

        // Tagihan Siswa routes
        Route::get("tagihan-siswa/siswa/{siswaId}", [
            \App\Http\Controllers\Api\V1\TagihanSiswaController::class,
            "getBySiswa",
        ]);
        Route::get("tagihan-siswa/kelas/{kelasId}", [
            \App\Http\Controllers\Api\V1\TagihanSiswaController::class,
            "getByKelas",
        ]);
        Route::get("tagihan-siswa/unpaid/{siswaId}", [
            \App\Http\Controllers\Api\V1\TagihanSiswaController::class,
            "getUnpaid",
        ]);
        // Route::apiResource('tagihan-siswa', \App\Http\Controllers\Api\V1\TagihanSiswaController::class)->only(['index', 'show']);

        // Unit routes
        // Route::apiResource(
        //     "unit",
        //     \App\Http\Controllers\Api\V1\UnitController::class,
        // );

        // Tahun Ajaran routes
        // Route::apiResource('tahun-ajaran', \App\Http\Controllers\Api\V1\TahunAjaranController::class);

        // Jurusan routes
        // Route::apiResource('jurusan', \App\Http\Controllers\Api\V1\JurusanController::class);

        // Kategori Tagihan routes
        // Route::apiResource('kategori-tagihan', \App\Http\Controllers\Api\V1\KategoriTagihanController::class);

        // Potongan routes
        // Route::apiResource('potongan', \App\Http\Controllers\Api\V1\PotonganController::class);

        // Pembayaran routes
        Route::get("pembayaran/pending-approval", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "pendingApproval",
        ]);
        Route::get("pembayaran/siswa/{siswaId}", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "getBySiswa",
        ]);
        Route::get("pembayaran/kelas/{kelasId}", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "getByKelas",
        ]);
        Route::get("pembayaran/tagihan-siswa/{tagihanSiswaId}", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "getByTagihanSiswa",
        ]);
        Route::get("pembayaran/receipt/{id}", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "getReceipt",
        ]);
        Route::post("pembayaran/{id}/upload-bukti", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "uploadBukti",
        ]);
        Route::post("pembayaran/{id}/approve", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "approve",
        ]);
        Route::post("pembayaran/{id}/reject", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "reject",
        ]);
        Route::apiResource(
            "pembayaran",
            \App\Http\Controllers\Api\V1\PembayaranController::class,
        );

        // Tagihan Siswa Mutasi routes
        Route::get("tagihan-siswa-mutasi/siswa/{siswaId}", [
            \App\Http\Controllers\Api\V1\TagihanSiswaMutasiController::class,
            "getBySiswa",
        ]);
        Route::post("tagihan-siswa-mutasi/{id}/approve", [
            \App\Http\Controllers\Api\V1\TagihanSiswaMutasiController::class,
            "approve",
        ]);
        Route::post("tagihan-siswa-mutasi/{id}/reject", [
            \App\Http\Controllers\Api\V1\TagihanSiswaMutasiController::class,
            "reject",
        ]);
        Route::apiResource(
            "tagihan-siswa-mutasi",
            \App\Http\Controllers\Api\V1\TagihanSiswaMutasiController::class,
        );

        // Roles routes
        // Route::apiResource(
        //     "roles",
        //     \App\Http\Controllers\Api\V1\RolesController::class,
        // );
        Route::get("roles/{id}/permissions", [
            \App\Http\Controllers\Api\V1\RolesController::class,
            "permissions",
        ]);
        Route::post("roles/{id}/permissions", [
            \App\Http\Controllers\Api\V1\RolesController::class,
            "updatePermissions",
        ]);

        // Dashboard Tagihan routes
        Route::prefix("dashboard-tagihan")->group(function () {
            Route::get("/", [DashboardTagihanController::class, "dashboard"]);
            Route::get("/list", [
                DashboardTagihanController::class,
                "listTagihan",
            ]);
            Route::get("/detail/{id}", [
                DashboardTagihanController::class,
                "detailTagihan",
            ]);
        });

        // Tabungan routes
        Route::prefix("tabungan")->group(function () {
            Route::get("/dashboard", [TabunganController::class, "dashboard"]);
            Route::get("/transaksi", [TabunganController::class, "transaksi"]);
            Route::get("/transaksi/{id}", [
                TabunganController::class,
                "detailTransaksi",
            ]);
            Route::get("/mutasi", [
                TabunganController::class,
                "mutasiRekening",
            ]);
            Route::post("/setor", [TabunganController::class, "setor"]);
            Route::post("/tarik", [TabunganController::class, "tarik"]);
        });
    });
});

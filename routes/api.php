<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SiswaController;
use App\Http\Controllers\Api\V1\KelasController;
use App\Http\Controllers\Api\V1\OfficerController;
use App\Http\Controllers\Api\V1\TagihanController;
use App\Http\Controllers\Api\V1\DashboardTagihanController;
use App\Http\Controllers\Api\V1\TabunganController;
use App\Http\Controllers\Api\V1\RiwayatApiController;
use App\Http\Controllers\Api\V1\UnitListController;
use App\Http\Controllers\Api\V1\YayasanListController;
use App\Http\Controllers\Api\V1\TipeunitListController;
use App\Http\Controllers\Api\V1\DataRekeningListController;
use App\Http\Controllers\Api\V1\JurusanListController;
use App\Http\Controllers\Api\V1\MeDataRekeningController;

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
        Route::post("login-nisn", [AuthController::class, "loginWithNisn"]);
        Route::post("register", [AuthController::class, "register"]);

        Route::middleware("auth:api")->group(function () {
            Route::post("logout", [AuthController::class, "logout"]);
            Route::post("refresh", [AuthController::class, "refresh"]);
            Route::get("me", [AuthController::class, "me"]);
            Route::get("me/data-rekenings", [MeDataRekeningController::class, "index"]);
        });
    });

    // Protected routes
    Route::middleware('auth:api')->group(function () {

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
        Route::get("tagihan/daftarTagihan/{siswaId}", [
            TagihanController::class,
            "daftarTagihan",
        ]);
        Route::get("tagihan/perbulan/{siswaId}/{tagihanId}", [
            TagihanController::class,
            "perbulan",
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

        // List API v1 routes (index only)
        Route::get('units/list', [UnitListController::class, 'index']);
        Route::get('yayasans/list', [YayasanListController::class, 'index']);
        Route::get('tipeunits/list', [TipeunitListController::class, 'index']);
        Route::get('data-rekenings/list', [DataRekeningListController::class, 'index']);
        Route::get('jurusans/list', [JurusanListController::class, 'index']);

        // Tahun Ajaran routes
        // Route::apiResource('tahun-ajaran', \App\Http\Controllers\Api\V1\TahunAjaranController::class);

        // Jurusan routes
        // Route::apiResource('jurusan', \App\Http\Controllers\Api\V1\JurusanController::class);

        // Kategori Tagihan routes
        // Route::apiResource('kategori-tagihan', \App\Http\Controllers\Api\V1\KategoriTagihanController::class);

        // Potongan routes
        // Route::apiResource('potongan', \App\Http\Controllers\Api\V1\PotonganController::class);

        // Pembayaran routes
        Route::post("pembayaran/proses-multiple-with-detail", [
            \App\Http\Controllers\Api\V1\PembayaranController::class,
            "prosesPembayaranMultipleWithDetail",
        ]);
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

            // New Tabungan API v1 routes
            Route::get("/transaksi", [\App\Http\Controllers\Api\V1\TabunganApiController::class, "transaksi"]);
            Route::post("/setor", [\App\Http\Controllers\Api\V1\TabunganApiController::class, "setor"]);
            Route::post("/tarik", [\App\Http\Controllers\Api\V1\TabunganApiController::class, "tarik"]);
            Route::post("/{id}/upload-bukti", [\App\Http\Controllers\Api\V1\TabunganApiController::class, "uploadBukti"]);
            Route::get("/{id}/detail", [\App\Http\Controllers\Api\V1\TabunganApiController::class, "detail"]);
            Route::post("/{id}/approve", [\App\Http\Controllers\Api\V1\TabunganApiController::class, "approve"]);
            Route::post("/{id}/reject", [\App\Http\Controllers\Api\V1\TabunganApiController::class, "reject"]);

            Route::get("/transaksi/{id}", [
                TabunganController::class,
                "detailTransaksi",
            ]);
            Route::get("/mutasi", [
                TabunganController::class,
                "mutasiRekening",
            ]);
            Route::post("/verify", [TabunganController::class, "verifyToken"]);
            Route::post("/regenerate-token", [TabunganController::class, "regenerateToken"]);
        });

        // Riwayat (History) routes
        Route::prefix("riwayat")->group(function () {
            // Main history endpoints
            Route::get("/", [RiwayatApiController::class, "index"]);
            Route::get("/dashboard", [RiwayatApiController::class, "dashboard"]);

            // Tabungan history
            Route::get("/tabungan", [RiwayatApiController::class, "riwayatTabungan"]);
            Route::get("/tabungan/setor", [RiwayatApiController::class, "riwayatTabunganSetor"]);
            Route::get("/tabungan/tarik", [RiwayatApiController::class, "riwayatTabunganTarik"]);
            Route::get("/tabungan/siswa/{siswaId}", [RiwayatApiController::class, "riwayatTabunganSiswa"]);
            Route::get("/tabungan/{id}", [RiwayatApiController::class, "detailTabungan"]);

            // Tagihan history
            Route::get("/tagihan", [RiwayatApiController::class, "riwayatTagihan"]);
            Route::get("/tagihan/siswa/{siswaId}", [RiwayatApiController::class, "riwayatTagihanSiswa"]);
            Route::get("/tagihan/{id}", [RiwayatApiController::class, "detailTagihan"]);

            // Tagihan pembayaran history
            Route::get("/tagihan-pembayaran", [RiwayatApiController::class, "riwayatPembayaranTagihan"]);

            // Tagihan mutasi history
            Route::get("/tagihan-mutasi", [RiwayatApiController::class, "riwayatMutasiTagihan"]);
            Route::get("/tagihan-mutasi/{siswaId}", [RiwayatApiController::class, "riwayatMutasiTagihanSiswa"]);

            // Audit trail
            Route::get("/audit-trail/{transactionId}", [RiwayatApiController::class, "auditTrail"]);
        });
    });
});

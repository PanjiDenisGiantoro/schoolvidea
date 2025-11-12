<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\AkunUserController;
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
use App\Http\Controllers\KeuanganTransaksiController;
use App\Http\Controllers\SettingAkunController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\PayrollComponentsController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PotonganController;
use App\Http\Controllers\TipeunitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\PayrollDeductionsController;
use App\Http\Controllers\PayrollSettingController;

Route::prefix('landing')->group(function () {
    Route::get('/registerpublic', [\App\Http\Controllers\TrialRegistrationController::class, 'showForm'])->name('landing.registerpublic');
    Route::post('/store', [\App\Http\Controllers\TrialRegistrationController::class, 'store'])->name('landing.store');
    Route::get('/registration_portal/{id}', [\App\Http\Controllers\TrialRegistrationController::class, 'registrationPortal'])->name('landing.registration_portal');
    Route::put('/storeportal/{id}', [\App\Http\Controllers\TrialRegistrationController::class, 'storePortal'])->name('landing.storeportal');
    Route::get('/success', function () {
        return view('pages.notif.notif_success');
    })->name('landing.success');
    Route::get('/successregister', function () {
        return view('pages.notif.notif_register_success');
    })->name('landing.successregister');
});
Route::get('/login', [AuthController::class, 'portalCode'])->name('login');
Route::post('/portalpost', [AuthController::class, 'checkPortalCode'])->name('portal.check');
Route::get('/login-central', [AuthController::class, 'portalcentral'])->name('logincentral.form');
Route::post('/loginprocess', [AuthController::class, 'portal'])->name('login.process');
Route::get('/portal', [AuthController::class, 'loginForm'])->name('login.form');

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile/', [AuthController::class, 'showupdate'])->name('profile.showupdate');
    Route::put('/profile/update-password', [AuthController::class, 'updatePassword'])->name('profile.updatePassword');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->middleware('permission:view_dashboard')
        ->name('dashboard');
    Route::prefix('officer')->middleware('permission:view_officer')->group(function () {
        Route::get('/', [OfficerController::class, 'index'])->name('officer.index');
        Route::get('/create', [OfficerController::class, 'create'])->middleware('permission:create_officer')->name('officer.create');
        Route::post('/store', [OfficerController::class, 'store'])->middleware('permission:create_officer')->name('officer.store');
        Route::get('/edit/{id}', [OfficerController::class, 'edit'])->middleware('permission:edit_officer')->name('officer.edit');
        Route::put('officer/update/{id}', [OfficerController::class, 'update'])->middleware('permission:edit_officer')->name('officer.update');
        Route::get('/delete/{id}', [OfficerController::class, 'destroy'])->middleware('permission:delete_officer')->name('officer.destroy');
        Route::get('/show/{id}', [OfficerController::class, 'show'])->name('officer.show');
        Route::post('/upload', [OfficerController::class, 'upload'])->middleware('permission:upload_officer')->name('officer.upload');
    });

    Route::prefix('roles')->middleware('permission:view_role')->group(function () {
        Route::get('/', [RolesController::class, 'index'])->name('roles.index');
        Route::get('/create', [RolesController::class, 'create'])->middleware('permission:create_role')->name('roles.create');
        Route::post('/store', [RolesController::class, 'store'])->middleware('permission:create_role')->name('roles.store');
        Route::get('/edit/{id}', [RolesController::class, 'edit'])->middleware('permission:edit_role')->name('roles.edit');
        Route::put('roles/update/{id}', [RolesController::class, 'update'])->middleware('permission:edit_role')->name('roles.update');
        Route::get('/delete/{id}', [RolesController::class, 'destroy'])->middleware('permission:delete_role')->name('roles.destroy');
        Route::get('/show/{id}', [RolesController::class, 'show'])->name('roles.show');

        // Permission Management
        Route::get('/{id}/permissions', [RolesController::class, 'permissions'])->middleware('permission:manage_permission_role')->name('roles.permissions');
        Route::post('/{id}/permissions', [RolesController::class, 'updatePermissions'])->middleware('permission:manage_permission_role')->name('roles.updatePermissions');

        // User Management
        Route::get('/{id}/users', [RolesController::class, 'users'])->middleware('permission:manage_permission_role')->name('roles.users');
        Route::post('/{id}/assign-user', [RolesController::class, 'assignUser'])->middleware('permission:manage_permission_role')->name('roles.assignUser');
        Route::post('/{id}/remove-user', [RolesController::class, 'removeUser'])->middleware('permission:manage_permission_role')->name('roles.removeUser');

        // Clone Role
        Route::post('/{id}/clone', [RolesController::class, 'clone'])->middleware('permission:create_role')->name('roles.clone');
    });

    Route::prefix('lembagaunit')->middleware('permission:view_lembagaunit')->group(function () {
        Route::get('/', [LembagaunitController::class, 'index'])->name('lembagaunit.index');
        Route::get('/create', [LembagaunitController::class, 'create'])->middleware('permission:create_lembagaunit')->name('lembagaunit.create');
        Route::post('/store', [LembagaunitController::class, 'store'])->middleware('permission:create_lembagaunit')->name('lembagaunit.store');
        Route::get('/edit/{id}', [LembagaunitController::class, 'edit'])->middleware('permission:edit_lembagaunit')->name('lembagaunit.edit');
        Route::put('lembagaunit/update/{id}', [LembagaUnitController::class, 'update'])->middleware('permission:edit_lembagaunit')->name('lembagaunit.update');
        Route::get('/delete/{id}', [LembagaunitController::class, 'destroy'])->middleware('permission:delete_lembagaunit')->name('lembagaunit.destroy');
        Route::get('/show/{id}', [LembagaunitController::class, 'show'])->name('lembagaunit.show');
        Route::post('/upload', [LembagaUnitController::class, 'upload'])->middleware('permission:upload_lembagaunit')->name('lembagaunit.upload');
    });

    Route::prefix('unit')->middleware('permission:view_unit')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('unit.index');
        Route::get('/create', [UnitController::class, 'create'])->middleware('permission:create_unit')->name('unit.create');
        Route::post('/store', [UnitController::class, 'store'])->middleware('permission:create_unit')->name('unit.store');
        Route::get('/edit/{id}', [UnitController::class, 'edit'])->middleware('permission:edit_unit')->name('unit.edit');
        Route::put('unit/update/{id}', [UnitController::class, 'update'])->middleware('permission:edit_unit')->name('unit.update');
        Route::get('/delete/{id}', [UnitController::class, 'destroy'])->middleware('permission:delete_unit')->name('unit.destroy');
        Route::get('/show/{id}', [UnitController::class, 'show'])->name('unit.show');
        Route::post('/upload', [UnitController::class, 'upload'])->middleware('permission:upload_unit')->name('unit.upload');
        Route::get('/{unitId}/kelas', [UnitController::class, 'listkelas'])->name('unit.kelas');
        Route::get('/by-unit/{unitId}', [\App\Http\Controllers\UnitController::class, 'getKelasByUnit']);

    });

    Route::prefix('tahun_ajaran')->middleware('permission:view_tahun_ajaran')->group(function () {
        Route::get('/', [TahunajaranController::class, 'index'])->name('tahun_ajaran.index');
        Route::get('/create', [TahunajaranController::class, 'create'])->middleware('permission:create_tahun_ajaran')->name('tahun_ajaran.create');
        Route::post('/store', [TahunajaranController::class, 'store'])->middleware('permission:create_tahun_ajaran')->name('tahun_ajaran.store');
        Route::get('/edit/{id}', [TahunajaranController::class, 'edit'])->middleware('permission:edit_tahun_ajaran')->name('tahun_ajaran.edit');
        Route::put('tahun_ajaran/update/{id}', [TahunajaranController::class, 'update'])->middleware('permission:edit_tahun_ajaran')->name('tahun_ajaran.update');
        Route::get('/delete/{id}', [TahunajaranController::class, 'destroy'])->middleware('permission:delete_tahun_ajaran')->name('tahun_ajaran.destroy');
        Route::get('/show/{id}', [TahunajaranController::class, 'show'])->name('tahun_ajaran.show');
    });

    Route::prefix('tipe_unit')->middleware('permission:view_tipe_unit')->group(function () {
        Route::get('/', [TipeunitController::class, 'index'])->name('tipe_unit.index');
        Route::get('/create', [TipeunitController::class, 'create'])->middleware('permission:create_tipe_unit')->name('tipe_unit.create');
        Route::post('/store', [TipeunitController::class, 'store'])->middleware('permission:create_tipe_unit')->name('tipe_unit.store');
        Route::get('/edit/{id}', [TipeunitController::class, 'edit'])->middleware('permission:edit_tipe_unit')->name('tipe_unit.edit');
        Route::put('tipe_unit/update/{id}', [TipeunitController::class, 'update'])->middleware('permission:edit_tipe_unit')->name('tipe_unit.update');
        Route::get('/delete/{id}', [TipeunitController::class, 'destroy'])->middleware('permission:delete_tipe_unit')->name('tipe_unit.destroy');
        Route::get('/show/{id}', [TipeunitController::class, 'show'])->name('tipe_unit.show');
    });


    Route::prefix('kelas')->middleware('permission:view_kelas')->group(function () {
        Route::get('/', [KelasController::class, 'index'])->name('kelas.index');
        Route::get('/create', [KelasController::class, 'create'])->middleware('permission:create_kelas')->name('kelas.create');
        Route::post('/store', [KelasController::class, 'store'])->middleware('permission:create_kelas')->name('kelas.store');
        Route::get('/edit/{id}', [KelasController::class, 'edit'])->middleware('permission:edit_kelas')->name('kelas.edit');
        Route::put('kelas/update/{id}', [KelasController::class, 'update'])->middleware('permission:edit_kelas')->name('kelas.update');
        Route::get('/delete/{id}', [KelasController::class, 'destroy'])->middleware('permission:delete_kelas')->name('kelas.destroy');
        Route::get('/show/{id}', [KelasController::class, 'show'])->name('kelas.show');
        Route::get('/{id}/siswa', [KelasController::class, 'getSiswa']);
        Route::get('/walikelas-by-unit/{unitId}', [KelasController::class, 'getWaliKelasByUnit']);
        Route::get('/jurusan-by-unit/{unitId}', [KelasController::class, 'getJurusanByUnit']);
        Route::get('/by-unit/{unitId}', [KelasController::class, 'getKelasByUnit']);
    });

    Route::prefix('jurusan')->middleware('permission:view_jurusan')->group(function () {
        Route::get('/', [JurusanController::class, 'index'])->name('jurusan.index');
        Route::get('/create', [JurusanController::class, 'create'])->middleware('permission:create_jurusan')->name('jurusan.create');
        Route::post('/store', [JurusanController::class, 'store'])->middleware('permission:create_jurusan')->name('jurusan.store');
        Route::get('/edit/{id}', [JurusanController::class, 'edit'])->middleware('permission:edit_jurusan')->name('jurusan.edit');
        Route::put('jurusan/update/{id}', [JurusanController::class, 'update'])->middleware('permission:edit_jurusan')->name('jurusan.update');
        Route::get('/delete/{id}', [JurusanController::class, 'destroy'])->middleware('permission:delete_jurusan')->name('jurusan.destroy');
        Route::get('/show/{id}', [JurusanController::class, 'show'])->name('jurusan.show');
    });

    Route::prefix('siswa')->middleware('permission:view_siswa')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->name('siswa.index');
        Route::get('/create', [SiswaController::class, 'create'])->middleware('permission:create_siswa')->name('siswa.create');
        Route::post('/store', [SiswaController::class, 'store'])->middleware('permission:create_siswa')->name('siswa.store');
        Route::get('/edit/{id}', [SiswaController::class, 'edit'])->middleware('permission:edit_siswa')->name('siswa.edit');
        Route::put('/update/{id}', [SiswaController::class, 'update'])->middleware('permission:edit_siswa')->name('siswa.update');
        Route::get('/delete/{id}', [SiswaController::class, 'destroy'])->middleware('permission:delete_siswa')->name('siswa.destroy');
        Route::get('/show/{id}', [SiswaController::class, 'show'])->name('siswa.show');
        Route::get('/by-kelas/{kelasId}', [App\Http\Controllers\SiswaController::class, 'getByKelas']);
        Route::get('/siswadetail/{id}', [App\Http\Controllers\SiswaController::class, 'showdetail']);
        Route::post('/upload', [SiswaController::class, 'upload'])->middleware('permission:upload_siswa')->name('siswa.upload');
        Route::get('/get-kelas/{unitId}', [UnitController::class, 'getKelasByUnit'])->name('getKelasByUnit');
        Route::get('/get-jurusan/{unitId}', [SiswaController::class, 'getJurusanByUnit'])->name('getJurusanByUnit');
        Route::get('/kelas-by-unit/{unitId}', [SiswaController::class, 'getKelasByUnit']);
        // routes/web.php
        Route::post('/siswa/check-unique', [SiswaController::class, 'checkUnique'])->name('siswa.checkUnique');
        Route::get('/jurusan-by-unit/{unitId}', [SiswaController::class, 'getJurusanByUnit']);
    });

    Route::prefix('kategoritagihan')->middleware('permission:view_kategoritagihan')->group(function () {
        Route::get('/', [KategoritagihanController::class, 'index'])->name('kategoritagihan.index');
        Route::get('/create', [KategoritagihanController::class, 'create'])->middleware('permission:create_kategoritagihan')->name('kategoritagihan.create');
        Route::post('/store', [KategoritagihanController::class, 'store'])->middleware('permission:create_kategoritagihan')->name('kategoritagihan.store');
        Route::get('/edit/{id}', [KategoritagihanController::class, 'edit'])->middleware('permission:edit_kategoritagihan')->name('kategoritagihan.edit');
        Route::put('kategoritagihan/update/{id}', [KategoritagihanController::class, 'update'])->middleware('permission:edit_kategoritagihan')->name('kategoritagihan.update');
        Route::get('/delete/{id}', [KategoritagihanController::class, 'destroy'])->middleware('permission:delete_kategoritagihan')->name('kategoritagihan.destroy');
        Route::get('/show/{id}', [KategoritagihanController::class, 'show'])->name('kategoritagihan.show');
    });

    Route::prefix('tabungan')->middleware('permission:view_tabungan')->group(function () {
        Route::get('/', [TabunganController::class, 'index'])->name('tabungan.index');
        Route::get('/create', [TabunganController::class, 'create'])->middleware('permission:create_tabungan')->name('tabungan.create');
        Route::post('/store', [TabunganController::class, 'store'])->middleware('permission:create_tabungan')->name('tabungan.store');
        Route::get('/tarik', [TabunganController::class, 'tarik'])->middleware('permission:create_tabungan')->name('tabungan.tarik');
        Route::post('/store-tarik', [TabunganController::class, 'tarikStore'])->middleware('permission:create_tabungan')->name('tabungan.tarik.store');
        Route::get('/show/{id}', [TabunganController::class, 'show'])->name('tabungan.show');
        Route::post('/verify-token', [TabunganController::class, 'verifyToken'])->name('tabungan.verify');
        Route::get('/status/{id}', [TabunganController::class, 'status'])->name('tabungan.status');
        Route::get('/report', [TabunganController::class, 'report'])->name('tabungan.report');
        Route::get('/report-all', [TabunganController::class, 'reportAll'])->name('tabungan.report-all');
        Route::get('/print-laporan', [TabunganController::class, 'printLaporan'])->name('tabungan.print_laporan');

        // Bukti transfer & verifikasi
        Route::post('/upload-bukti/{id}', [TabunganController::class, 'uploadBuktiTransfer'])->name('tabungan.upload_bukti');
        Route::post('/approve/{id}', [TabunganController::class, 'approveTransaksi'])->middleware('permission:edit_tabungan')->name('tabungan.approve');
        Route::post('/reject/{id}', [TabunganController::class, 'rejectTransaksi'])->middleware('permission:edit_tabungan')->name('tabungan.reject');
    });

    Route::prefix('keuangan-transaksi')->middleware('permission:view_report')->group(function () {
        Route::get('/', [KeuanganTransaksiController::class, 'index'])->name('keuangan_transaksi.index');
        Route::get('/show/{id}', [KeuanganTransaksiController::class, 'show'])->name('keuangan_transaksi.show');
        Route::get('/print-laporan', [KeuanganTransaksiController::class, 'printLaporan'])->name('keuangan_transaksi.print_laporan');
        Route::get('/print-detail/{id}', [KeuanganTransaksiController::class, 'printDetail'])->name('keuangan_transaksi.print_detail');

        // Approve/Reject routes
        Route::post('/approve/{id}', [KeuanganTransaksiController::class, 'approve'])->name('keuangan_transaksi.approve');
        Route::post('/reject/{id}', [KeuanganTransaksiController::class, 'reject'])->name('keuangan_transaksi.reject');
    });

    Route::prefix('akun')->middleware('permission:view_akun')->group(function () {
        Route::get('/', [\App\Http\Controllers\AkunController::class, 'index'])->name('akun.index');
        Route::get('/create', [\App\Http\Controllers\AkunController::class, 'create'])->middleware('permission:create_akun')->name('akun.create');
        Route::post('/store', [\App\Http\Controllers\AkunController::class, 'store'])->middleware('permission:create_akun')->name('akun.store');
        Route::get('/show/{id}', [\App\Http\Controllers\AkunController::class, 'show'])->name('akun.show');
        Route::get('/{id}/edit', [\App\Http\Controllers\AkunController::class, 'edit'])->middleware('permission:edit_akun')->name('akun.edit');
        Route::put('/{id}', [\App\Http\Controllers\AkunController::class, 'update'])->middleware('permission:edit_akun')->name('akun.update');
        Route::get('/destroy/{id}', [\App\Http\Controllers\AkunController::class, 'destroy'])->middleware('permission:delete_akun')->name('akun.destroy');
    });

    Route::prefix('setting_akun')->middleware('permission:view_setting_akun')->group(function () {
        Route::get('/', [SettingAkunController::class, 'index'])->name('setting_akun.index');
        Route::get('/create', [SettingAkunController::class, 'create'])->middleware('permission:create_setting_akun')->name('setting_akun.create');
        Route::post('/store', [SettingAkunController::class, 'store'])->middleware('permission:create_setting_akun')->name('setting_akun.store');
        Route::get('/{id}', [SettingAkunController::class, 'show'])->name('setting_akun.show');
        Route::get('/{id}/edit', [SettingAkunController::class, 'edit'])->middleware('permission:edit_setting_akun')->name('setting_akun.edit');
        Route::put('/{id}', [SettingAkunController::class, 'update'])->middleware('permission:edit_setting_akun')->name('setting_akun.update');
        Route::delete('/{id}', [SettingAkunController::class, 'destroy'])->middleware('permission:delete_setting_akun')->name('setting_akun.destroy');
    });

    Route::prefix('report')->middleware('permission:view_report')->group(function () {
        Route::get('/arus-kas', [JurnalController::class, 'aruskas'])->name('report.arus-kas');
        Route::get('/jurnal', [JurnalController::class, 'jurnal'])->name('report.jurnal');
        Route::get('/buku_besar', [JurnalController::class, 'buku_besar'])->name('report.buku_besar');
        Route::get('/neraca_saldo', [JurnalController::class, 'neraca_saldo'])->name('report.neraca_saldo');
        Route::get('/neraca', [JurnalController::class, 'neraca'])->name('report.neraca');
        Route::get('/labarugi', [JurnalController::class, 'labarugi'])->name('report.labarugi');
    });

    Route::prefix('tagihan')->middleware('permission:view_tagihan')->group(function () {
        Route::get('/', [TagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/create', [TagihanController::class, 'create'])->middleware('permission:create_tagihan')->name('tagihan.create');
        Route::post('/store', [TagihanController::class, 'store'])->middleware('permission:create_tagihan')->name('tagihan.store');
        Route::get('/show/{tagihanId}/{siswaId}', [TagihanController::class, 'show'])->name('tagihan.show');
        Route::get('/bayar/{id}', [TagihanController::class, 'bayar'])->name('tagihan.bayar');
        Route::get('/perbulan/{siswaId}', [TagihanController::class, 'perbulanAll'])->name('tagihan.perbulanAll');
        Route::get('/daftarTagihan/{id}', [TagihanController::class, 'daftarTagihan'])->name('tagihan.daftarTagihan');
        Route::get('/daftarTagihanBebas/{id}', [TagihanController::class, 'daftarTagihanBebas'])->name('tagihan.daftarTagihanBebas');
        Route::get('/perbulan/{siswaId}/{tagihanId}', [TagihanController::class, 'perbulan'])->name('tagihan.perbulan');
        Route::get('/bebas/{siswaId}', [TagihanController::class, 'tagihanBebas']);
        Route::get('/print-laporan', [TagihanController::class, 'printLaporan'])->name('tagihan.print_laporan');
    });

    Route::prefix('potongan')->middleware('permission:view_potongan')->group(function () {
        Route::get('/', [PotonganController::class, 'index'])->name('potongan.index');
        Route::get('/create', [PotonganController::class, 'create'])->middleware('permission:create_potongan')->name('potongan.create');
        Route::post('/store', [PotonganController::class, 'store'])->middleware('permission:create_potongan')->name('potongan.store');
        Route::get('/show/{id}', [PotonganController::class, 'show'])->name('potongan.show');
        Route::get('/edit/{id}', [PotonganController::class, 'edit'])->middleware('permission:edit_potongan')->name('potongan.edit');
        Route::put('potongan/update/{id}', [PotonganController::class, 'update'])->middleware('permission:edit_potongan')->name('potongan.update');
        Route::get('/delete/{id}', [PotonganController::class, 'destroy'])->middleware('permission:delete_potongan')->name('potongan.destroy');
    });

    Route::prefix('pembayaran')->middleware('permission:view_pembayaran')->group(function () {
        Route::get('/', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::post('/store', [PembayaranController::class, 'bayar'])->name('pembayaran.store');
        Route::post('/catatan', [TagihanController::class, 'simpanCatatan'])->name('pembayaran.catatan');
    });

    Route::prefix('migrasi')->middleware('permission:view_migrasi')->group(function () {
        Route::get('/import', [\App\Http\Controllers\MigrasiController::class, 'index'])->name('import.index');
        Route::post('/import/siswa', [\App\Http\Controllers\MigrasiController::class, 'importSiswa'])->middleware('permission:import_migrasi')->name('import.siswa');
        Route::post('/import/kelas', [\App\Http\Controllers\MigrasiController::class, 'importKelas'])->middleware('permission:import_migrasi')->name('import.kelas');
        Route::post('/import/officer', [\App\Http\Controllers\MigrasiController::class, 'importOfficer'])->middleware('permission:import_migrasi')->name('import.officer');
        Route::post('/import/jurusan', [\App\Http\Controllers\MigrasiController::class, 'importJurusan'])->middleware('permission:import_migrasi')->name('import.jurusan');
        Route::get('/import/template/{type}', [\App\Http\Controllers\MigrasiController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/officerexport', [\App\Http\Controllers\MigrasiController::class, 'exportOfficer'])->middleware('permission:export_migrasi')->name('export.officerexport');
        Route::get('/exportkelas/', [\App\Http\Controllers\MigrasiController::class, 'exportkelas'])->middleware('permission:export_migrasi')->name('export.exportkelas');
        Route::get('/jurusantkelas/', [\App\Http\Controllers\MigrasiController::class, 'jurusantkelas'])->middleware('permission:export_migrasi')->name('export.jurusantkelas');
        Route::get('/siswatkelas/', [\App\Http\Controllers\MigrasiController::class, 'exportSiswa'])->middleware('permission:export_migrasi')->name('export.exportSiswa');
    });

    Route::prefix('rekening')->middleware('permission:view_rekening')->group(function () {
        Route::get('/', [\App\Http\Controllers\RekeningController::class, 'index'])->name('rekening.index');
        Route::get('/create', [\App\Http\Controllers\RekeningController::class, 'create'])->middleware('permission:create_rekening')->name('rekening.create');
        Route::post('/store', [\App\Http\Controllers\RekeningController::class, 'store'])->middleware('permission:create_rekening')->name('rekening.store');
        Route::get('/edit/{id}', [\App\Http\Controllers\RekeningController::class, 'edit'])->middleware('permission:edit_rekening')->name('rekening.edit');
        Route::put('/update/{id}', [\App\Http\Controllers\RekeningController::class, 'update'])->middleware('permission:edit_rekening')->name('rekening.update');
        Route::get('/delete/{id}', [\App\Http\Controllers\RekeningController::class, 'destroy'])->middleware('permission:delete_rekening')->name('rekening.destroy');
        Route::get('/show/{id}', [\App\Http\Controllers\RekeningController::class, 'show'])->name('rekening.show');
    });

    Route::prefix('positions')->middleware('permission:view_positions')->group(function () {
        Route::get('/', [PositionsController::class, 'index'])->name('positions.index');
        Route::get('/create', [PositionsController::class, 'create'])->middleware('permission:create_positions')->name('positions.create');
        Route::post('/store', [PositionsController::class, 'store'])->middleware('permission:create_positions')->name('positions.store');
        Route::get('/edit/{id}', [PositionsController::class, 'edit'])->middleware('permission:edit_positions')->name('positions.edit');
        Route::put('positions/update/{id}', [PositionsController::class, 'update'])->middleware('permission:edit_positions')->name('positions.update');
        Route::get('/delete/{id}', [PositionsController::class, 'destroy'])->middleware('permission:delete_positions')->name('positions.destroy');
        Route::get('/show/{id}', [PositionsController::class, 'show'])->name('positions.show');
    });

    Route::prefix('payroll-components')->middleware('permission:view_payroll_components')->group(function () {
        Route::get('/', [PayrollComponentsController::class, 'index'])->name('payroll_components.index');
        Route::get('/create', [PayrollComponentsController::class, 'create'])->middleware('permission:create_payroll_components')->name('payroll_components.create');
        Route::post('/store', [PayrollComponentsController::class, 'store'])->middleware('permission:create_payroll_components')->name('payroll_components.store');
        Route::get('/edit/{id}', [PayrollComponentsController::class, 'edit'])->middleware('permission:edit_payroll_components')->name('payroll_components.edit');
        Route::put('payroll_components/update/{id}', [PayrollComponentsController::class, 'update'])->middleware('permission:edit_payroll_components')->name('payroll_components.update');
        Route::get('/delete/{id}', [PayrollComponentsController::class, 'destroy'])->middleware('permission:delete_payroll_components')->name('payroll_components.destroy');
        Route::get('/show/{id}', [PayrollComponentsController::class, 'show'])->name('payroll_components.show');
        Route::get('/components/by-officer/{officerId}', [PayrollComponentsController::class, 'getByOfficer'])
            ->name('components.byOfficer');
    });

    Route::prefix('akun-user')->middleware('permission:view_user')->group(function () {
        Route::get('/', [AkunUserController::class, 'index'])->name('akun-user.index');
        Route::get('/edit/{id}', [AkunUserController::class, 'edit'])->name('akun-user.edit');
        Route::put('/update/{id}', [AkunUserController::class, 'update'])->name('akun-user.update');
        Route::get('/show/{id}', [AkunUserController::class, 'show'])->name('akun-user.show');

    });
    Route::prefix('payroll-deductions')->middleware('permission:view_payroll_deductions')->group(function () {
        Route::get('/', [PayrollDeductionsController::class, 'index'])->name('payroll_deductions.index');
        Route::get('/create', [PayrollDeductionsController::class, 'create'])->middleware('permission:create_payroll_deductions')->name('payroll_deductions.create');
        Route::post('/store', [PayrollDeductionsController::class, 'store'])->middleware('permission:create_payroll_deductions')->name('payroll_deductions.store');
        Route::get('/edit/{id}', [PayrollDeductionsController::class, 'edit'])->middleware('permission:edit_payroll_deductions')->name('payroll_deductions.edit');
        Route::put('payroll-deductions/update/{id}', [PayrollDeductionsController::class, 'update'])->middleware('permission:edit_payroll_deductions')->name('payroll_deductions.update');
        Route::get('/delete/{id}', [PayrollDeductionsController::class, 'destroy'])->middleware('permission:delete_payroll_deductions')->name('payroll_deductions.destroy');
        Route::get('/show/{id}', [PayrollDeductionsController::class, 'show'])->name('payroll_deductions.show');
    });

    Route::prefix('activity')->middleware('permission:view_activity')->group(function () {
        Route::get('/', [AuthController::class, 'activity'])->name('activity.index');
    });

    Route::prefix('payroll-setting')->middleware('permission:view_payroll_settings')->group(function () {
        Route::get('/', [PayrollSettingController::class, 'index'])->name('payroll_settings.index');
        Route::get('/create', [PayrollSettingController::class, 'create'])->middleware('permission:create_payroll_settings')->name('payroll_settings.create');
        Route::post('/store', [PayrollSettingController::class, 'store'])->middleware('permission:create_payroll_settings')->name('payroll_settings.store');
        Route::get('/edit/{id}', [PayrollSettingController::class, 'edit'])->middleware('permission:edit_payroll_settings')->name('payroll_settings.edit');
        Route::put('payroll-setting/update/{id}', [PayrollSettingController::class, 'update'])->middleware('permission:edit_payroll_settings')->name('payroll_settings.update');
        Route::get('/delete/{id}', [PayrollSettingController::class, 'destroy'])->middleware('permission:delete_payroll_settings')->name('payroll_settings.destroy');
        Route::get('/show/{id}', [PayrollSettingController::class, 'show'])->name('payroll_settings.show');

        // Ambil data payroll berdasarkan guru/staff (AJAX)
        Route::get('/fetch/{officerId}', [PayrollSettingController::class, 'fetch'])
            ->name('payroll_settings.fetch');

        // Ambil daftar guru/staff berdasarkan unit (AJAX)
        Route::get('/officers/by-unit/{unitId}', [PayrollSettingController::class, 'getByUnit'])
            ->name('officers.byUnit');
    });
    Route::get('/payroll-payment', function () {
        return view('pages.penggajian.payroll_payment.payroll_payment');
    });

    // Ambil data payroll berdasarkan guru/staff (AJAX)
    Route::get('/fetch/{officerId}', [PayrollSettingController::class, 'fetch'])
        ->name('payroll_settings.fetch');

    // Ambil daftar guru/staff berdasarkan unit (AJAX)
    Route::get('/officers/by-unit/{unitId}', [PayrollSettingController::class, 'getByUnit'])
        ->name('officers.byUnit');
    Route::post('/tabungan/mass-status', [App\Http\Controllers\TabunganController::class, 'massStatus'])
        ->name('tabungan.massStatus');

    Route::prefix('akun-user')->middleware('permission:view_user')->group(function () {
        Route::get('/', [AkunUserController::class, 'index'])->name('akun-user.index');
        Route::get('/edit/{id}', [AkunUserController::class, 'edit'])->name('akun-user.edit');
        Route::put('/update/{id}', [AkunUserController::class, 'update'])->name('akun-user.update');
        Route::get('/show/{id}', [AkunUserController::class, 'show'])->name('akun-user.show');

    });




});
// Route::get('/payroll-payment', function () {
//     return view('pages.penggajian.payroll_payment.payroll_payment');
// });

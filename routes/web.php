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
use App\Http\Controllers\PotonganController;
use App\Http\Controllers\TipeunitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\PayrollComponentsController;
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
});
Route::get('/login', [AuthController::class, 'portalCode'])->name('login');
Route::post('/portalpost', [AuthController::class, 'checkPortalCode'])->name('portal.check');
Route::get('/login-central', [AuthController::class, 'logincentral'])->name('logincentral.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/portal', [AuthController::class, 'portal'])->name('login.form');

Route::post('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile/', [AuthController::class, 'showupdate'])->name('profile.showupdate');
    Route::put('/profile/update-password', [AuthController::class, 'updatePassword'])->name('profile.updatePassword');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('officer')->group(function () {
        Route::get('/', [OfficerController::class, 'index'])->name('officer.index');
        Route::get('/create', [OfficerController::class, 'create'])->name('officer.create');
        Route::post('/store', [OfficerController::class, 'store'])->name('officer.store');
        Route::get('/edit/{id}', [OfficerController::class, 'edit'])->name('officer.edit');
        Route::put('officer/update/{id}', [OfficerController::class, 'update'])->name('officer.update');
        Route::get('/delete/{id}', [OfficerController::class, 'destroy'])->name('officer.destroy');
        Route::get('/show/{id}', [OfficerController::class, 'show'])->name('officer.show');
        Route::post('/upload', [OfficerController::class, 'upload'])->name('officer.upload');

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
        Route::get('roles/{id}/permissions', [RolesController::class, 'permissions'])->name('roles.permissions');
        Route::post('roles/{id}/permissions', [RolesController::class, 'updatePermissions'])->name('roles.updatePermissions');

    });

    Route::prefix('lembagaunit')->group(function () {
        Route::get('/', [LembagaunitController::class, 'index'])->name('lembagaunit.index');
        Route::get('/create', [LembagaunitController::class, 'create'])->name('lembagaunit.create');
        Route::post('/store', [LembagaunitController::class, 'store'])->name('lembagaunit.store');
        Route::get('/edit/{id}', [LembagaunitController::class, 'edit'])->name('lembagaunit.edit');
        Route::put('lembagaunit/update/{id}', [LembagaUnitController::class, 'update'])->name('lembagaunit.update');
        Route::get('/delete/{id}', [LembagaunitController::class, 'destroy'])->name('lembagaunit.destroy');
        Route::get('/show/{id}', [LembagaunitController::class, 'show'])->name('lembagaunit.show');
        Route::post('/upload', [LembagaUnitController::class, 'upload'])->name('lembagaunit.upload');

    });

    Route::prefix('unit')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('unit.index');
        Route::get('/create', [UnitController::class, 'create'])->name('unit.create');
        Route::post('/store', [UnitController::class, 'store'])->name('unit.store');
        Route::get('/edit/{id}', [UnitController::class, 'edit'])->name('unit.edit');
        Route::put('unit/update/{id}', [UnitController::class, 'update'])->name('unit.update');
        Route::get('/delete/{id}', [UnitController::class, 'destroy'])->name('unit.destroy');
        Route::get('/show/{id}', [UnitController::class, 'show'])->name('unit.show');
        Route::post('/upload', [UnitController::class, 'upload'])->name('unit.upload');
        Route::get('/{unitId}/kelas', [UnitController::class, 'listkelas'])->name('unit.kelas');
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
    Route::prefix('tipe_unit')->group(function () {
        Route::get('/', [TipeunitController::class, 'index'])->name('tipe_unit.index');
        Route::get('/create', [TipeunitController::class, 'create'])->name('tipe_unit.create');
        Route::post('/store', [TipeunitController::class, 'store'])->name('tipe_unit.store');
        Route::get('/edit/{id}', [TipeunitController::class, 'edit'])->name('tipe_unit.edit');
        Route::put('tipe_unit/update/{id}', [TipeunitController::class, 'update'])->name('tipe_unit.update');
        Route::get('/delete/{id}', [TipeunitController::class, 'destroy'])->name('tipe_unit.destroy');
        Route::get('/show/{id}', [TipeunitController::class, 'show'])->name('tipe_unit.show');
    });


    Route::prefix('report')->group(function () {
        Route::get('/tagihan', [ReportController::class, 'tagihan'])->name('report.tagihan');
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
        Route::post('/upload', [SiswaController::class, 'upload'])->name('siswa.upload');
        Route::get('/jurusan/by-unit/{unit}', [SiswaController::class, 'getJurusanByUnit']);
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
        Route::get('/show/{id}', [\App\Http\Controllers\AkunController::class, 'show'])->name('akun.show'); // detail transaksi
        Route::get('/{id}/edit', [\App\Http\Controllers\AkunController::class, 'edit'])->name('akun.edit');
        Route::put('/{id}', [\App\Http\Controllers\AkunController::class, 'update'])->name('akun.update');
        Route::get('/destroy/{id}', [\App\Http\Controllers\AkunController::class, 'destroy'])->name('akun.destroy');
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
        Route::get('/perbulan/{id}', [TagihanController::class, 'perbulan'])->name('tagihan.perbulan');
        Route::get('/daftarTagihan/{id}', [TagihanController::class, 'daftarTagihan'])->name('tagihan.daftarTagihan');
        Route::get('/daftarTagihanBebas/{id}', [TagihanController::class, 'daftarTagihanBebas'])->name('tagihan.daftarTagihanBebas');
        Route::get('/perbulan/{siswaId}/{tagihanId}', [TagihanController::class, 'perbulan'])->name('tagihan.perbulan');
        Route::get('/bebas/{siswaId}', [TagihanController::class, 'tagihanBebas']);

    });

    Route::prefix('potongan')->group(function () {
        Route::get('/', [PotonganController::class, 'index'])->name('potongan.index');
        Route::get('/create', [PotonganController::class, 'create'])->name('potongan.create');
        Route::post('/store', [PotonganController::class, 'store'])->name('potongan.store');
        Route::get('/show/{id}', [PotonganController::class, 'show'])->name('potongan.show');
        Route::get('/edit/{id}', [PotonganController::class, 'edit'])->name('potongan.edit');
        Route::put('potongan/update/{id}', [PotonganController::class, 'update'])->name('potongan.update');
        Route::get('/delete/{id}', [PotonganController::class, 'destroy'])->name('potongan.destroy');
    });

    Route::prefix('pembayaran')->group(function () {
        Route::get('/', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::post('/store', [PembayaranController::class, 'bayar'])->name('pembayaran.store');
        Route::post('/catatan', [PembayaranController::class, 'simpanCatatan']);

    });

    Route::prefix('migrasi')->group(function () {
        Route::get('/import', [\App\Http\Controllers\MigrasiController::class, 'index'])->name('import.index');
        Route::post('/import/siswa', [\App\Http\Controllers\MigrasiController::class, 'importSiswa'])->name('import.siswa');
        Route::post('/import/kelas', [\App\Http\Controllers\MigrasiController::class, 'importKelas'])->name('import.kelas');
        Route::post('/import/officer', [\App\Http\Controllers\MigrasiController::class, 'importOfficer'])->name('import.officer');
        Route::post('/import/jurusan', [\App\Http\Controllers\MigrasiController::class, 'importJurusan'])->name('import.jurusan');
        Route::get('/import/template/{type}', [\App\Http\Controllers\MigrasiController::class, 'downloadTemplate'])->name('import.template');
        Route::get('/officerexport', [\App\Http\Controllers\MigrasiController::class, 'exportOfficer'])->name('export.officerexport');
        Route::get('/exportkelas/', [\App\Http\Controllers\MigrasiController::class, 'exportkelas'])->name('export.exportkelas');
        Route::get('/jurusantkelas/', [\App\Http\Controllers\MigrasiController::class, 'jurusantkelas'])->name('export.jurusantkelas');
        Route::get('/siswatkelas/', [\App\Http\Controllers\MigrasiController::class, 'exportSiswa'])->name('export.exportSiswa');

    });

    Route::prefix('rekening')->group(function () {
        Route::get('/', [\App\Http\Controllers\RekeningController::class, 'index'])->name('rekening.index');
        Route::get('/create', [\App\Http\Controllers\RekeningController::class, 'create'])->name('rekening.create');
        Route::post('/store', [\App\Http\Controllers\RekeningController::class, 'store'])->name('rekening.store');
        Route::get('/edit/{id}', [\App\Http\Controllers\RekeningController::class, 'edit'])->name('rekening.edit');
        Route::put('/update/{id}', [\App\Http\Controllers\RekeningController::class, 'update'])->name('rekening.update');
        Route::get('/delete/{id}', [\App\Http\Controllers\RekeningController::class, 'destroy'])->name('rekening.destroy');
        Route::get('/show/{id}', [\App\Http\Controllers\RekeningController::class, 'show'])->name('rekening.show');
    });
    Route::prefix('positions')->group(function () {
        Route::get('/', [PositionsController::class, 'index'])->name('positions.index');
        Route::get('/create', [PositionsController::class, 'create'])->name('positions.create');
        Route::post('/store', [PositionsController::class, 'store'])->name('positions.store');
        Route::get('/edit/{id}', [PositionsController::class, 'edit'])->name('positions.edit');
        Route::put('positions/update/{id}', [PositionsController::class, 'update'])->name('positions.update');
        Route::get('/delete/{id}', [PositionsController::class, 'destroy'])->name('positions.destroy');
        Route::get('/show/{id}', [PositionsController::class, 'show'])->name('positions.show');
    });
    Route::prefix('payroll_components')->group(function () {
        Route::get('/', [PayrollComponentsController::class, 'index'])->name('payroll_components.index');
        Route::get('/create', [PayrollComponentsController::class, 'create'])->name('payroll_components.create');
        Route::post('/store', [PayrollComponentsController::class, 'store'])->name('payroll_components.store');
        Route::get('/edit/{id}', [PayrollComponentsController::class, 'edit'])->name('payroll_components.edit');
        Route::put('payroll_components/update/{id}', [PayrollComponentsController::class, 'update'])->name('payroll_components.update');
        Route::get('/delete/{id}', [PayrollComponentsController::class, 'destroy'])->name('payroll_components.destroy');
        Route::get('/show/{id}', [PayrollComponentsController::class, 'show'])->name('payroll_components.show');
    });
    Route::prefix('payroll_deductions')->group(function () {
        Route::get('/', [PayrollDeductionsController::class, 'index'])->name('payroll_deductions.index');
        Route::get('/create', [PayrollDeductionsController::class, 'create'])->name('payroll_deductions.create');
        Route::post('/store', [PayrollDeductionsController::class, 'store'])->name('payroll_deductions.store');
        Route::get('/edit/{id}', [PayrollDeductionsController::class, 'edit'])->name('payroll_deductions.edit');
        Route::put('payroll_deductions/update/{id}', [PayrollDeductionsController::class, 'update'])->name('payroll_deductions.update');
        Route::get('/delete/{id}', [PayrollDeductionsController::class, 'destroy'])->name('payroll_deductions.destroy');
        Route::get('/show/{id}', [PayrollDeductionsController::class, 'show'])->name('payroll_deductions.show');
        Route::put('payroll-deductions/update/{id}', [PayrollDeductionsController::class, 'update'])->name('payroll_deductions.update');
    });

    Route::prefix('activity')->group(function () {
        Route::get('/', [AuthController::class, 'activity'])->name('activity.index');
    });

    Route::prefix('payroll-setting')->group(function() {
        Route::get('/', [PayrollSettingController::class, 'index'])->name('payroll_settings.index');
        Route::get('/create', [PayrollSettingController::class, 'create'])->name('payroll_settings.create');
        Route::post('/store', [PayrollSettingController::class, 'store'])->name('payroll_settings.store');
        Route::get('/edit/{id}', [PayrollSettingController::class, 'edit'])->name('payroll_settings.edit');
        Route::put('payroll-setting/update/{id}', [PayrollSettingController::class, 'update'])->name('payroll_settings.update');
        Route::get('/delete/{id}', [PayrollSettingController::class, 'destroy'])->name('payroll_settings.destroy');
        Route::get('/show/{id}', [PayrollSettingController::class, 'show'])->name('payroll_settings.show');

        // Ambil data payroll berdasarkan guru/staff (AJAX)
        Route::get('/fetch/{officerId}', [PayrollSettingController::class, 'fetch'])
            ->name('payroll_settings.fetch');

        // Ambil daftar guru/staff berdasarkan unit (AJAX)
        Route::get('/officers/by-unit/{unitId}', [PayrollSettingController::class, 'getByUnit'])
            ->name('officers.byUnit');
    });
    Route::get('/payroll-payment', function() {
        return view('pages.penggajian.payroll_payment.payroll_payment');
    });

});

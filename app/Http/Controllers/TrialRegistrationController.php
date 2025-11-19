<?php

namespace App\Http\Controllers;

use App\Mail\TrialRegistrationConfirmation;
use App\Mail\TrialRegistrationConfirmationnext;
use App\Models\Akun;
use App\Models\Kategoritagihan;
use App\Models\Roles;
use App\Models\Roles_petugas;
use App\Models\setting_akun;
use App\Models\TrialRegistration;
use App\Models\Tipeunit;
use App\Models\Unit;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class TrialRegistrationController extends Controller
{
    public function showForm()
    {
        // Mengambil data tipe unit dan yayasan untuk dropdown
        $tipeunit = Tipeunit::all();
        $yayasans = Yayasan::all();
        return view('registerpublic', compact('tipeunit', 'yayasans'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'school_name'    => 'required|string|max:150',
                'npsn'           => 'required|string|max:30',
                'address'        => 'required|string|max:255',
                'full_name'      => 'required|string|max:150',
                'email'          => 'required|email|max:150|unique:trial_registrations,email',
                'no_hp'          => 'required|string|max:30',
                'agree'          => 'accepted',
                'tipe_unit_id'   => 'required|integer',
                'yayasan_id'     => 'nullable',
            ]);

            $yayasan_id = $data['yayasan_id'] ?? null;
            $trialRegistration =  TrialRegistration::create([
                'school_name'    => $data['school_name'],
                'npsn'           => $data['npsn'],
                'address'        => $data['address'],
                'full_name'      => $data['full_name'],
                'email'          => $data['email'],
                'no_hp'          => $data['no_hp'],
                'tipe_unit_id'   => $data['tipe_unit_id'],
                'yayasan_id'     => $yayasan_id,
                'status'         => '0',  // Status default
            ]);

            Mail::to($trialRegistration->email)->send(new TrialRegistrationConfirmation($trialRegistration));

            return redirect()->route('landing.successregister');

        }catch (Exception $e) {
            Log::error('Error in TrialRegistrationController@store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }
    public function registrationPortal($id)
    {
        $trialRegistration = TrialRegistration::findOrFail($id);
        return view('registration_portal', compact('trialRegistration'));
    }


    public function storePortal(Request $request, $id)
    {
        try {
            DB::beginTransaction();


            $trialUser = TrialRegistration::where('id', $id)->firstOrFail();

            $yayasan_id = null;

            if (!empty($trialUser->yayasan_id)) {
                $codeyayasan = 'Y' . strtoupper(Str::random(7));

                $yayasan = Yayasan::create([
                    'central_code' => $codeyayasan,
                    'nama_yayasan' => $trialUser->yayasan_id,
                    'nama_pimpinan' => $trialUser->full_name ?? '',
                    'no_hp' => $trialUser->no_hp ?? '',
                    'email' => $trialUser->email ?? '',
                    'alamat' => $trialUser->address ?? '',
                    'website' => '',
                    'status' => '0',
                ]);

                $yayasan_id = $yayasan->id; // simpan ID-nya di variabel terpisah
            }

            $centralCode = 'U' . strtoupper(Str::random(7));

            $unit = Unit::create([
                'nama_unit' => $trialUser->school_name,
                'code' => $centralCode,
                'image' => null,
                'no_hp' => $request->no_hp,
                'email' => $request->email,
                'alamat' => $request->address,
                'website' => '',
                'tipe_unit_id' => $trialUser->tipe_unit_id,
                'status' => 1,
                'yayasan_id' => $yayasan_id, // ✅ pakai variabel yang aman
            ]);

            $updateunit_idyayasan = Yayasan::where('id', $yayasan_id)->update([
                'unit_id' => $unit->id,
            ]);
            $this->seedAkunsForUnit($unit->id);
            $this->seedSettingAkunsForUnit($unit->id);
            $this->seedKategoritagihanForUnit($unit->id);


            $usercek = User::where('email', $request->email)->first();

            if ($usercek) {
                return redirect()->back()->with('error', 'Email sudah terdaftar!');
            }

            // Buat user admin
            $user = User::create([
                'name' => $request->username,
                'unit_id' => $unit->id,
                'password' => Hash::make('123456'),
                'email' => $request->email,
                'yayasan_id' => $yayasan_id,

            ]);

            // Role Spatie
            $roleSpatie = \Spatie\Permission\Models\Role::firstOrCreate(
                ['name' => 'admin'],
                ['guard_name' => 'web']
            );
            $user->assignRole($roleSpatie->name);

            // Update trial registration
            $trialUser->update([
                'status' => '1',
                'updated_at' => now(),
            ]);

            // Kirim email
            Mail::to($user->email)->send(new TrialRegistrationConfirmationnext($user, $unit));

            DB::commit();

            return redirect()->route('landing.success')
                ->with('success', 'Akun portal berhasil dibuat!, Harap cek email untuk login.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error storePortal: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyiapkan portal: ' . $e->getMessage());
        }
    }
    private function seedAkunsForUnit($unitId)
    {
        $akunTemplate = $this->getAkunTemplate();
        $parentMap = []; // Map to store parent_kode => id

        foreach ($akunTemplate as $row) {
            // Check if akun already exists
            $existingAkun = Akun::where('kode_akun', $row['kode_akun'])
                ->where('unit_id', $unitId)
                ->first();

            if (!$existingAkun) {
                // Find parent_id based on parent_kode if exists
                $parentId = null;
                if (!empty($row['parent_kode'])) {
                    // First check if we have the parent in our map (already created)
                    if (isset($parentMap[$row['parent_kode']])) {
                        $parentId = $parentMap[$row['parent_kode']];
                    } else {
                        // Otherwise query the database for existing parent
                        $parent = Akun::where('kode_akun', $row['parent_kode'])
                            ->where('unit_id', $unitId)
                            ->first();
                        $parentId = $parent?->id;
                    }
                }

                $newAkun = Akun::create([
                    'kode_akun' => $row['kode_akun'],
                    'nama_akun' => $row['nama_akun'],
                    'tipe' => $row['tipe'] ?? 'ASET',
                    'parent_id' => $parentId,
                    'unit_id' => $unitId,
                    'status' => $row['status'] ?? 1,
                    'kategori_akun' => $row['kategori_akun'] ?? null,
                    'keterangan' => $row['keterangan'] ?? null,
                ]);

                // Store in map for future parent lookups
                $parentMap[$row['kode_akun']] = $newAkun->id;
            }
        }
    }

    /**
     * Get the account template structure
     */
    private function getAkunTemplate(): array
    {
        return [
            // ASET (Assets)
            ['kode_akun' => '11', 'nama_akun' => 'ASET LANCAR', 'tipe' => 'ASET', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],

            // ASET LANCAR - Kas & Bank
            ['kode_akun' => '11.1', 'nama_akun' => 'KAS & BANK', 'tipe' => 'ASET', 'parent_kode' => '11', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '11.1.01', 'nama_akun' => 'KAS SEKOLAH', 'tipe' => 'ASET', 'parent_kode' => '11.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '11.1.02', 'nama_akun' => 'KAS BANK', 'tipe' => 'ASET', 'parent_kode' => '11.1', 'kategori_akun' => 'transaksi', 'status' => 1],

            // ASET LANCAR - Piutang
            ['kode_akun' => '11.2', 'nama_akun' => 'PIUTANG', 'tipe' => 'ASET', 'parent_kode' => '11', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '11.2.01', 'nama_akun' => 'PINJAMAN GURU & KARYAWAN', 'tipe' => 'ASET', 'parent_kode' => '11.2', 'kategori_akun' => 'transaksi', 'status' => 1],

            // ASET LANCAR - Persediaan
            ['kode_akun' => '11.3', 'nama_akun' => 'PERSEDIAAN', 'tipe' => 'ASET', 'parent_kode' => '11', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '11.3.01', 'nama_akun' => 'PERSEDIAAN ATK & CETAKAN', 'tipe' => 'ASET', 'parent_kode' => '11.3', 'kategori_akun' => 'transaksi', 'status' => 1],

            // ASET TIDAK LANCAR
            ['kode_akun' => '12', 'nama_akun' => 'ASET TIDAK LANCAR', 'tipe' => 'ASET', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '12.2', 'nama_akun' => 'ASET TETAP', 'tipe' => 'ASET', 'parent_kode' => '12', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '12.2.02', 'nama_akun' => 'PERALATAN & INVENTARIS', 'tipe' => 'ASET', 'parent_kode' => '12.2', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '12.2.09', 'nama_akun' => 'AKUMULASI PENYUSUTAN ASET TETAP', 'tipe' => 'ASET', 'parent_kode' => '12.2', 'kategori_akun' => 'transaksi', 'status' => 1],

            // LIABILITAS (Kewajiban)
            ['kode_akun' => '21', 'nama_akun' => 'KEWAJIBAN JANGKA PENDEK', 'tipe' => 'LIABILITAS', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '21.1', 'nama_akun' => 'TABUNGAN SEKOLAH', 'tipe' => 'LIABILITAS', 'parent_kode' => '21', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '21.1.01', 'nama_akun' => 'TABUNGAN SISWA', 'tipe' => 'LIABILITAS', 'parent_kode' => '21.1', 'kategori_akun' => 'tabungan', 'status' => 1],
            ['kode_akun' => '21.1.02', 'nama_akun' => 'TABUNGAN GURU', 'tipe' => 'LIABILITAS', 'parent_kode' => '21.1', 'kategori_akun' => 'tabungan', 'status' => 1],
            ['kode_akun' => '21.5', 'nama_akun' => 'DANA TITIPAN', 'tipe' => 'LIABILITAS', 'parent_kode' => '21', 'kategori_akun' => null, 'status' => 1],

            // EKUITAS (Aset Bersih)
            ['kode_akun' => '31', 'nama_akun' => 'ASET BERSIH / EKUITAS', 'tipe' => 'EKUITAS', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.1', 'nama_akun' => 'DANA TIDAK TERIKAT', 'tipe' => 'EKUITAS', 'parent_kode' => '31', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.1.01', 'nama_akun' => 'DONASI / SUMBANGAN UMUM', 'tipe' => 'EKUITAS', 'parent_kode' => '31.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '31.2', 'nama_akun' => 'DANA TERIKAT TEMPORER', 'tipe' => 'EKUITAS', 'parent_kode' => '31', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.2.01', 'nama_akun' => 'DANA BOS / BOSDA', 'tipe' => 'EKUITAS', 'parent_kode' => '31.2', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '31.3', 'nama_akun' => 'DANA TERIKAT PERMANEN', 'tipe' => 'EKUITAS', 'parent_kode' => '31', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '31.3.01', 'nama_akun' => 'DANA WAKAF', 'tipe' => 'EKUITAS', 'parent_kode' => '31.3', 'kategori_akun' => 'transaksi', 'status' => 1],

            // PENDAPATAN (Revenue)
            ['kode_akun' => '41', 'nama_akun' => 'PENDAPATAN OPERASIONAL', 'tipe' => 'PENDAPATAN', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '41.1', 'nama_akun' => 'PENDAPATAN SEKOLAH', 'tipe' => 'PENDAPATAN', 'parent_kode' => '41', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '41.1.01', 'nama_akun' => 'PENDAPATAN TAGIHAN', 'tipe' => 'PENDAPATAN', 'parent_kode' => '41.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '41.1.02', 'nama_akun' => 'PENDAPATAN SPMB', 'tipe' => 'PENDAPATAN', 'parent_kode' => '41.1', 'kategori_akun' => 'transaksi', 'status' => 1],

            // BEBAN (Expenses)
            ['kode_akun' => '51', 'nama_akun' => 'BEBAN OPERASIONAL', 'tipe' => 'BEBAN', 'parent_kode' => null, 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.1', 'nama_akun' => 'BEBAN GAJI', 'tipe' => 'BEBAN', 'parent_kode' => '51', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.1.01', 'nama_akun' => 'BEBAN GAJI GURU & KARYAWAN', 'tipe' => 'BEBAN', 'parent_kode' => '51.1', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '51.2', 'nama_akun' => 'BEBAN UMUM & ADMINISTRASI', 'tipe' => 'BEBAN', 'parent_kode' => '51', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.2.01', 'nama_akun' => 'BIAYA OPERASIONAL SEKOLAH', 'tipe' => 'BEBAN', 'parent_kode' => '51.2', 'kategori_akun' => 'transaksi', 'status' => 1],
            ['kode_akun' => '51.5', 'nama_akun' => 'BEBAN LAIN-LAIN', 'tipe' => 'BEBAN', 'parent_kode' => '51', 'kategori_akun' => null, 'status' => 1],
            ['kode_akun' => '51.5.01', 'nama_akun' => 'BEBAN PENYUSUTAN ASET', 'tipe' => 'BEBAN', 'parent_kode' => '51.5', 'kategori_akun' => 'transaksi', 'status' => 1],
        ];
    }
    private function seedSettingAkunsForUnit($unitId)
    {
        $settingTemplate = $this->getSettingAkunTemplate();

        foreach ($settingTemplate as $row) {
            // Check if setting akun already exists
            $existingSetting = setting_akun::where('nama_setting', $row['nama_setting'])
                ->where('unit_id', $unitId)
                ->first();

            if (!$existingSetting) {
                // Find akun_id based on kode_akun
                $akun = null;
                if (!empty($row['kode_akun'])) {
                    $akun = Akun::where('kode_akun', $row['kode_akun'])
                        ->where('unit_id', $unitId)
                        ->first();
                }

                if ($akun) {
                    setting_akun::create([
                        'nama_setting' => $row['nama_setting'],
                        'kategori' => $row['kategori'] ?? null,
                        'akun_id' => $akun->id,
                        'debit' => $row['debit'] ?? 0,
                        'kredit' => $row['kredit'] ?? 0,
                        'status' => $row['status'] ?? 1,
                        'unit_id' => $unitId,
                        'keterangan' => $row['keterangan'] ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * Get the setting akun template structure
     * Maps account codes to their transaction settings
     */
    private function getSettingAkunTemplate(): array
    {
        return [
            // TAGIHAN-MASUK (Incoming Invoices/Receipts)
            [
                'nama_setting' => 'PENERIMAAN TAGIHAN SISWA',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '41.1.01',  // PENDAPATAN TAGIHAN
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENERIMAAN SPMB',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '41.1.02',  // PENDAPATAN SPMB
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'SETOR KE BANK',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '11.1.02',  // KAS BANK
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENERIMAAN DANA BOS',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '31.2.01',  // DANA BOS / BOSDA
                'debit' => 0,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENGEMBALIAN PINJAMAN',
                'kategori' => 'tagihan-masuk',
                'kode_akun' => '11.2.01',  // PINJAMAN GURU & KARYAWAN
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],

            // TAGIHAN-KELUAR (Outgoing Invoices/Disbursements)
            [
                'nama_setting' => 'PENGELUARAN GAJI',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '51.1.01',  // BEBAN GAJI GURU & KARYAWAN
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'BIAYA OPERASIONAL SEKOLAH',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '51.2.01',  // BIAYA OPERASIONAL SEKOLAH
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENARIKAN DARI BANK',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '11.1.02',  // KAS BANK
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENGELUARAN DANA BOS',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '31.2.01',  // DANA BOS / BOSDA
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'PENGELUARAN UNTUK PINJAMAN',
                'kategori' => 'tagihan-keluar',
                'kode_akun' => '11.2.01',  // PINJAMAN GURU & KARYAWAN
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],

            // TABUNGAN (Savings - Deposits)
            [
                'nama_setting' => 'SETOR TABUNGAN SISWA',
                'kategori' => 'tabungan',
                'kode_akun' => '21.1.01',  // TABUNGAN SISWA
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'SETOR TABUNGAN GURU',
                'kategori' => 'tabungan',
                'kode_akun' => '21.1.02',  // TABUNGAN GURU
                'debit' => 0,
                'kredit' => 1,
                'status' => 1,
                'keterangan' => null,
            ],

            // TABUNGAN-TARIK (Savings - Withdrawals)
            [
                'nama_setting' => 'TARIK TABUNGAN SISWA',
                'kategori' => 'tabungan-tarik',
                'kode_akun' => '21.1.01',  // TABUNGAN SISWA
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
            [
                'nama_setting' => 'TARIK TABUNGAN GURU',
                'kategori' => 'tabungan-tarik',
                'kode_akun' => '21.1.02',  // TABUNGAN GURU
                'debit' => 1,
                'kredit' => 0,
                'status' => 1,
                'keterangan' => null,
            ],
        ];
    }
    private function seedKategoritagihanForUnit($unitId)
    {
        $kategoriTemplate = $this->getKategoritagihanTemplate();

        foreach ($kategoriTemplate as $row) {
            // Check if kategori tagihan already exists
            $existingKategori = Kategoritagihan::where('kode_kategori', $row['kode_kategori'])
                ->where('unit_id', $unitId)
                ->first();

            if (!$existingKategori) {
                Kategoritagihan::create([
                    'nama_kategori' => $row['nama_kategori'],
                    'kode_kategori' => $row['kode_kategori'],
                    'keterangan' => $row['keterangan'] ?? null,
                    'unit_id' => $unitId,
                    'biaya_tagihan' => $row['biaya_tagihan'] ?? 0,
                    'tahun_ajaran_id' => null,
                    'status' => $row['status'] ?? '1',
                ]);
            }
        }
    }

    /**
     * Get the kategori tagihan template structure
     */
    private function getKategoritagihanTemplate(): array
    {
        return [
            [
                'nama_kategori' => 'TAGIHAN SPP',
                'kode_kategori' => '01',
                'biaya_tagihan' => 500000,
                'status' => '1',
                'keterangan' => 'Tagihan Sumbangan Pendidikan Peserta Didik bulanan',
            ],
            [
                'nama_kategori' => 'TAGIHAN BUKU',
                'kode_kategori' => '02',
                'biaya_tagihan' => 200000,
                'status' => '1',
                'keterangan' => 'Biaya pembelian buku sekolah dan referensi',
            ],
            [
                'nama_kategori' => 'TAGIHAN SERAGAM',
                'kode_kategori' => '03',
                'biaya_tagihan' => 400000,
                'status' => '1',
                'keterangan' => 'Biaya pengadaan seragam sekolah',
            ],
            [
                'nama_kategori' => 'STUDY TOUR',
                'kode_kategori' => '04',
                'biaya_tagihan' => 2000000,
                'status' => '1',
                'keterangan' => 'Biaya kunjungan belajar / study tour',
            ],
            [
                'nama_kategori' => 'BIAYA UJIAN PRAKTEK',
                'kode_kategori' => '05',
                'biaya_tagihan' => 0,
                'status' => '1',
                'keterangan' => 'Biaya ujian praktek',
            ],
        ];
    }
}

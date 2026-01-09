<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Kategoritagihan;
use App\Models\Kelas;
use App\Models\setting_akun;
use App\Models\Tipeunit;
use App\Models\Unit;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query
        $query = Unit::with('tipe_unit');
        // Filter berdasarkan prioritas: yayasan_id > unit_id
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit yang terkait dengan yayasan tersebut
            $query->where('yayasan_id', Auth::user()->yayasan_id);
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $query->where('id', Auth::user()->unit_id);
        }
        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_unit', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%")
                    ->orWhere('nama_pimpinan_unit', 'like', "%{$search}%")
                    ->orWhereHas('tipe_unit', function ($q) use ($search) {
                        $q->where('nama_tipe_unit', 'like', "%{$search}%");
                    });
            });
        }

        // Paginate results
        $unit = $query->get();

        $headers = [
            'No',
            'Yayasan',
            'Tipe Unit',
            'Nama Unit',
            'Kode Unit',
            'No Telp',
            'Email',
            'Alamat',
            'Website',
            'Status',
            'Aksi',
        ];

        return view('pages.data_master.unit.unit', compact('unit', 'headers', 'units'));
    }

    public function create()
    {
        $unit = Unit::where('id', Auth::user()->unit_id)->first();

        // Filter yayasan berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, hanya tampilkan yayasan tersebut
            $yayasan = Yayasan::where('status', '1')->where('id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan yayasan yang terkait dengan unit tersebut
            $yayasan = Yayasan::where('status', '1')->when($unit->yayasan_id, function ($query, $yayasanId) {
                $query->where('id', $yayasanId);
            })->get();
        } else {
            // Admin bisa melihat semua yayasan
            $yayasan = Yayasan::where('status', '1')->get();
        }

        $tipeunit = Tipeunit::where('status', '1')->get();

        return view('pages.data_master.unit.unit_create', compact('yayasan', 'tipeunit'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:units,nama_unit',
            'image' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'website' => 'nullable|string',
            'status' => 'required|in:0,1',
            'tipe_unit_id' => 'nullable',
            'nama_pimpinan_unit' => 'nullable|string',
            'code' => 'nullable|string|max:10|unique:units,code',
        ]);

        $centralCode = $request->code;
        if (empty($centralCode)) {
            $centralCode = strtoupper(Str::random(7));
        }

        $unit = Unit::create([
            'nama_unit' => $request->nama_unit,
            'code' => $centralCode,
            'image' => $request->image,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'website' => $request->website,
            'yayasan_id' => $request->yayasan_id,
            'status' => $request->status,
            'tipe_unit_id' => $request->tipe_unit_id,
            'nama_pimpinan_unit' => $request->nama_pimpinan_unit,
        ]);

        // Auto-seed akuns for the new unit
        $this->seedAkunsForUnit($unit->id);

        // Auto-seed setting akuns for the new unit
        $this->seedSettingAkunsForUnit($unit->id);

        // Auto-seed kategori tagihans for the new unit
        $this->seedKategoritagihanForUnit($unit->id);

        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil ditambahkan dengan Central Code: '.$centralCode.'. Akun template, setting akun, dan kategori tagihan telah otomatis ditambahkan.');
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);

        // Filter yayasan berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, hanya tampilkan yayasan tersebut
            $yayasan = Yayasan::where('status', '1')->where('id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan yayasan yang terkait dengan unit tersebut
            $yayasan = Yayasan::where('status', '1')->when($unit->yayasan_id, function ($query, $yayasanId) {
                $query->where('id', $yayasanId);
            })->get();
        } else {
            // Admin bisa melihat semua yayasan
            $yayasan = Yayasan::where('status', '1')->get();
        }

        $tipeunit = Tipeunit::where('status', '1')->get();

        return view('pages.data_master.unit.unit_create', compact('unit', 'yayasan', 'tipeunit'));
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:units,nama_unit,'.$id,
            'image' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'website' => 'nullable|string',
            'status' => 'required|in:0,1',
            'tipe_unit_id' => 'nullable',
            'nama_pimpinan_unit' => 'nullable|string',
            'code' => 'nullable|string|max:10|unique:units,code,'.$id,
        ]);

        $unit->update($request->only([
            'nama_unit',
            'image',
            'no_hp',
            'email',
            'alamat',
            'website',
            'status',
            'tipe_unit_id',
            'nama_pimpinan_unit',
            'code',
        ]));

        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil dihapus');
    }

    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        $show = true;

        // Filter yayasan berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, hanya tampilkan yayasan tersebut
            $yayasan = Yayasan::where('status', '1')->where('id', Auth::user()->yayasan_id)->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan yayasan yang terkait dengan unit tersebut
            $yayasan = Yayasan::with('units')
                ->when(Auth::user()->unit_id, function ($query, $unitId) {
                    $query->whereHas('units', function ($q) use ($unitId) {
                        $q->where('id', $unitId);
                    });
                })->where('status', '1')->get();
        } else {
            // Admin bisa melihat semua yayasan
            $yayasan = Yayasan::where('status', '1')->get();
        }

        $tipeunit = Tipeunit::where('status', '1')->get();

        return view('pages.data_master.unit.unit_create', compact('unit', 'show', 'yayasan', 'tipeunit'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $file = $request->file('file');
        $filename = Str::random(15).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/unit', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/'.$path,
        ]);
    }

    public function listkelas($id)
    {
        $kelas = Kelas::where('unit_id', $id)->where('status', '1')->get();

        return response()->json($kelas);
    }

    public function getKelasByUnit($unitId)
    {
        $kelas = \App\Models\Kelas::where('unit_id', $unitId)
            ->where('status', '1')
            ->select('id', 'nama_kelas')
            ->get();

        return response()->json($kelas);
    }

    /**
     * Seed akuns for a new unit
     * Auto-populates the unit with template accounts
     */
    private function seedAkunsForUnit($unitId)
    {
        $akunTemplate = $this->getAkunTemplate();
        $parentMap = []; // Map to store parent_kode => id

        foreach ($akunTemplate as $row) {
            // Check if akun already exists
            $existingAkun = Akun::where('kode_akun', $row['kode_akun'])
                ->where('unit_id', $unitId)
                ->first();

            if (! $existingAkun) {
                // Find parent_id based on parent_kode if exists
                $parentId = null;
                if (! empty($row['parent_kode'])) {
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

    /**
     * Seed setting akuns for a new unit
     * Auto-populates the unit with template account settings
     */
    private function seedSettingAkunsForUnit($unitId)
    {
        $settingTemplate = $this->getSettingAkunTemplate();

        foreach ($settingTemplate as $row) {
            // Check if setting akun already exists
            $existingSetting = setting_akun::where('nama_setting', $row['nama_setting'])
                ->where('unit_id', $unitId)
                ->first();

            if (! $existingSetting) {
                // Find akun_id based on kode_akun
                $akun = null;
                if (! empty($row['kode_akun'])) {
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

    /**
     * Seed kategori tagihans for a new unit
     * Auto-populates the unit with template billing categories
     */
    private function seedKategoritagihanForUnit($unitId)
    {
        $kategoriTemplate = $this->getKategoritagihanTemplate();

        foreach ($kategoriTemplate as $row) {
            // Check if kategori tagihan already exists
            $existingKategori = Kategoritagihan::where('kode_kategori', $row['kode_kategori'])
                ->where('unit_id', $unitId)
                ->first();

            if (! $existingKategori) {
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

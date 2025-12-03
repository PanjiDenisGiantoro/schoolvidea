<?php

namespace App\Http\Controllers;

use App\Models\DataRekening;
use App\Models\Jurnals;
use App\Models\Kelas;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Pembayarantagihan;
use App\Models\PembayaranTagihanDetail;
use App\Models\setting_akun;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Tagihansiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index()
    {
        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan dari semua unit dalam yayasan
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $siswaList = Siswa::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $tagihanList = Tagihan::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $akunList = setting_akun::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $kelas = Kelas::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();

            // Get pembayaran data for summary
            $pembayaranQuery = PembayaranTagihan::whereHas('tagihanSiswa.tagihan.unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id (tapi tidak punya yayasan_id), tampilkan dari unit tersebut
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
            $siswaList = Siswa::where('unit_id', Auth::user()->unit_id)->get();
            $tagihanList = Tagihan::where('unit_id', Auth::user()->unit_id)->get();
            $akunList = setting_akun::where('unit_id', Auth::user()->unit_id)->get();
            $kelas = Kelas::where('unit_id', Auth::user()->unit_id)->get();

            // Get pembayaran data for summary
            $pembayaranQuery = PembayaranTagihan::whereHas('tagihanSiswa.tagihan', function($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        } else {
            // Super admin - tampilkan semua
            $units = \App\Models\Unit::where('status', '1')->get();
            $siswaList = Siswa::all();
            $tagihanList = Tagihan::all();
            $akunList = setting_akun::all();
            $kelas = Kelas::get();

            // Get all pembayaran data for summary
            $pembayaranQuery = PembayaranTagihan::query();
        }

        // Calculate summary data - hanya pembayaran yang sudah approved dan verified
        $allPembayaran = $pembayaranQuery
            ->where('status_approval', 'approved')
            ->get();

        // Total tunggakan (belum dibayar) - dari tagihan_siswa dengan status != 1
        $totalTunggakan = 0;
        foreach ($siswaList as $siswa) {
            $belumBayar = Tagihansiswa::where('siswa_id', $siswa->id)
                ->where('status', '!=', '1')
                ->sum('sisa_nominal');
            $totalTunggakan += $belumBayar;
        }

        // Total pembayaran all (hanya yang approved)
        $totalPembayaran = $allPembayaran->sum('jumlah_bayar');

        // Total pembayaran tunai (case insensitive)
        $totalTunai = $allPembayaran->filter(function($p) {
            return strtolower($p->metode_bayar) === 'tunai' || strtolower($p->metode_bayar) === 'cash';
        })->sum('jumlah_bayar');

        // Total pembayaran non-tunai
        $totalNonTunai = $allPembayaran->filter(function($p) {
            return strtolower($p->metode_bayar) !== 'tunai' && strtolower($p->metode_bayar) !== 'cash';
        })->sum('jumlah_bayar');

        $summary = [
            'total_tunggakan' => $totalTunggakan,
            'total_pembayaran' => $totalPembayaran,
            'total_tunai' => $totalTunai,
            'total_nontunai' => $totalNonTunai,
        ];

        return view('pages.pembayaran.pembayaran', compact('siswaList', 'tagihanList', 'akunList','kelas','units', 'summary'));

    }
    public function bayar(Request $request)
    {
        DB::beginTransaction();
        try {
            $tagihanSiswa = Tagihansiswa::findOrFail($request->tagihan_siswa_id);

            if ($tagihanSiswa->status === 1) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tagihan ini sudah lunas'
                ], 400);
            }

            $siswa = $tagihanSiswa->siswa;

            $jumlahBayar = (int) $request->jumlah_bayar;
            $nominal     = (int) $request->nominal;
            $sisaNominal = (int) $tagihanSiswa->sisa_nominal;

            if ($jumlahBayar > $sisaNominal) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Jumlah bayar tidak boleh lebih besar dari sisa tagihan'
                ], 400);
            }

            // hitung sisa tagihan
            $sisaSetelahBayar = $sisaNominal - $jumlahBayar;

            // tentukan status baru
            if ($sisaSetelahBayar == '0') {
                $statusTagihan = '1'; // Lunas
                $sisaSetelahBayar = 0; // jaga-jaga jangan negatif
                $keterangan = "Lunas tagihan bulan {$request->bulan} {$request->tahun} sebesar Rp " . number_format($nominal, 0, ',', '.');
                $tanggalBayar = now();
            } else {
                $statusTagihan = '2'; // Cicilan
                $keterangan = "Cicilan tagihan bulan {$request->bulan} {$request->tahun} bayar Rp " . number_format($jumlahBayar, 0, ',', '.') . " dari Rp " . number_format($nominal, 0, ',', '.');
                $tanggalBayar = null;
            }

            // Generate head_tagihan unik untuk tracking pembayaran
            $headTagihan = 'HT' . date('YmdHis') . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

            // simpan pembayaran
            $pembayaran = PembayaranTagihan::create([
                'code_pembayaran' => 'PS' . date('YmdHis').rand(1000, 9999),
                'head_tagihan' => $headTagihan,
                'is_master' => true,
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jumlah_bayar'     => $jumlahBayar,
                'tanggal_bayar'    => now(),
                'metode_bayar'     => $request->metode ?? 'Tunai',
                'keterangan'       => $keterangan,
                'create_by'        => Auth::id(),
                'status_approval' => 'approved',
                'jumlah_tagihan_detail' => 1,
            ]);

            // Create detail record untuk tracking (single payment)
            PembayaranTagihanDetail::create([
                'head_tagihan' => $headTagihan,
                'pembayaran_id' => $pembayaran->id,
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jumlah_bayar_detail' => $jumlahBayar,
                'urutan' => 1,
                'periode' => $request->bulan ?? null,
                'tahun' => $request->tahun ?? null,
            ]);

            // update status dan sisa_nominal
            $tagihanSiswa->update([
                'status'        => $statusTagihan,
                'sisa_nominal'  => $sisaSetelahBayar,
                'tanggal_bayar' => $tanggalBayar,
            ]);

            // Cek apakah masih ada tagihan siswa yang belum lunas untuk tagihan ini
            $hasUnpaid = Tagihansiswa::where('tagihan_id', $tagihanSiswa->tagihan_id)
                ->where('status', '0') // 0 = Belum Lunas
                ->exists();

            // Jika tidak ada lagi yang belum lunas, update status tagihan utama menjadi lunas
            if (!$hasUnpaid) {
                Tagihan::where('id', $tagihanSiswa->tagihan_id)
                    ->update(['status_tagihan' => 1]); // 1 = Lunas
            }

            // catat transaksi keuangan
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran'      => $pembayaran->code_pembayaran,
                'penerima_id'          => $siswa->id,
                'penerima_tipe'        => Siswa::class,
                'jenis_transaksi'      => 'tagihan',
                'jumlah'               => $jumlahBayar,
                'metode'               => $request->metode ?? 'CASH',
                'referensi_tagihan_id' => $pembayaran->id,
                'tanggal_transaksi'    => now(),
                'keterangan'           => $keterangan,
                'created_by'           => Auth::id(),
                'status_approval' => 'approved',
                'approved_by'         => Auth::id(),
                'approved_at'          => now(),
                'status_verifikasi'  => 'approved',
                'verified_at'          => now(),
                'verified_by'          => Auth::id(),
            ]);


            $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                ->first();



            if (!$datarekening) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Rekening tabungan tidak ditemukan.'
                ], 400);
            }


            if($datarekening->allotment == 'Semua Pembayaran'){
                $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                    ->where('allotment','Semua Pembayaran')
                    ->first();
            }else{
                $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                    ->where('allotment','Pembayaran Tagihan')
                    ->first();
            }


            $settings = setting_akun::where('kategori', 'tagihan-masuk');

            if (Auth::user()->yayasan_id) {
                $settings->where('unit_id', Auth::user()->unit_id);
            } elseif (Auth::user()->unit_id) {
                $settings->where('unit_id', Auth::user()->unit_id);
            } elseif ($request->filled('unit_id')) {
                $settings->where('unit_id', $request->unit_id);
            }

            $settings = $settings->where('status', '1')->first();

            if ($settings == null) {
                return back()->with('danger', "Setting akun untuk kategori tagihan-masuk belum lengkap.");
            }

            $akun_id = $settings->akun_id;
            $position = $settings->debit;

            if (!$akun_id) {
                return back()->with('danger', "Akun untuk kategori tabungan-tarik belum dikonfigurasi. Silakan hubungi administrator.");
            }


            if($position == 1){
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'        => 0,
                    'kredit'       => $jumlahBayar,
                    'keterangan'   => $keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'kredit'        => 0,
                    'debit'       =>$jumlahBayar,
                    'keterangan'   => $keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);
            }else{
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'       => $jumlahBayar,
                    'kredit'        => 0,
                    'keterangan'   => $keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'kredit'       => $jumlahBayar,
                    'debit'        => 0,
                    'keterangan'   => $keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);
            }
//
//
//
//            // jurnal debit
//            Jurnals::create([
//                'transaksi_id' => $transaksi->id,
//                'akun_id'      => setting_akun::where('kategori', 'tagihan-keluar')->where('debit', 1)->where('unit_id',Auth::user()->id)->first()?->akun_id,
//                'debit'        => $jumlahBayar,
//                'kredit'       => 0,
//                'keterangan'   => $keterangan,
//            ]);
//
//            // jurnal kredit
//            Jurnals::create([
//                'transaksi_id' => $transaksi->id,
//                'akun_id'      => setting_akun::where('kategori', 'tagihan-keluar')->where('kredit', 1)->where('unit_id',Auth::user()->id)->first()?->akun_id,
//                'debit'        => 0,
//                'kredit'       => $jumlahBayar,
//                'keterangan'   => $keterangan,
//            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => $statusTagihan == 1 ? 'Pembayaran lunas' : 'Pembayaran cicilan',
                'data'    => [
                    'pembayaran'   => $pembayaran,
                    'tagihanSiswa' => $tagihanSiswa->fresh()
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Print struk pembayaran tagihan
     */
    public function printStruk($pembayaranId)
    {
        $pembayaran = Pembayarantagihan::with([
            'tagihanSiswa.tagihan.items.kategori',
            'tagihanSiswa.siswa.user',
            'user'
        ])->findOrFail($pembayaranId);

        $tagihanSiswa = $pembayaran->tagihanSiswa;
        $siswa = $tagihanSiswa->siswa;
        $tagihan = $tagihanSiswa->tagihan;

        // Generate kode tagihan
        $kodeTagihan = 'TAG-' . str_pad($tagihan->id, 5, '0', STR_PAD_LEFT);

        // Generate HTML untuk cetak
        $html = view('pages.pembayaran.struk', compact(
            'pembayaran',
            'tagihanSiswa',
            'siswa',
            'tagihan',
            'kodeTagihan'
        ))->render();

        // Konfigurasi mPDF untuk thermal printer (80mm x 200mm)
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 200], // 80mm width, 200mm height (thermal printer size)
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $mpdf->SetTitle('Struk Pembayaran - ' . $siswa->user->name);
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Struk-Pembayaran-' . date('YmdHis') . '.pdf', 'I');
    }

    /**
     * Approve pembayaran tagihan
     */
    public function approve(Request $request, $pembayaranId)
    {
        $request->validate([
            'catatan_verifikasi' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            // Load pembayaran dengan relasi yang diperlukan
            $pembayaran = Pembayarantagihan::with([
                'tagihanSiswa.tagihan.items.kategori',
                'tagihanSiswa.siswa.user'
            ])->findOrFail($pembayaranId);

            // Cek apakah pembayaran sudah diapprove sebelumnya
            if ($pembayaran->status_approval === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah diapprove sebelumnya'
                ], 400);
            }

            // Update status pembayaran menjadi approved
            $pembayaran->update([
                'status_approval' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            // Get data untuk update transaksi keuangan
            $tagihanSiswa = $pembayaran->tagihanSiswa;
            $siswa = $tagihanSiswa->siswa;
            $tagihan = $tagihanSiswa->tagihan;
            $jumlahBayar = (int) $pembayaran->jumlah_bayar;

            // Update status tagihan siswa jika belum diupdate
            $sisaNominalBaru = $tagihanSiswa->sisa_nominal - $jumlahBayar;
            $statusBaru = '0'; // Default: Belum Bayar

            if ($sisaNominalBaru <= 0) {
                $statusBaru = '1'; // Lunas
                $sisaNominalBaru = 0;
            } elseif ($sisaNominalBaru > 0) {
                $statusBaru = '2'; // Cicilan
            }

            $tagihanSiswa->update([
                'status' => $statusBaru,
                'sisa_nominal' => $sisaNominalBaru,
                'tanggal_bayar' => now(),
            ]);

            // Update atau buat keuangan_transaksi
            $transaksi = Keuangan_transaksi::where('referensi_tagihan_id', $pembayaran->id)->first();

            if (!$transaksi) {
                // Buat transaksi baru jika belum ada
                $transaksi = Keuangan_transaksi::create([
                    'code_pembayaran' => $pembayaran->code_pembayaran,
                    'penerima_id' => $siswa->id,
                    'penerima_tipe' => Siswa::class,
                    'jenis_transaksi' => 'pembayaran',
                    'jumlah' => $jumlahBayar,
                    'metode' => $pembayaran->metode_bayar ?? 'CASH',
                    'referensi_tagihan_id' => $pembayaran->id,
                    'tanggal_transaksi' => now(),
                    'keterangan' => "Pembayaran {$tagihan->nama_tagihan} sebesar Rp " . number_format($jumlahBayar, 0, ',', '.'),
                    'created_by' => Auth::id(),
                    'status_approval' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                    'status_verifikasi' => 'approved',
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'catatan_verifikasi' => $request->catatan_verifikasi
                ]);
            } else {
                // Update transaksi yang sudah ada
                $transaksi->update([
                    'status_verifikasi' => 'approved',
                    'status_approval' => 'approved',
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                    'catatan_verifikasi' => $request->catatan_verifikasi
                ]);
            }

            // Create journal entries untuk pembayaran tagihan
            $keterangan = "Pembayaran {$tagihan->nama_tagihan} sebesar Rp " . number_format($jumlahBayar, 0, ',', '.');

            // Debit: Kas (uang masuk dari siswa)
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => 1, // Kas
                'debit' => $jumlahBayar,
                'kredit' => 0,
                'keterangan' => $keterangan . ' - ' . ($siswa->user->name ?? 'Siswa'),
                'tanggal' => now(),
            ]);

            // Kredit: Tagihan (mengurangi hutang siswa)
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => 3, // Tagihan Masuk (receivable)
                'kredit' => $jumlahBayar,
                'debit' => 0,
                'keterangan' => $keterangan . ' - ' . ($siswa->user->name ?? 'Siswa'),
                'tanggal' => now(),
            ]);

            // Log activity
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'approve',
                'data_lama' => json_encode(['status_approval' => 'pending']),
                'data_baru' => json_encode(['status_approval' => 'approved']),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diapprove',
                'data' => [
                    'pembayaran' => $pembayaran->fresh(),
                    'tagihanSiswa' => $tagihanSiswa->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses Multiple Pembayaran Tagihan
     * Menggabungkan beberapa pembayaran tagihan menjadi 1 pembayaran utama
     */
    public function prosesMultiplePembayaran(Request $request)
    {
        $request->validate([
            'tagihan_siswa_ids' => 'required|array|min:2',
            'tagihan_siswa_ids.*' => 'integer|exists:tagihan_siswa,id',
            'jumlah_bayar' => 'required|integer|min:1',
            'metode' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $tagihanSiswaIds = $request->tagihan_siswa_ids;
            $jumlahBayar = (int) $request->jumlah_bayar;
            $metode = $request->metode ?? 'tunai';

            // Load semua tagihan siswa yang akan dibayar
            $tagihanSiswaList = Tagihansiswa::with('siswa.user', 'tagihan')
                ->whereIn('id', $tagihanSiswaIds)
                ->get();

            // Validasi: semua tagihan harus milik siswa yang sama
            $firstSiswaId = $tagihanSiswaList->first()?->siswa_id;
            $allSameSiswa = $tagihanSiswaList->every(fn($ts) => $ts->siswa_id === $firstSiswaId);

            if (!$allSameSiswa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Semua tagihan harus milik siswa yang sama'
                ], 400);
            }

            // Hitung total sisa nominal dari semua tagihan
            $totalSisaNominal = $tagihanSiswaList->sum('sisa_nominal');

            // Validasi: jumlah bayar tidak boleh lebih besar dari total
            if ($jumlahBayar > $totalSisaNominal) {
                return response()->json([
                    'status' => false,
                    'message' => "Jumlah bayar tidak boleh lebih besar dari total sisa tagihan (Rp " . number_format($totalSisaNominal, 0, ',', '.') . ")"
                ], 400);
            }

            $siswa = $tagihanSiswaList->first()->siswa;
            $totalSisaSetelahBayar = $totalSisaNominal - $jumlahBayar;

            // Generate kode pembayaran master (untuk pembayaran utama)
            $kodePembayaranMaster = 'PS' . date('YmdHis') . rand(1000, 9999);

            // Simpan pembayaran master
            $pembayaranMaster = Pembayarantagihan::create([
                'code_pembayaran' => $kodePembayaranMaster,
                'tagihan_siswa_id' => $tagihanSiswaList->first()->id, // referensi ke tagihan pertama
                'jumlah_bayar' => $jumlahBayar,
                'tanggal_bayar' => now(),
                'metode_bayar' => $metode,
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan sebesar Rp " . number_format($jumlahBayar, 0, ',', '.'),
                'create_by' => Auth::id(),
                'status_approval' => 'approved',
            ]);

            // Distribusi pembayaran ke masing-masing tagihan secara proporsional
            $pembayaranDetails = [];
            $sisaBayar = $jumlahBayar;
            $tagihanIndex = 0;

            foreach ($tagihanSiswaList as $tagihanSiswa) {
                // Tentukan berapa banyak yang dibayarkan untuk tagihan ini
                $sisaNominalTagihan = $tagihanSiswa->sisa_nominal;

                if ($tagihanIndex === count($tagihanSiswaList) - 1) {
                    // Tagihan terakhir: bayar sisa yang tersisa
                    $bayarUntukTagihanIni = $sisaBayar;
                } else {
                    // Tagihan lainnya: bayar proporsional
                    $bayarUntukTagihanIni = min($sisaBayar, $sisaNominalTagihan);
                }

                // Hitung sisa nominal setelah pembayaran
                $sisaNominalBaru = $sisaNominalTagihan - $bayarUntukTagihanIni;

                // Tentukan status tagihan
                $statusTagihan = ($sisaNominalBaru == 0) ? '1' : '2';
                $tanggalBayar = ($statusTagihan == '1') ? now() : null;

                // Update tagihan siswa
                $tagihanSiswa->update([
                    'status' => $statusTagihan,
                    'sisa_nominal' => $sisaNominalBaru,
                    'tanggal_bayar' => $tanggalBayar,
                ]);

                // Simpan ke pembayaran details untuk pencatatan
                $pembayaranDetails[] = [
                    'tagihan_siswa_id' => $tagihanSiswa->id,
                    'tagihan_nama' => $tagihanSiswa->tagihan->jenis_tagihan ?? 'Tagihan',
                    'sisa_nominal_sebelum' => $sisaNominalTagihan,
                    'jumlah_bayar' => $bayarUntukTagihanIni,
                    'sisa_nominal_sesudah' => $sisaNominalBaru,
                    'status' => $statusTagihan,
                ];

                // Cek apakah masih ada tagihan yang belum lunas untuk tagihan utama ini
                $hasUnpaid = Tagihansiswa::where('tagihan_id', $tagihanSiswa->tagihan_id)
                    ->where('status', '0')
                    ->exists();

                if (!$hasUnpaid) {
                    Tagihan::where('id', $tagihanSiswa->tagihan_id)
                        ->update(['status_tagihan' => 1]);
                }

                $sisaBayar -= $bayarUntukTagihanIni;
                $tagihanIndex++;
            }

            // Catat transaksi keuangan master
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => $kodePembayaranMaster,
                'penerima_id' => $siswa->id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'tagihan-multiple',
                'jumlah' => $jumlahBayar,
                'metode' => strtoupper($metode),
                'referensi_tagihan_id' => $pembayaranMaster->id,
                'tanggal_transaksi' => now(),
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan dari " . ($siswa->user->name ?? 'Siswa'),
                'created_by' => Auth::id(),
                'status_approval' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'status_verifikasi' => 'approved',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
            ]);

            // Buat jurnal debit (kas masuk)
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => setting_akun::where('kategori', 'tagihan-keluar')->where('debit', 1)->first()?->akun_id,
                'debit' => $jumlahBayar,
                'kredit' => 0,
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan - " . ($siswa->user->name ?? 'Siswa'),
            ]);

            // Buat jurnal kredit (hutang berkurang)
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => setting_akun::where('kategori', 'tagihan-keluar')->where('kredit', 1)->first()?->akun_id,
                'debit' => 0,
                'kredit' => $jumlahBayar,
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan - " . ($siswa->user->name ?? 'Siswa'),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran gabungan berhasil diproses',
                'data' => [
                    'pembayaran_master' => $pembayaranMaster,
                    'pembayaran_details' => $pembayaranDetails,
                    'siswa' => [
                        'id' => $siswa->id,
                        'nama' => $siswa->user->name ?? 'Siswa',
                    ],
                    'summary' => [
                        'jumlah_tagihan' => count($tagihanSiswaIds),
                        'total_sisa_nominal_sebelum' => $totalSisaNominal,
                        'jumlah_bayar' => $jumlahBayar,
                        'total_sisa_nominal_sesudah' => $totalSisaSetelahBayar,
                    ]
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Reject pembayaran tagihan
     */
    public function reject(Request $request, $pembayaranId)
    {
        $request->validate([
            'catatan_verifikasi' => 'required|string'
        ]);

        DB::beginTransaction();

        try {
            // Load pembayaran dengan relasi yang diperlukan
            $pembayaran = Pembayarantagihan::with([
                'tagihanSiswa.tagihan.items.kategori',
                'tagihanSiswa.siswa.user'
            ])->findOrFail($pembayaranId);

            // Cek apakah pembayaran sudah direject sebelumnya
            if ($pembayaran->status_approval === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran sudah direject sebelumnya'
                ], 400);
            }

            // Update status pembayaran menjadi rejected
            $pembayaran->update([
                'status_approval' => 'rejected'
            ]);

            // Rollback status tagihan siswa jika sudah diupdate
            $tagihanSiswa = $pembayaran->tagihanSiswa;
            $siswa = $tagihanSiswa->siswa;
            $tagihan = $tagihanSiswa->tagihan;
            $jumlahBayar = (int) $pembayaran->jumlah_bayar;

            // Kembalikan nominal yang sudah berkurang
            $sisaNominalBaru = $tagihanSiswa->sisa_nominal + $jumlahBayar;
            $statusBaru = '0'; // Kembali ke Belum Bayar

            $tagihanSiswa->update([
                'status' => $statusBaru,
                'sisa_nominal' => $sisaNominalBaru,
            ]);

            // Update keuangan_transaksi status
            $transaksi = Keuangan_transaksi::where('referensi_tagihan_id', $pembayaran->id)->first();

            if ($transaksi) {
                $transaksi->update([
                    'status_verifikasi' => 'rejected',
                    'status_approval' => 'rejected',
                    'catatan_verifikasi' => $request->catatan_verifikasi
                ]);

                // Log activity
                Keuangan_transaksi_logs::create([
                    'transaksi_id' => $transaksi->id,
                    'aksi' => 'reject',
                    'data_lama' => json_encode(['status_approval' => 'approved']),
                    'data_baru' => json_encode(['status_approval' => 'rejected']),
                    'dilakukan_oleh' => Auth::id(),
                    'dilakukan_pada' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil direject',
                'data' => [
                    'pembayaran' => $pembayaran->fresh(),
                    'tagihanSiswa' => $tagihanSiswa->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ============================================================
     * FUNGSI PEMBAYARAN MULTIPLE DENGAN HEAD_TAGIHAN & DETAIL
     * ============================================================
     */

    /**
     * Generate nomor head_tagihan yang unik
     * Format: HT + YYYYMMDDHHMMSS + 4 digit random
     * Contoh: HT202412021530001234
     */
    private function generateHeadTagihan()
    {
        return 'HT' . date('YmdHis') . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create record di pembayaran_tagihan_detail untuk tracking
     * Menyimpan detail setiap tagihan yang dibayarkan dalam satu group
     */
    private function createPembayaranDetail($headTagihan, $pembayaranId, $tagihanSiswaId, $jumlahBayar, $urutan, $periode = null, $tahun = null)
    {
        return PembayaranTagihanDetail::create([
            'head_tagihan' => $headTagihan,
            'pembayaran_id' => $pembayaranId,
            'tagihan_siswa_id' => $tagihanSiswaId,
            'jumlah_bayar_detail' => $jumlahBayar,
            'urutan' => $urutan,
            'periode' => $periode,
            'tahun' => $tahun,
        ]);
    }

    /**
     * Proses Pembayaran Multiple dengan Detail Tracking
     * Improved version dari prosesMultiplePembayaran dengan head_tagihan & detail recording
     *
     * Flow:
     * 1. Validasi input
     * 2. Generate head_tagihan unik
     * 3. Create pembayaran master (is_master = true)
     * 4. Create pembayaran_detail records untuk setiap tagihan
     * 5. Distribusi pembayaran proporsional
     * 6. Update status tagihan_siswa
     * 7. Create transaksi keuangan & jurnal
     *
     * @param Request $request
     * @return JSON response
     */
    public function prosesPembayaranMultipleWithDetail(Request $request)
    {
        $request->validate([
            'tagihan_siswa_ids' => 'required|array|min:1',
            'tagihan_siswa_ids.*' => 'integer|exists:tagihan_siswa,id',
            'jumlah_bayar' => 'required|integer|min:1',
            'metode' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $tagihanSiswaIds = $request->tagihan_siswa_ids;
            $jumlahBayar = (int) $request->jumlah_bayar;
            $metode = $request->metode ?? 'TUNAI';

            // 1. Load semua tagihan siswa yang akan dibayar
            $tagihanSiswaList = Tagihansiswa::with('siswa.user', 'tagihan')
                ->whereIn('id', $tagihanSiswaIds)
                ->get();

            // 2. Validasi: semua tagihan harus milik siswa yang sama
            $firstSiswaId = $tagihanSiswaList->first()?->siswa_id;
            $allSameSiswa = $tagihanSiswaList->every(fn($ts) => $ts->siswa_id === $firstSiswaId);

            if (!$allSameSiswa) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Semua tagihan harus milik siswa yang sama'
                ], 400);
            }

            // 3. Hitung total sisa nominal dari semua tagihan
            $totalSisaNominal = $tagihanSiswaList->sum('sisa_nominal');

            // 4. Validasi: jumlah bayar tidak boleh lebih besar dari total
            if ($jumlahBayar > $totalSisaNominal) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => "Jumlah bayar tidak boleh lebih besar dari total sisa tagihan (Rp " . number_format($totalSisaNominal, 0, ',', '.') . ")"
                ], 400);
            }

            // 5. Validasi: max 10 tagihan per pembayaran
            if (count($tagihanSiswaIds) > 10) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Maksimal 10 tagihan per pembayaran'
                ], 400);
            }

            $siswa = $tagihanSiswaList->first()->siswa;
            $totalSisaSetelahBayar = $totalSisaNominal - $jumlahBayar;

            // 6. Generate head_tagihan unik
            $headTagihan = $this->generateHeadTagihan();

            // 7. Create pembayaran master dengan is_master = true
            $kodePembayaranMaster = 'PS' . date('YmdHis') . rand(1000, 9999);
            $pembayaranMaster = Pembayarantagihan::create([
                'code_pembayaran' => $kodePembayaranMaster,
                'head_tagihan' => $headTagihan,
                'is_master' => true,
                'tagihan_siswa_id' => $tagihanSiswaList->first()->id,
                'jumlah_bayar' => $jumlahBayar,
                'tanggal_bayar' => now(),
                'metode_bayar' => $metode,
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan sebesar Rp " . number_format($jumlahBayar, 0, ',', '.'),
                'create_by' => Auth::id(),
                'status_approval' => 'approved',
                'jumlah_tagihan_detail' => count($tagihanSiswaIds),
            ]);

            // 8. Distribusi pembayaran dan create detail records
            $pembayaranDetails = [];
            $sisaBayar = $jumlahBayar;
            $tagihanIndex = 0;

            foreach ($tagihanSiswaList as $tagihanSiswa) {
                $sisaNominalTagihan = $tagihanSiswa->sisa_nominal;

                // Tentukan jumlah pembayaran untuk tagihan ini
                if ($tagihanIndex === count($tagihanSiswaList) - 1) {
                    // Tagihan terakhir: bayar sisa yang tersisa
                    $bayarUntukTagihanIni = $sisaBayar;
                } else {
                    // Tagihan lainnya: bayar proporsional
                    $bayarUntukTagihanIni = min($sisaBayar, $sisaNominalTagihan);
                }

                // Hitung sisa nominal setelah pembayaran
                $sisaNominalBaru = $sisaNominalTagihan - $bayarUntukTagihanIni;

                // Tentukan status tagihan
                $statusTagihan = ($sisaNominalBaru == 0) ? '1' : '2';
                $tanggalBayarTagihan = ($statusTagihan == '1') ? now() : null;

                // 9. Update tagihan siswa
                $tagihanSiswa->update([
                    'status' => $statusTagihan,
                    'sisa_nominal' => $sisaNominalBaru,
                    'tanggal_bayar' => $tanggalBayarTagihan,
                ]);

                // 10. Create pembayaran_detail record untuk tracking
                $this->createPembayaranDetail(
                    $headTagihan,
                    $pembayaranMaster->id,
                    $tagihanSiswa->id,
                    $bayarUntukTagihanIni,
                    $tagihanIndex + 1,
                    $tagihanSiswa->bulan ?? null,
                    $tagihanSiswa->tahun ?? null
                );

                // 11. Simpan ke pembayaran details untuk response
                $pembayaranDetails[] = [
                    'tagihan_siswa_id' => $tagihanSiswa->id,
                    'tagihan_nama' => $tagihanSiswa->tagihan->jenis_tagihan ?? 'Tagihan',
                    'sisa_nominal_sebelum' => $sisaNominalTagihan,
                    'jumlah_bayar' => $bayarUntukTagihanIni,
                    'sisa_nominal_sesudah' => $sisaNominalBaru,
                    'status' => $statusTagihan,
                ];

                // 12. Cek apakah masih ada tagihan yang belum lunas untuk tagihan utama ini
                $hasUnpaid = Tagihansiswa::where('tagihan_id', $tagihanSiswa->tagihan_id)
                    ->where('status', '0')
                    ->exists();

                if (!$hasUnpaid) {
                    Tagihan::where('id', $tagihanSiswa->tagihan_id)
                        ->update(['status_tagihan' => 1]);
                }

                $sisaBayar -= $bayarUntukTagihanIni;
                $tagihanIndex++;
            }

            // 13. Catat transaksi keuangan master
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => $kodePembayaranMaster,
                'penerima_id' => $siswa->id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'pembayaran-multiple',
                'jumlah' => $jumlahBayar,
                'metode' => strtoupper($metode),
                'referensi_tagihan_id' => $pembayaranMaster->id,
                'tanggal_transaksi' => now(),
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan dari " . ($siswa->user->name ?? 'Siswa') . " | Head: " . $headTagihan,
                'created_by' => Auth::id(),
                'status_approval' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'status_verifikasi' => 'approved',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
            ]);

            // 14. Buat jurnal debit (kas masuk)
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => setting_akun::where('kategori', 'tagihan-masuk')->where('debit', 1)->first()?->akun_id ?? 1,
                'debit' => $jumlahBayar,
                'kredit' => 0,
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan - " . ($siswa->user->name ?? 'Siswa'),
                'unit_id' => Auth::user()->unit_id,
            ]);

            // 15. Buat jurnal kredit (hutang berkurang)
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => setting_akun::where('kategori', 'tagihan-keluar')->where('kredit', 1)->first()?->akun_id ?? 2,
                'debit' => 0,
                'kredit' => $jumlahBayar,
                'keterangan' => "Pembayaran gabungan " . count($tagihanSiswaIds) . " tagihan - " . ($siswa->user->name ?? 'Siswa'),
                'unit_id' => Auth::user()->unit_id,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran gabungan berhasil diproses',
                'data' => [
                    'head_tagihan' => $headTagihan,
                    'pembayaran_master' => $pembayaranMaster,
                    'pembayaran_details' => $pembayaranDetails,
                    'siswa' => [
                        'id' => $siswa->id,
                        'nama' => $siswa->user->name ?? 'Siswa',
                    ],
                    'summary' => [
                        'jumlah_tagihan' => count($tagihanSiswaIds),
                        'total_sisa_nominal_sebelum' => $totalSisaNominal,
                        'jumlah_bayar' => $jumlahBayar,
                        'total_sisa_nominal_sesudah' => $totalSisaSetelahBayar,
                    ]
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get Pembayaran Detail by Head Tagihan
     * Retrieve semua detail pembayaran dari satu head_tagihan
     */
    public function getPembayaranDetailByHeadTagihan($headTagihan)
    {
        try {
            $details = PembayaranTagihanDetail::byHeadTagihan($headTagihan)
                ->with('tagihanSiswa.siswa.user', 'tagihanSiswa.tagihan', 'pembayaran')
                ->orderBy('urutan')
                ->get();

            if ($details->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pembayaran tidak ditemukan'
                ], 404);
            }

            $pembayaranMaster = $details->first()->pembayaran;

            return response()->json([
                'status' => true,
                'data' => [
                    'head_tagihan' => $headTagihan,
                    'pembayaran_master' => $pembayaranMaster,
                    'details' => $details->map(function($detail) {
                        return [
                            'urutan' => $detail->urutan,
                            'siswa_nama' => $detail->tagihanSiswa->siswa->user->name ?? '-',
                            'tagihan_nama' => $detail->tagihanSiswa->tagihan->jenis_tagihan ?? '-',
                            'periode' => $detail->periode,
                            'tahun' => $detail->tahun,
                            'jumlah_bayar' => $detail->jumlah_bayar_detail,
                            'jumlah_bayar_formatted' => $detail->formatted_jumlah_bayar,
                        ];
                    }),
                    'total_bayar' => $details->sum('jumlah_bayar_detail'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

}

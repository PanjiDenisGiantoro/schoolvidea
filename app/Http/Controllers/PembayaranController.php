<?php

namespace App\Http\Controllers;

use App\Models\Jurnals;
use App\Models\Kelas;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Pembayarantagihan;
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

        // Calculate summary data
        $allPembayaran = $pembayaranQuery->get();

        // Total tunggakan (belum dibayar) - dari tagihan_siswa dengan status != 1
        $totalTunggakan = 0;
        foreach ($siswaList as $siswa) {
            $belumBayar = Tagihansiswa::where('siswa_id', $siswa->id)
                ->where('status', '!=', '1')
                ->sum('sisa_nominal');
            $totalTunggakan += $belumBayar;
        }

        // Total pembayaran all (semua pembayaran)
        $totalPembayaran = $allPembayaran->sum('jumlah_bayar');

        // Total pembayaran tunai
        $totalTunai = $allPembayaran->where('metode_bayar', 'tunai')->sum('jumlah_bayar');

        // Total pembayaran non-tunai
        $totalNonTunai = $allPembayaran->where('metode_bayar', '!=', 'tunai')->sum('jumlah_bayar');

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

            // simpan pembayaran
            $pembayaran = PembayaranTagihan::create([
                'code_pembayaran' => 'PS' . date('YmdHis').rand(1000, 9999),
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jumlah_bayar'     => $jumlahBayar,
                'tanggal_bayar'    => now(),
                'metode_bayar'     => $request->metode ?? 'Tunai',
                'keterangan'       => $keterangan,
                'create_by'        => Auth::id(),
                'status_approval' => 'approved',
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

            // jurnal debit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => setting_akun::where('kategori', 'tagihan-keluar')->where('debit', 1)->first()?->akun_id,
                'debit'        => $jumlahBayar,
                'kredit'       => 0,
                'keterangan'   => $keterangan,
            ]);

            // jurnal kredit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => setting_akun::where('kategori', 'tagihan-keluar')->where('kredit', 1)->first()?->akun_id,
                'debit'        => 0,
                'kredit'       => $jumlahBayar,
                'keterangan'   => $keterangan,
            ]);

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

}

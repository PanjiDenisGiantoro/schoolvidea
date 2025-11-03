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
        $siswaList = Siswa::all();
        $tagihanList = Tagihan::all();
        $akunList = setting_akun::all();
        $kelas = Kelas::get();

        return view('pages.pembayaran.pembayaran', compact('siswaList', 'tagihanList', 'akunList','kelas'));

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
                'code_pembayaran' => 'PS' . date('YmdHis').$siswa->nisn.rand(1000,9999),
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jumlah_bayar'     => $jumlahBayar,
                'tanggal_bayar'    => now(),
                'metode_bayar'     => $request->metode ?? 'manual',
                'keterangan'       => $keterangan,
                'create_by'        => Auth::id(),
            ]);

            // update status dan sisa_nominal
            $tagihanSiswa->update([
                'status'        => $statusTagihan,
                'sisa_nominal'  => $sisaSetelahBayar,
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


}

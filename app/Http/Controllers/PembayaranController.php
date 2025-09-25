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
use App\Models\TagihanSiswa;
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
            // Ambil ID dari request body
            $tagihanSiswa = TagihanSiswa::findOrFail($request->tagihan_siswa_id);

            if ($tagihanSiswa->status === 1) { // 1 = lunas
                return response()->json([
                    'status'  => false,
                    'message' => 'Tagihan ini sudah lunas'
                ], 400);
            }

            $siswa = $tagihanSiswa->siswa;

            $keterangan = "Bayar tagihan bulan '.$request->bulan.' tahun '.$request->tahun.' kategori id '.$request->kategori_id. ' nominal '.$request->nominal";
            // Insert ke tabel pembayaran
            $pembayaran = PembayaranTagihan::create([
                'tagihan_siswa_id' => $tagihanSiswa->id,
                'jumlah_bayar'     => $request->nominal,
                'tanggal_bayar'    => now(),
                'metode_bayar'     => $request->metode ?? 'manual',
                'keterangan'       => $keterangan,
            ]);

            // Update status tagihan_siswa
            $tagihanSiswa->update([
                'status'        => '1',
                'tanggal_bayar' => now(),
            ]);

            // Ambil akun untuk jurnal (tagihan-keluar)
            $settings = setting_akun::where('kategori', 'tagihan-keluar')->get();
            $akun_debit  = $settings->where('debit', 1)->first()?->akun_id;   // piutang siswa
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id; // pendapatan sekolah

            $total_tagihan = $request->nominal;

            // Transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'penerima_id'     => $siswa->id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'tagihan',
                'jumlah'          => $total_tagihan,
                'keterangan'      => "Pembayaran Tagihan ID: {$tagihanSiswa->tagihan_id}",
                'created_by'      => Auth::id(),
            ]);

            // Jurnal debit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => $akun_debit,
                'debit'        => $total_tagihan,
                'kredit'       => 0,
                'keterangan'   => "Pembayaran Tagihan Siswa ID: {$siswa->id}",
            ]);

            // Jurnal kredit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => $akun_kredit,
                'debit'        => 0,
                'kredit'       => $total_tagihan,
                'keterangan'   => "Pembayaran Tagihan Siswa ID: {$siswa->id}",
            ]);

            // Log transaksi
            Keuangan_transaksi_logs::create([
                'transaksi_id'   => $transaksi->id,
                'aksi'           => 'bayar_tagihan',
                'data_lama'      => null,
                'data_baru'      => json_encode([
                    'tagihan_siswa_id' => $tagihanSiswa->id,
                    'jumlah'           => $total_tagihan,
                ]),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Pembayaran berhasil dicatat',
                'data'    => [
                    'pembayaran'   => $pembayaran,
                    'tagihanSiswa' => $tagihanSiswa
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Terjadi kesalahan: '.$th->getMessage()
            ], 500);
        }
    }


}

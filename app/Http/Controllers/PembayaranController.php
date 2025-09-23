<?php

namespace App\Http\Controllers;

use App\Models\Jurnals;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\setting_akun;
use App\Models\Siswa;
use App\Models\Tagihan;
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

        return view('pages.pembayaran.pembayaran', compact('siswaList', 'tagihanList', 'akunList'));

    }
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tagihan_id' => 'required|exists:tagihans,id',
            'jumlah_bayar' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            $siswa = Siswa::findOrFail($request->siswa_id);
            $tagihan = Tagihan::with('items')->findOrFail($request->tagihan_id);

            // Hitung total tagihan (periode * nominal item)
            $total_tagihan = $tagihan->items->sum('nominal') * ($tagihan->periode ?? 1);

            // Ambil setting akun
            $settings = setting_akun::where('kategori', 'tagihan-masuk')->get();
            $akun_debit = $settings->where('debit', 1)->first()?->akun_id; // piutang siswa
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id; // pendapatan sekolah

            if (!$akun_debit || !$akun_kredit) {
                throw new \Exception("Setting akun untuk kategori tagihan-masuk belum lengkap.");
            }

            // Simpan transaksi pembayaran
            $transaksi = Keuangan_transaksi::create([
                'penerima_id' => $siswa->id,
                'penerima_tipe' => Siswa::class,
                'jenis_transaksi' => 'pembayaran_tagihan',
                'jumlah' => $request->jumlah_bayar,
                'keterangan' => "Pembayaran Tagihan ID: {$tagihan->id}",
                'created_by' => Auth::id(),
            ]);

            // Jurnal debit/kredit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => $akun_debit,
                'debit' => 0,
                'kredit' => $request->jumlah_bayar,
                'keterangan' => "Pembayaran tagihan siswa ID: {$siswa->id}",
            ]);

            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id' => $akun_kredit,
                'debit' => $request->jumlah_bayar,
                'kredit' => 0,
                'keterangan' => "Pembayaran tagihan siswa ID: {$siswa->id}",
            ]);

            // Catat log transaksi
            Keuangan_transaksi_logs::create([
                'transaksi_id' => $transaksi->id,
                'aksi' => 'bayar',
                'data_lama' => null,
                'data_baru' => json_encode([
                    'tagihan_id' => $tagihan->id,
                    'jumlah_bayar' => $request->jumlah_bayar,
                ]),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return redirect()->route('tagihan.index')->with('success', 'Pembayaran berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}

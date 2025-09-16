<?php

namespace App\Http\Controllers;

use App\Models\Jurnals;
use App\Models\Kelas;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Saldo_keuangan;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TabunganController extends Controller
{
    /**
     * List transaksi tabungan
     */
    public function index()
    {
        $transaksis = Siswa::with('tahun_ajaran','saldo','user','kelas','unit')->get();

        return view('pages.tabungan.index', compact('transaksis'));
    }

    /**
     * Form tambah transaksi tabungan
     */
    public function create()
    {
        $kelas = Kelas::get();
        return view('pages.tabungan.create', compact('kelas'));
    }

    /**
     * Simpan transaksi baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'       => 'required',
            'penerima_id'    => 'required',
            'jumlah'         => 'required|numeric|min:1',
            'keterangan'     => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Ambil data siswa
            $siswa = Siswa::findOrFail($request->penerima_id);

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'setoran_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'created_by'      => Auth::id(),
            ]);

            // Akun statis (misal)
            $akun_debit  = 2; // Kas
            $akun_kredit = 4; // Tabungan Siswa

            // Jurnal Debit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => $akun_debit,
                'debit'        => $request->jumlah,
                'kredit'       => 0,
                'keterangan'   => $request->keterangan,
            ]);

            // Jurnal Kredit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => $akun_kredit,
                'debit'        => 0,
                'kredit'       => $request->jumlah,
                'keterangan'   => $request->keterangan,
            ]);

            $saldoSiswa = Saldo_keuangan::firstOrCreate(
                [
                    'akun_id' => $akun_kredit,
                    'user_id' => $request->penerima_id
                ],
                ['saldo_akhir' => 0]
            );

// Tambah saldo (setoran)
            $saldoSiswa->increment('saldo_akhir', $request->jumlah);
            DB::commit();

            return redirect()->route('tabungan.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

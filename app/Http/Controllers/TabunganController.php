<?php

namespace App\Http\Controllers;

use App\Models\Jurnals;
use App\Models\Kelas;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\Saldo_keuangan;
use App\Models\setting_akun;
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

        $total_setoran = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->sum('jumlah');

        $total_penarikan = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->sum('jumlah');

        return view('pages.tabungan.index', compact('transaksis','total_setoran','total_penarikan'));
    }

    /**
     * Form tambah transaksi tabungan
     */
    public function create()
    {
        $kelas = Kelas::get();
        return view('pages.tabungan.create', compact('kelas'));
    }
    public function tarik()
    {
        $kelas = Kelas::get();
        return view('pages.tabungan.tarik', compact('kelas'));
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

            if(!$siswa){
                return back()->with('error', 'Siswa tidak ditemukan.');
            }

            $rekening = Saldo_keuangan::where('user_id', $request->penerima_id)->where('status', 1)->first();
            if(!$rekening){
                return back()->with('error', 'Rekening tabungan tidak ditemukan.');
            }
            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'setoran_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'created_by'      => Auth::id(),
            ]);

            $settings = setting_akun::where('kategori', 'tabungan')->get();

            $akun_debit  = $settings->where('debit', 1)->first()?->akun_id;
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id;

            if (!$akun_debit || !$akun_kredit) {
                throw new \Exception("Setting akun untuk kategori tabungan belum lengkap.");
            }

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
                    'status' => 1,
                    'akun_id' => $akun_kredit,
                    'user_id' => $request->penerima_id
                ],
                ['saldo_akhir' => 0]
            );

            Keuangan_transaksi_logs::create([
                'transaksi_id'  => $transaksi->id,
                'aksi'          => 'create',
                'data_lama'     => null,
                'data_baru'     => json_encode($transaksi),
                'dilakukan_oleh'=> Auth::id(),
                'dilakukan_pada'=> now(),
            ]);
            $saldoSiswa->increment('saldo_akhir', $request->jumlah);
            DB::commit();

            return redirect()->route('tabungan.index')->with('success', 'Transaksi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function tarikStore(Request $request)
    {
        $request->validate([
            'kelas_id'    => 'required',
            'penerima_id' => 'required',
            'jumlah'      => 'required|numeric|min:1',
            'keterangan'  => 'nullable|string',
        ]);

//        DB::beginTransaction();

        try {
            $siswa = Siswa::findOrFail($request->penerima_id);
            if (!$siswa) {
                return back()->with('error', 'Siswa tidak ditemukan.');
            }

            $rekening = Saldo_keuangan::where('user_id', $request->penerima_id)->where('status', 1)->first();
            if(!$rekening){
                return back()->with('error', 'Rekening tabungan tidak ditemukan.');
            }
            $settings = setting_akun::where('kategori', 'tabungan-tarik')->get();

            $akun_debit  = $settings->where('debit', 1)->first()?->akun_id;
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id;

            if (!$akun_debit || !$akun_kredit) {
                throw new \Exception("Setting akun untuk kategori tabungan-tarik belum lengkap.");
            }

            // Ambil saldo siswa
            $saldoSiswa = Saldo_keuangan::firstOrCreate(
                [
                    'akun_id' => $akun_debit,
                    'user_id' => $request->penerima_id
                ],
                ['saldo_akhir' => 0]
            );

            if ($saldoSiswa->saldo_akhir < $request->jumlah) {
                throw new \Exception("Saldo siswa tidak mencukupi untuk penarikan.");
            }

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'penarikan_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'created_by'      => Auth::id(),
            ]);

            // Jurnal Debit (akun siswa berkurang → debit 0, kredit jumlah)
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

            // Update saldo siswa (kurangi saldo)
            $saldoSiswa->decrement('saldo_akhir', $request->jumlah);

            // Catat log transaksi
            Keuangan_transaksi_logs::create([
                'transaksi_id'   => $transaksi->id,
                'aksi'           => 'withdraw',
                'data_lama'      => json_encode(['saldo_awal' => $saldoSiswa->saldo_akhir + $request->jumlah]),
                'data_baru'      => json_encode(['saldo_akhir' => $saldoSiswa->saldo_akhir]),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

//            DB::commit();

            return redirect()->route('tabungan.index')->with('success', 'Penarikan tabungan berhasil.');
        } catch (\Exception $e) {
            dd($e->getMessage());
//            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function show($siswa_id)
    {
        $siswa = Siswa::with('kelas')->findOrFail($siswa_id);

        // Ambil semua transaksi siswa
        $logs = Keuangan_transaksi::where('penerima_id', $siswa_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil saldo akhir dari saldo_keuangan
        $saldo = Saldo_keuangan::where('user_id', $siswa_id)->first();
        $saldo_akhir = $saldo?->saldo_akhir ?? 0;

        // Hitung saldo awal dari transaksi terakhir
        $saldo_awal = 0;
        if ($logs->isNotEmpty()) {
            $lastTrans = $logs->first();

            if ($lastTrans->jenis_transaksi === 'setoran_tabungan') {
                $saldo_awal = $saldo_akhir - $lastTrans->jumlah;
            } else {
                $saldo_awal = $saldo_akhir + $lastTrans->jumlah;
            }
        }

        return view('pages.tabungan.detail', compact(
            'siswa',
            'logs',
            'saldo_awal',
            'saldo_akhir'
        ));
    }
    public function status($id)
    {
        $status = Saldo_keuangan::findOrFail($id);
        $status->update([
            'status' => $status->status == 1 ? 0 : 1
            ]);

        return redirect()->route('tabungan.index')->with('success', 'Status tabungan berhasil diubah.');

    }



}

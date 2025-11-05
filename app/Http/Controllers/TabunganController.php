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
        $transaksis = Siswa::with('tahun_ajaran','user.saldo','kelas','unit')
            // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
            ->when(Auth::user()->yayasan_id, function ($query) {
                $query->whereHas('unit', function($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            })
            ->when(!Auth::user()->yayasan_id && Auth::user()->unit_id, function ($query, $unit_id) {
                $query->where('unit_id', $unit_id);
            })
            ->where('status','1')
            ->get();
        $total_setoran = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->when(Auth::user()->yayasan_id, function ($query) use ($unitIds) {
                $query->whereIn('unit_id', $unitIds);
            })
            ->when(!Auth::user()->yayasan_id && Auth::user()->unit_id, function ($query) {
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->sum('jumlah');

        $total_penarikan = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->when(Auth::user()->yayasan_id, function ($query) use ($unitIds) {
                $query->whereIn('unit_id', $unitIds);
            })
            ->when(!Auth::user()->yayasan_id && Auth::user()->unit_id, function ($query) {
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->sum('jumlah');
        return view('pages.tabungan.index', compact('transaksis','total_setoran','total_penarikan'));
    }

    /**
     * Form tambah transaksi tabungan
     */
    public function create()
    {
        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
        if (Auth::user()->yayasan_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status','1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status','1')->get();
        } else {
            $units = \App\Models\Unit::where('status','1')->get();
        }
        return view('pages.tabungan.create', compact('units'));
    }
    public function tarik()
    {
        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
        if (Auth::user()->yayasan_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status','1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status','1')->get();
        } else {
            $units = \App\Models\Unit::where('status','1')->get();
        }
        return view('pages.tabungan.tarik', compact('units'));
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
            $siswa = Siswa::with('user')->findOrFail($request->penerima_id);

            if(!$siswa){
                return back()->with('danger', 'Siswa tidak ditemukan.');
            }

            $rekening = Saldo_keuangan::with('user')->where('user_id', $siswa->user->id)->where('status', 1)->first();
            if(!$rekening){
                return back()->with('danger', 'Rekening tabungan belum Aktif.');
            }
            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TST' . date('YmdHis').$siswa->nisn.rand(1000,9999),
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'setoran_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'created_by'      => Auth::id(),
            ]);

            $settings = setting_akun::where('kategori', 'tabungan')
                ->when(Auth::user()->unit_id,function ($query,$unit_id){
                    $query->where('unit_id',$unit_id);
                })
                ->where('status','1')
                ->get();


            $akun_debit  = $settings->where('debit', 1)->first()?->akun_id;
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id;

            if (!$akun_debit || !$akun_kredit) {
                throw new \Exception("Setting akun untuk kategori tabungan belum lengkap.");
            }

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

            $saldoSiswa = Saldo_keuangan::with('user')
                ->firstOrCreate(
                    [
//                        'akun_id' => $akun_kredit,
                        'user_id' => $siswa->user->id,
                        'status' => 1
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
            return back()->with('danger', $e->getMessage());
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

        DB::beginTransaction();

        try {
            $siswa = Siswa::with('user')->findOrFail($request->penerima_id);
            if (!$siswa) {
                return back()->with('danger', 'Siswa tidak ditemukan.');
            }

            $rekening = Saldo_keuangan::where('user_id', $siswa->user->id)->where('status', 1)->first();
            if(!$rekening){
                return back()->with('danger', 'Rekening tabungan tidak ditemukan.');
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
//                    'akun_id' => $akun_debit,
                    'user_id' => $siswa->user->id,
                ],
                ['saldo_akhir' => 0]
            );

            if ($saldoSiswa->saldo_akhir < $request->jumlah) {
                throw new \Exception("Saldo siswa tidak mencukupi untuk penarikan.");
            }

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TRK' . date('YmdHis').$siswa->nisn.rand(1000,9999),
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

            DB::commit();

            return redirect()->route('tabungan.index')->with('success', 'Penarikan tabungan berhasil.');
        } catch (\Exception $e) {
//            dd($e->getMessage());
            DB::rollBack();
            return back()->with('danger',$e->getMessage());
        }
    }
    public function show($siswa_id)
    {
        $siswa = Siswa::with('kelas','user')->findOrFail($siswa_id);

        // Ambil semua transaksi siswa
        $logs = Keuangan_transaksi::where('penerima_id', $siswa_id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil saldo akhir dari saldo_keuangan
        $saldo = Saldo_keuangan::where('user_id', $siswa->user->id)->first();
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
    public function report(Request $request, $userId)
    {
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to ?? date('Y-m-t');

        // saldo terakhir
        $saldoAkhir = Saldo_keuangan::where('user_id', $userId)
            ->orderBy('last_updated', 'desc')
            ->value('saldo_akhir');

        // transaksi per user
        $transaksis = Keuangan_transaksi::where('penerima_id', $userId)
            ->whereBetween('tanggal_transaksi', [$from, $to])
            ->orderBy('tanggal_transaksi', 'asc')
            ->get();

        // saldo berjalan
        $saldoAwal = 0;
        $saldoBerjalan = $saldoAwal;
        $riwayat = [];

        foreach ($transaksis as $trx) {
            if ($trx->jenis_transaksi === 'setoran_tabungan') {
                $saldoBerjalan += $trx->jumlah;
            } elseif ($trx->jenis_transaksi === 'penarikan_tabungan') {
                $saldoBerjalan -= $trx->jumlah;
            }

            $riwayat[] = [
                'tanggal' => $trx->tanggal_transaksi,
                'jenis'   => $trx->jenis_transaksi,
                'jumlah'  => $trx->jumlah,
                'keterangan' => $trx->keterangan,
                'saldo'   => $saldoBerjalan,
            ];
        }


        return view('pages.report.tabungan.tabungan', compact(
            'userId', 'from', 'to', 'saldoAwal', 'saldoAkhir', 'riwayat'
        ));
    }
    public function reportAll(Request $request)
    {
        $from = $request->from ?? date('Y-m-01');
        $to   = $request->to ?? date('Y-m-t');

        $saldos = Saldo_keuangan::with('siswa')->get();

        $rekap = [];
        foreach ($saldos as $saldo) {
            $transaksis = Keuangan_transaksi::where('penerima_id', $saldo->user_id)
                ->where('penerima_tipe', Siswa::class)
                ->whereBetween('tanggal_transaksi', [$from, $to])
                ->get();


            $setoran   = $transaksis->where('jenis_transaksi', 'setoran_tabungan')->sum('jumlah');
            $penarikan = $transaksis->where('jenis_transaksi', 'penarikan_tabungan')->sum('jumlah');

            $rekap[] = [
                'nama'        => $saldo->siswa->nisn ?? 'Siswa-'.$saldo->user_id,
                'setoran'     => $setoran,
                'penarikan'   => $penarikan,
                'saldo_akhir' => $saldo->saldo_akhir,
            ];
        }


        return view('pages.report.tabungan.tabunganall', compact(
            'rekap', 'from', 'to'
        ));
    }



}

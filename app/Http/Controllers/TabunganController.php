<?php

namespace App\Http\Controllers;

use App\Models\DataRekening;
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
use Mpdf\Mpdf;

class TabunganController extends Controller
{
    /**
     * List transaksi tabungan
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $transaksis = Siswa::with('tahun_ajaran', 'user.saldo', 'kelas', 'unit')
            ->when($request->filled('unit_id') && $request->unit_id != '', function ($query) use ($request) {
                $query->where('unit_id', $request->unit_id);
            })
            ->when(!$request->filled('unit_id') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                $query->whereHas('unit', function ($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            })
            ->when(!$request->filled('unit_id') && Auth::user()->unit_id, function ($query) {
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nisn', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('kelas', function ($q) use ($search) {
                          $q->where('nama_kelas', 'like', "%{$search}%");
                      });
                });
            })
            ->where('status', '1')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->except('page'));

        // Ambil semua siswa_id dari siswa yang lolos filter
        $siswaIds = $transaksis->pluck('id')->unique()->toArray();

        // Build base query with unit filtering if auth user has unit_id
        $baseQuery = function ($query) use ($siswaIds, $request) {
            return $query->where('penerima_tipe', Siswa::class)
                ->whereIn('penerima_id', $siswaIds)
                ->when(Auth::user()->unit_id, function ($q) {
                    // Join with siswa table to filter by unit_id
                    $q->join('siswas', 'keuangan_transaksis.penerima_id', '=', 'siswas.id')
                      ->where('siswas.unit_id', Auth::user()->unit_id)
                      ->select('keuangan_transaksis.*');
                })
                ->when($request->filled('dari_tanggal'), function ($q) use ($request) {
                    $q->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
                })
                ->when($request->filled('sampai_tanggal'), function ($q) use ($request) {
                    $q->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
                });
        };

        // Total setoran
        $total_setoran = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->tap($baseQuery)
            ->sum('jumlah');

        // Total penarikan (approved/approve status only)
        $total_penarikan = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where(function ($query) {
                $query->where('status_approval', '=', 'approve')
                      ->orWhere('status_approval', '=', 'approved');
            })
            ->tap($baseQuery)
            ->sum('jumlah');

        // Total transaksi (count)
        $jumlah_transaksi = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
            ->tap($baseQuery)
            ->count();

        // Total pending penarikan
        $total_pending = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where('status_approval', 'pending')
            ->tap($baseQuery)
            ->count();

        // Total pending setoran
        $total_pending_setoran = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->where('status_approval', 'pending')
            ->tap($baseQuery)
            ->count();

        // Total approved penarikan
        $total_approved = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->whereIn('status_approval', ['approve', 'approved'])
            ->tap($baseQuery)
            ->count();

        // Total approved setoran
        $total_approved_setoran = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->whereIn('status_approval', ['approve', 'approved'])
            ->tap($baseQuery)
            ->count();

        // Total rejected penarikan
        $total_rejected = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->whereIn('status_approval', ['reject', 'rejected'])
            ->tap($baseQuery)
            ->count();

        // Total rejected setoran
        $total_rejected_setoran = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->whereIn('status_approval', ['reject', 'rejected'])
            ->tap($baseQuery)
            ->count();

        // Get units for filter
        if (Auth::user()->yayasan_id && !Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->orderBy('nama_unit')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
        } else {
            $units = \App\Models\Unit::where('status', '1')->orderBy('nama_unit')->get();
        }

        return view('pages.tabungan.index', compact(
            'transaksis',
            'total_setoran',
            'total_penarikan',
            'jumlah_transaksi',
            'total_pending',
            'total_approved',
            'total_rejected',
            'total_pending_setoran',
            'total_approved_setoran',
            'total_rejected_setoran',
            'units'
        ));
    }

    /**
     * Form tambah transaksi tabungan
     */
    public function create()
    {
        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
        if (Auth::user()->yayasan_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
        } else {
            $units = \App\Models\Unit::where('status', '1')->get();
        }
        return view('pages.tabungan.create', compact('units'));
    }
    public function tarik()
    {
        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
        if (Auth::user()->yayasan_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
        } else {
            $units = \App\Models\Unit::where('status', '1')->get();
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
            'jumlah'         => 'required|numeric',
            'keterangan'     => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Ambil data siswa
            $siswa = Siswa::with('user')->findOrFail($request->penerima_id);

            if (!$siswa) {
                return back()->with('danger', 'Siswa tidak ditemukan.');
            }

            $rekening = Saldo_keuangan::with('user')->where('user_id', $siswa->user->id)->where('status', 1)->first();
            if (!$rekening) {
                return back()->with('danger', 'Rekening tabungan belum Aktif.');
            }

            // Generate token 5 digit
            //            $token = str_pad(rand(0, 99999), 6, '0', STR_PAD_LEFT);

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TST' . date('YmdHis').rand(1000, 9999),
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'setoran_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'metode' => 'TUNAI',
                'token'           => null,
                'status_approval' => 'approved',
                'created_by'      => Auth::id(),
            ]);

            $settings = setting_akun::where('kategori', 'tabungan');

            // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
            if (Auth::user()->yayasan_id) {
                // Jika user punya yayasan_id, tampilkan akun dari semua unit di yayasan tersebut
                $settings->whereHas('unit', function ($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            } elseif (Auth::user()->unit_id) {
                // Jika user punya unit_id, tampilkan akun dari unit tersebut saja
                $settings->where('unit_id', Auth::user()->unit_id);
            } elseif ($request->filled('unit_id')) {
                // Admin user filtering by unit
                $settings->where('unit_id', $request->unit_id);
            }

            $settings = $settings->where('status', '1')->first();

            if(!$settings){
                return back()->with('danger', 'Setting akun tabungan belum diatur.');
            }

            $akun_id = $settings->akun_id;
            $position = $settings->debit;


            $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                ->first();



            if (!$datarekening) {
                return back()->with('danger', 'Rekening tabungan tidak ditemukan.');
            }


            if($datarekening->allotment == 'Semua Pembayaran'){
                $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                    ->where('allotment','Semua Pembayaran')
                    ->first();
            }else{
                $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                    ->where('allotment','Pembayaran Tabungan')
                    ->first();
            }


            if($position == 1){
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'        => 0,
                    'kredit'       => $request->jumlah,
                    'keterangan'   => $request->keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'kredit'        => 0,
                    'debit'       => $request->jumlah,
                    'keterangan'   => $request->keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);
            }else{
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'       => $request->jumlah,
                    'kredit'        => 0,
                    'keterangan'   => $request->keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'kredit'       => $request->jumlah,
                    'debit'        => 0,
                    'keterangan'   => $request->keterangan,
                    'unit_id' => Auth::user()->unit_id
                ]);
            }
            // Jurnal Kredit




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
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);
            $saldoSiswa->increment('saldo_akhir', $request->jumlah);
            DB::commit();

            return redirect()->route('tabungan.show', $siswa->id)
                ->with('success', 'Transaksi berhasil disimpan!')
                ->with('token', null)
                ->with('transaksi_id', $transaksi->id);
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
            'jumlah'      => 'required|numeric|min:1000',
            'keterangan'  => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $siswa = Siswa::with('user')->findOrFail($request->penerima_id);
            if (!$siswa) {
                return back()->with('danger', 'Siswa tidak ditemukan.');
            }

            $rekening = Saldo_keuangan::where('user_id', $siswa->user->id)->where('status', 1)->first();
            if (!$rekening) {
                return back()->with('danger', 'Rekening tabungan tidak ditemukan.');
            }

            // Ambil setting akun tabungan-tarik dengan filter unit
            $settings = setting_akun::where('kategori', 'tabungan-tarik');

            if (Auth::user()->yayasan_id) {
                $settings->whereHas('unit', function ($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            } elseif (Auth::user()->unit_id) {
                $settings->where('unit_id', Auth::user()->unit_id);
            } elseif ($request->filled('unit_id')) {
                $settings->where('unit_id', $request->unit_id);
            }

            $settings = $settings->where('status', '1')->first();

            if ($settings == null) {
                return back()->with('danger', "Setting akun untuk kategori tabungan-tarik belum lengkap.");
            }

            $akun_id = $settings->akun_id;

            if (!$akun_id) {
                return back()->with('danger', "Akun untuk kategori tabungan-tarik belum dikonfigurasi. Silakan hubungi administrator.");
            }

            $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                ->first();

            if (!$datarekening) {
                return back()->with('danger', 'Rekening tabungan tidak ditemukan.');
            }


            if($datarekening->allotment == 'Semua Pembayaran'){
                $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                    ->where('allotment','Semua Pembayaran')
                    ->first();
            }else{
                $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                    ->where('allotment','Pembayaran Tabungan')
                    ->first();
            }
            // Ambil saldo siswa
            $saldoSiswa = Saldo_keuangan::firstOrCreate(
                [
                    'user_id' => $siswa->user->id,
                ],
                ['saldo_akhir' => 0]
            );

            if ($saldoSiswa->saldo_akhir < $request->jumlah) {
                throw new \Exception("Saldo siswa tidak mencukupi untuk penarikan.");
            }

            // Generate token 5 digit
            $token = str_pad(rand(0, 99999), 6, '0', STR_PAD_LEFT);

            // Simpan transaksi utama dengan status pending
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TRK' . date('YmdHis').rand(1000, 9999),
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'penarikan_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'metode' => 'TUNAI',
                'token'           => $token,
                'token_expired_at' => now()->addDay(),
                'status_approval' => 'pending',
                'created_by'      => Auth::id(),
                'status_verifikasi' => 'pending'
            ]);

            // Query setting_akuns untuk akun data rekening
            $settingRekening = setting_akun::where('akun_id', $datarekening->akun_id)
                ->where('status', '1')
                ->first();

            $positionRekening = $settingRekening ? $settingRekening->debit : 0;

            // Buat jurnal berdasarkan posisi debit/kredit
            if($positionRekening == 1){
                // Jika rekening di debit
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'        => 0,
                    'kredit'       => $request->jumlah,
                    'keterangan'   => $request->keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'debit'        => $request->jumlah,
                    'kredit'       => 0,
                    'keterangan'   => $request->keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);
            }else{
                // Jika rekening di kredit
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'        => $request->jumlah,
                    'kredit'       => 0,
                    'keterangan'   => $request->keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'debit'        => 0,
                    'kredit'       => $request->jumlah,
                    'keterangan'   => $request->keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);
            }

            // Jurnal Debit: Akun penarikan tabungan

            // Catat log transaksi
            Keuangan_transaksi_logs::create([
                'transaksi_id'   => $transaksi->id,
                'aksi'           => 'withdraw_request',
                'data_lama'      => json_encode(['saldo_saat_ini' => $saldoSiswa->saldo_akhir]),
                'data_baru'      => json_encode(['status' => 'pending', 'token_generated' => true]),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return redirect()->route('keuangan_transaksi.show', $transaksi->id)
                ->with('success', 'Permintaan penarikan berhasil dibuat! Gunakan token untuk memverifikasi dan menyetujui penarikan.')
                ->with('token', $token)
                ->with('transaksi_id', $transaksi->id)
                ->with('info', 'Saldo belum dikurangi. Penarikan akan diproses setelah token diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('danger', $e->getMessage());
        }
    }
    public function show(Request $request, $siswa_id)
    {
        $siswa = Siswa::with('kelas', 'user')->findOrFail($siswa_id);

        // Query untuk search
        $query = Keuangan_transaksi::where('penerima_id', $siswa_id)
            ->where('jenis_transaksi', 'like', '%tabungan%')
            ->orderBy('created_at', 'asc');

        // Filter berdasarkan jenis transaksi jika ada
        if ($request->filled('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->jenis_transaksi);
        }

        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $query->where('status_approval', $request->status);
        }

        // Filter berdasarkan tanggal jika ada
        if ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }

        // Ambil semua data untuk perhitungan running balance
        $allLogs = $query->get();

        // Ambil saldo akhir dari saldo_keuangan
        $saldo = Saldo_keuangan::where('user_id', $siswa->user->id)->first();
        $saldo_akhir = $saldo?->saldo_akhir ?? 0;

        // Hitung saldo awal dan saldo untuk setiap transaksi
        $saldo_awal = 0;
        $runningBalance = 0;

        if ($allLogs->isNotEmpty()) {
            // Hitung saldo awal (kebalikan dari saldo akhir dengan semua transaksi)
            foreach ($allLogs as $log) {
                if ($log->jenis_transaksi === 'setoran_tabungan' &&
                    in_array($log->status_approval, ['approve', 'approved'])) {
                    $runningBalance += $log->jumlah;
                } elseif ($log->jenis_transaksi === 'penarikan_tabungan' &&
                          in_array($log->status_approval, ['approve', 'approved'])) {
                    $runningBalance -= $log->jumlah;
                }
            }
            $saldo_awal = $saldo_akhir - $runningBalance;
        }

        // Tambahkan saldo_sebelum dan saldo_sesudah untuk setiap transaksi
        $runningBalance = $saldo_awal;
        foreach ($allLogs as $log) {
            $log->saldo_sebelum = $runningBalance;

            if ($log->jenis_transaksi === 'setoran_tabungan' &&
                in_array($log->status_approval, ['approve', 'approved'])) {
                $runningBalance += $log->jumlah;
            } elseif ($log->jenis_transaksi === 'penarikan_tabungan' &&
                      in_array($log->status_approval, ['approve', 'approved'])) {
                $runningBalance -= $log->jumlah;
            }

            $log->saldo_sesudah = $runningBalance;
        }

        // Reverse untuk display (paling baru di atas)
        $allLogs = $allLogs->reverse()->values();

        // Pagination: 10 per halaman
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $total = $allLogs->count();

        $paginatedLogs = $allLogs->slice(($currentPage - 1) * $perPage, $perPage)->values();

        // Buat LengthAwarePaginator object
        $logs = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => route('tabungan.show', $siswa_id),
                'query' => $request->query(),
            ]
        );

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
            'userId',
            'from',
            'to',
            'saldoAwal',
            'saldoAkhir',
            'riwayat'
        ));
    }
    public function reportAll(Request $request)
    {
        // Get filter parameters
        $from = $request->from ?? date('Y-m-01');
        $to = $request->to ?? date('Y-m-t');
        $unit_id = $request->unit_id; // Ambil dari request, bisa kosong untuk "Semua Unit"

        // Get units for filter based on user role
        $unitsQuery = \App\Models\Unit::query();
        if (auth()->user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan unit dari yayasan tersebut
            $unitsQuery->where('yayasan_id', auth()->user()->yayasan_id);
        } elseif (auth()->user()->unit_id) {
            // Jika user punya unit_id, tampilkan unit tersebut saja
            $unitsQuery->where('id', auth()->user()->unit_id);
        }
        $units = $unitsQuery->get();

        // Build query dari Siswa dan join dengan saldo
        $query = Siswa::with(['user', 'kelas', 'unit', 'saldo']);

        // Filter by unit berdasarkan pilihan user atau role
        if ($unit_id) {
            // Jika ada unit_id dipilih, filter sesuai unit tersebut
            $query->where('unit_id', $unit_id);
        } else {
            // Jika "Semua Unit" dipilih, filter berdasarkan role user
            if (auth()->user()->yayasan_id) {
                // Tampilkan semua unit di yayasan
                $query->whereHas('unit', function($q) {
                    $q->where('yayasan_id', auth()->user()->yayasan_id);
                });
            } elseif (auth()->user()->unit_id) {
                // Jika user punya unit_id, tetap filter ke unit mereka
                $query->where('unit_id', auth()->user()->unit_id);
            }
            // Jika super admin (tidak punya yayasan_id dan unit_id), tampilkan semua
        }

        // Hanya ambil siswa yang punya saldo
        $query->whereHas('saldo');

        $siswas = $query->get();

        $rekap = [];
        $totalSetoran = 0;
        $totalPenarikan = 0;
        $totalSaldo = 0;

        foreach ($siswas as $siswa) {
            if (!$siswa->user || !$siswa->saldo) continue;

            // Get transactions in periode
            $transaksis = Keuangan_transaksi::where('penerima_id', $siswa->id)
                ->where('penerima_tipe', Siswa::class)
                ->where('status_verifikasi', 'approved')
                ->whereBetween('tanggal_transaksi', [$from, $to])
                ->get();

            $setoran = $transaksis->where('jenis_transaksi', 'setoran_tabungan')->sum('jumlah');
            $penarikan = $transaksis->where('jenis_transaksi', 'penarikan_tabungan')->sum('jumlah');
            $saldoAkhir = $siswa->saldo->saldo_akhir ?? 0;

            $rekap[] = [
                'siswa_id' => $siswa->id,
                'nisn' => $siswa->nisn ?? '-',
                'nama' => $siswa->user->name ?? '-',
                'kelas' => $siswa->kelas->nama_kelas ?? '-',
                'unit' => $siswa->unit->nama_unit ?? '-',
                'setoran' => $setoran,
                'penarikan' => $penarikan,
                'saldo_akhir' => $saldoAkhir,
            ];

            $totalSetoran += $setoran;
            $totalPenarikan += $penarikan;
            $totalSaldo += $saldoAkhir;
        }

        $summary = [
            'jumlah_siswa' => count($rekap),
            'total_setoran' => $totalSetoran,
            'total_penarikan' => $totalPenarikan,
            'total_saldo' => $totalSaldo,
        ];

        // Handle export
        if ($request->has('export')) {
            return $this->exportTabungan($request->export, $rekap, $summary, $from, $to);
        }

        return view('pages.report.tabungan.tabunganall', compact(
            'rekap',
            'summary',
            'from',
            'to',
            'units',
            'unit_id'
        ));
    }

    private function exportTabungan($type, $rekap, $summary, $from, $to)
    {
        if ($type === 'excel') {
            return \Excel::download(
                new \App\Exports\TabunganReportExport($rekap, $summary, $from, $to),
                'laporan-tabungan-' . date('Y-m-d') . '.xlsx'
            );
        } elseif ($type === 'pdf') {
            $pdf = \PDF::loadView('pages.report.tabungan.tabungan-pdf', compact('rekap', 'summary', 'from', 'to'));
            return $pdf->download('laporan-tabungan-' . date('Y-m-d') . '.pdf');
        }
    }

    /**
     * Verify token dan approve/reject transaksi
     */
    public function verifyToken(Request $request)
    {
        $request->validate([
            'transaksi_id' => 'required|exists:keuangan_transaksis,id',
            'token' => 'required|string|size:6',
            'action' => 'required|in:approve,reject'
        ]);

        DB::beginTransaction();

        try {
            $transaksi = Keuangan_transaksi::findOrFail($request->transaksi_id);

            // Cek apakah transaksi sudah di-approve/reject sebelumnya
            if ($transaksi->status_approval !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah ' . ($transaksi->status_approval === 'approved' ? 'disetujui' : 'ditolak') . ' sebelumnya.'
                ], 400);
            }

            // Cek apakah token sudah expired
            if (now()->greaterThan($transaksi->token_expired_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token sudah kadaluarsa. Silakan buat transaksi baru.'
                ], 400);
            }

            // Verifikasi token
            if ($request->token !== $transaksi->token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid. Silakan coba lagi.'
                ], 400);
            }

            // Ambil data siswa
            $siswa = Siswa::with('user')->findOrFail($transaksi->penerima_id);
            $saldoSiswa = Saldo_keuangan::where('user_id', $siswa->user->id)->first();

            if (!$saldoSiswa) {
                throw new \Exception('Rekening tabungan siswa tidak ditemukan.');
            }

            if ($request->action === 'approve') {
                // Untuk penarikan tabungan: kurangi saldo dan buat jurnal
                if ($transaksi->jenis_transaksi === 'penarikan_tabungan') {
                    // Cek saldo cukup
                    if ($saldoSiswa->saldo_akhir < $transaksi->jumlah) {
                        throw new \Exception('Saldo siswa tidak mencukupi untuk penarikan.');
                    }

                    // Ambil setting akun untuk tabungan-tarik
                    $settings = setting_akun::where('kategori', 'tabungan-tarik')
                        ->where('status', '1')
                        ->first();

                    if (!$settings || !$settings->akun_id) {
                        throw new \Exception('Setting akun untuk kategori tabungan-tarik belum lengkap.');
                    }

                    // Buat jurnal Debit
//                    Jurnals::create([
//                        'transaksi_id' => $transaksi->id,
//                        'akun_id'      => $settings->akun_id,
//                        'debit'        => $transaksi->jumlah,
//                        'kredit'       => 0,
//                        'keterangan'   => $transaksi->keterangan,
//                    ]);

                    // Kurangi saldo siswa
                    $saldoSiswa->decrement('saldo_akhir', $transaksi->jumlah);
                }

                // Update status approval
                $transaksi->update([
                    'status_approval' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'status_verifikasi' => 'approved',
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                ]);

                $message = 'Transaksi berhasil disetujui dan saldo telah diperbarui!';
                $logAksi = 'approve';

            } else {
                // Untuk reject: tidak ada perubahan saldo karena saldo belum pernah dikurangi
                $transaksi->update([
                    'status_approval' => 'rejected',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'status_verifikasi' => 'rejected',
                    'verified_at' => now(),
                    'verified_by' => Auth::id(),
                ]);

                $message = 'Transaksi berhasil ditolak.';
                $logAksi = 'reject';
            }

            // Log activity
            Keuangan_transaksi_logs::create([
                'transaksi_id'   => $transaksi->id,
                'aksi'           => $logAksi,
                'data_lama'      => json_encode(['status_approval' => 'pending', 'saldo_sebelum' => $saldoSiswa->saldo_akhir]),
                'data_baru'      => json_encode(['status_approval' => $transaksi->status_approval, 'saldo_sesudah' => $saldoSiswa->fresh()->saldo_akhir]),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'transaksi_id' => $transaksi->id,
                    'code_pembayaran' => $transaksi->code_pembayaran,
                    'status_approval' => $transaksi->status_approval,
                    'saldo_akhir' => $saldoSiswa->fresh()->saldo_akhir
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print laporan tabungan menggunakan mPDF
     */
    public function printLaporan(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $transaksis = Siswa::with('tahun_ajaran', 'user.saldo', 'kelas', 'unit')
            ->when($request->filled('unit_id') && $request->unit_id != '', function ($query) use ($request) {
                $query->where('unit_id', $request->unit_id);
            })
            ->when(!$request->filled('unit_id') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                $query->whereHas('unit', function ($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            })
            ->when(!$request->filled('unit_id') && Auth::user()->unit_id, function ($query) {
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nisn', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('kelas', function ($q) use ($search) {
                          $q->where('nama_kelas', 'like', "%{$search}%");
                      });
                });
            })
            ->where('status', '1')
            ->get();

        $siswaIds = $transaksis->pluck('id')->unique()->toArray();

        // Filter transaksi keuangan berdasarkan penerima_id (siswa_id) dan penerima_tipe dengan date range
        $total_setoran = Keuangan_transaksi::where('jenis_transaksi', 'setoran_tabungan')
            ->where('penerima_tipe', Siswa::class)
            ->whereIn('penerima_id', $siswaIds)
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
            })
            ->sum('jumlah');

        $total_penarikan = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where('penerima_tipe', Siswa::class)
            ->where(function ($query) {
                $query->where('status_approval', '=', 'approve')
                      ->orWhere('status_approval', '=', 'approved');
            })
            ->whereIn('penerima_id', $siswaIds)
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
            })
            ->sum('jumlah');

        // Jumlah transaksi (count)
        $jumlah_transaksi = Keuangan_transaksi::whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])
            ->where('penerima_tipe', Siswa::class)
            ->whereIn('penerima_id', $siswaIds)
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
            })
            ->count();

        // Total pending transaksi tabungan
        $total_pending = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where('penerima_tipe', Siswa::class)
            ->where('status_approval', 'pending')
            ->whereIn('penerima_id', $siswaIds)
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
            })
            ->sum('jumlah');

        // Total approved penarikan
        $total_approved = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where('penerima_tipe', Siswa::class)
            ->whereIn('status_approval', ['approve', 'approved'])
            ->whereIn('penerima_id', $siswaIds)
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
            })
            ->sum('jumlah');

        // Total reject penarikan
        $total_rejected = Keuangan_transaksi::where('jenis_transaksi', 'penarikan_tabungan')
            ->where('penerima_tipe', Siswa::class)
            ->whereIn('status_approval', ['reject', 'rejected'])
            ->whereIn('penerima_id', $siswaIds)
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
            })
            ->sum('jumlah');

        $dari_tanggal = $request->dari_tanggal ?? '';
        $sampai_tanggal = $request->sampai_tanggal ?? '';

        // Generate HTML dari view
        $html = view('pages.tabungan.pdf_laporan', compact(
            'transaksis',
            'total_setoran',
            'total_penarikan',
            'jumlah_transaksi',
            'total_pending',
            'total_approved',
            'total_rejected',
            'dari_tanggal',
            'sampai_tanggal'
        ))->render();

        // Konfigurasi mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L', // Landscape untuk tabel lebar
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $mpdf->SetTitle('Laporan Tabungan Siswa');
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Laporan-Tabungan-' . date('Ymd') . '.pdf', 'I');
    }

    /**
     * Print Mutasi Tabungan per 20 transaksi
     */
    public function printMutasi($siswa_id)
    {
        $siswa = Siswa::with('kelas', 'user')->findOrFail($siswa_id);

        // Ambil semua transaksi siswa (urutkan dari paling lama ke paling baru)
        $allLogs = Keuangan_transaksi::where('penerima_id', $siswa_id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil saldo akhir dari saldo_keuangan
        $saldo = Saldo_keuangan::where('user_id', $siswa->user->id)->first();
        $saldo_akhir = $saldo?->saldo_akhir ?? 0;

        // Hitung saldo awal
        $saldo_awal = 0;
        $runningBalance = 0;

        if ($allLogs->isNotEmpty()) {
            foreach ($allLogs as $log) {
                if ($log->jenis_transaksi === 'setoran_tabungan' &&
                    in_array($log->status_approval, ['approve', 'approved'])) {
                    $runningBalance += $log->jumlah;
                } elseif ($log->jenis_transaksi === 'penarikan_tabungan' &&
                          in_array($log->status_approval, ['approve', 'approved'])) {
                    $runningBalance -= $log->jumlah;
                }
            }
            $saldo_awal = $saldo_akhir - $runningBalance;
        }

        // Tambahkan saldo_sebelum dan saldo_sesudah untuk setiap transaksi
        $runningBalance = $saldo_awal;
        foreach ($allLogs as $log) {
            $log->saldo_sebelum = $runningBalance;

            if ($log->jenis_transaksi === 'setoran_tabungan' &&
                in_array($log->status_approval, ['approve', 'approved'])) {
                $runningBalance += $log->jumlah;
            } elseif ($log->jenis_transaksi === 'penarikan_tabungan' &&
                      in_array($log->status_approval, ['approve', 'approved'])) {
                $runningBalance -= $log->jumlah;
            }

            $log->saldo_sesudah = $runningBalance;
        }

        // Reverse untuk display (paling baru di atas)
        $allLogs = $allLogs->reverse()->values();

        // Split per 20 transaksi
        $logsChunked = $allLogs->chunk(20);
        $perPage = 20;

        // Ambil halaman dari request, default 1
        $page = request('page', 1);
        $pageIndex = max(1, $page) - 1; // Index dimulai dari 0

        if ($pageIndex >= $logsChunked->count()) {
            $pageIndex = $logsChunked->count() - 1;
        }

        $logs = $logsChunked[$pageIndex];
        $totalPages = $logsChunked->count();
        $currentPage = $pageIndex + 1;

        // Generate HTML dari view
        $html = view('pages.tabungan.print_mutasi', compact(
            'siswa',
            'logs',
            'saldo_awal',
            'saldo_akhir',
            'currentPage',
            'totalPages'
        ))->render();

        // Konfigurasi mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L', // Landscape untuk tabel lebar
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $mpdf->SetTitle('Mutasi Tabungan - ' . $siswa->user->name);
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Mutasi-' . $siswa->nisn . '-' . $currentPage . '-' . date('Ymd') . '.pdf', 'I');
    }

    /**
     * Print Struk Transaksi (Thermal Printer)
     */
    public function printStruk($transaksi_id)
    {
        $transaksi = Keuangan_transaksi::findOrFail($transaksi_id);
        $siswa = Siswa::with('kelas', 'user')->findOrFail($transaksi->penerima_id);

        // Ambil saldo siswa sebelum dan sesudah transaksi
        $allLogs = Keuangan_transaksi::where('penerima_id', $transaksi->penerima_id)
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil saldo akhir dari saldo_keuangan
        $saldo = Saldo_keuangan::where('user_id', $siswa->user->id)->first();
        $saldo_akhir = $saldo?->saldo_akhir ?? 0;

        // Hitung saldo awal
        $saldo_awal = 0;
        $runningBalance = 0;

        if ($allLogs->isNotEmpty()) {
            foreach ($allLogs as $log) {
                if ($log->jenis_transaksi === 'setoran_tabungan' &&
                    in_array($log->status_approval, ['approve', 'approved'])) {
                    $runningBalance += $log->jumlah;
                } elseif ($log->jenis_transaksi === 'penarikan_tabungan' &&
                          in_array($log->status_approval, ['approve', 'approved'])) {
                    $runningBalance -= $log->jumlah;
                }
            }
            $saldo_awal = $saldo_akhir - $runningBalance;
        }

        // Tambahkan saldo_sebelum dan saldo_sesudah untuk setiap transaksi
        $runningBalance = $saldo_awal;
        foreach ($allLogs as $log) {
            $log->saldo_sebelum = $runningBalance;

            if ($log->jenis_transaksi === 'setoran_tabungan' &&
                in_array($log->status_approval, ['approve', 'approved'])) {
                $runningBalance += $log->jumlah;
            } elseif ($log->jenis_transaksi === 'penarikan_tabungan' &&
                      in_array($log->status_approval, ['approve', 'approved'])) {
                $runningBalance -= $log->jumlah;
            }

            $log->saldo_sesudah = $runningBalance;

            if ($log->id === $transaksi->id) {
                $current_transaction = $log;
            }
        }

        // Generate HTML dari view
        $html = view('pages.tabungan.struk', compact(
            'siswa',
            'transaksi',
            'current_transaction'
        ))->render();

        // Konfigurasi mPDF untuk thermal printer (80mm width)
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 200], // 80mm lebar, 200mm panjang (adjustable)
            'margin_left' => 2,
            'margin_right' => 2,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $mpdf->SetTitle('Struk Transaksi Tabungan');
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Struk-' . $transaksi->code_pembayaran . '-' . date('Ymd') . '.pdf', 'I');
    }

    public function massStatus(Request $request)
    {
        $ids = $request->input('ids', []);
        $status = $request->input('status'); // 1 untuk aktif, 0 untuk non-aktif

        if (empty($ids)) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada data yang dipilih.'], 400);
        }

        if (!in_array($status, [0, 1], true)) {
            return response()->json(['status' => 'error', 'message' => 'Status tidak valid.'], 400);
        }

        // Ambil semua saldo berdasarkan ID
        $saldos = \App\Models\Saldo_keuangan::whereIn('id', $ids)->get();

        if ($saldos->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
        }

        // Update status sesuai parameter yang dikirim
        $updated = 0;
        foreach ($saldos as $saldo) {
            $saldo->update(['status' => $status]);
            $updated++;
        }

        $statusText = $status == 1 ? 'diaktifkan' : 'dinonaktifkan';
        return response()->json([
            'status' => 'success',
            'message' => "$updated tabungan berhasil $statusText."
        ]);
    }
    /**
     * Upload bukti transfer untuk transaksi tabungan
     */
    public function uploadBuktiTransfer(Request $request, $id)
    {
        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        try {
            $transaksi = Keuangan_transaksi::findOrFail($id);

            // Hapus file lama jika ada
            if ($transaksi->bukti_transfer && file_exists(public_path($transaksi->bukti_transfer))) {
                unlink(public_path($transaksi->bukti_transfer));
            }

            // Upload file baru
            $file = $request->file('bukti_transfer');
            $filename = 'bukti_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path('uploads/bukti_transfer'), $filename);

            // Update database
            $transaksi->update([
                'bukti_transfer' => 'uploads/bukti_transfer/' . $filename,
                'status_verifikasi' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti transfer berhasil diupload',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload bukti transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve transaksi tabungan
     */
    public function approveTransaksi(Request $request, $id)
    {
        try {
            $transaksi = Keuangan_transaksi::findOrFail($id);

            $transaksi->update([
                'status_verifikasi' => 'approved',
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diapprove',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal approve transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject transaksi tabungan
     */
    public function rejectTransaksi(Request $request, $id)
    {
        $request->validate([
            'catatan_verifikasi' => 'required|string'
        ]);

        try {
            $transaksi = Keuangan_transaksi::findOrFail($id);

            $transaksi->update([
                'status_verifikasi' => 'rejected',
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'verified_by' => Auth::id(),
                'verified_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil direject',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reject transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

}

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
use Mpdf\Mpdf;

class TabunganController extends Controller
{
    /**
     * List transaksi tabungan
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $transaksis = Siswa::with('tahun_ajaran','user.saldo','kelas','unit')
            ->when($request->filled('unit_id') && $request->unit_id != '', function ($query) use ($request) {
                $query->where('unit_id', $request->unit_id);
            })
            ->when(!$request->filled('unit_id') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                $query->whereHas('unit', function($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            })
            ->when(!$request->filled('unit_id') && Auth::user()->unit_id, function ($query) {
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nisn', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('kelas', function($q) use ($search) {
                          $q->where('nama_kelas', 'like', "%{$search}%");
                      });
                });
            })
            ->where('status','1')
            ->paginate($perPage)
            ->appends($request->except('page'));

// Ambil semua siswa_id dari siswa yang lolos filter
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
            ->where(function($query) {
                $query->where('status_approval','=', 'approve')
                      ->orWhere('status_approval','=', 'approved');
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

        // Total pending transaksi tabungan (penarikan yang masih pending)
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

        // Get units for filter
        if (Auth::user()->yayasan_id && !Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status','1')->orderBy('nama_unit')->get();
        } elseif (Auth::user()->unit_id) {
            $units = \App\Models\Unit::where('id', Auth::user()->unit_id)->where('status','1')->get();
        } else {
            $units = \App\Models\Unit::where('status','1')->orderBy('nama_unit')->get();
        }

        return view('pages.tabungan.index', compact(
            'transaksis',
            'total_setoran',
            'total_penarikan',
            'jumlah_transaksi',
            'total_pending',
            'total_approved',
            'total_rejected',
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

            // Generate token 5 digit
//            $token = str_pad(rand(0, 99999), 6, '0', STR_PAD_LEFT);

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TST' . date('YmdHis').rand(1000,9999),
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'setoran_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'metode' => 'CASH',
                'token'           => null,
                'status_approval' => null,
                'created_by'      => Auth::id(),
            ]);

            $settings = setting_akun::where('kategori', 'tabungan');

            // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
            if (Auth::user()->yayasan_id) {
                // Jika user punya yayasan_id, tampilkan akun dari semua unit di yayasan tersebut
                $settings->whereHas('unit', function($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            } elseif (Auth::user()->unit_id) {
                // Jika user punya unit_id, tampilkan akun dari unit tersebut saja
                $settings->where('unit_id', Auth::user()->unit_id);
            } elseif ($request->filled('unit_id')) {
                // Admin user filtering by unit
                $settings->where('unit_id', $request->unit_id);
            }

            $settings = $settings->where('status','1')->first();
            $akun_id = $settings->akun_id;


            if ( !$akun_id) {
                throw new \Exception("Setting akun untuk kategori tabungan belum lengkap.");
            }



            // Jurnal Kredit
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => $akun_id,
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
            $settings = $settings->where('status','1')->first();
            $akun_id = $settings->akun_id;

            if (!$akun_id) {
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

            // Generate token 5 digit
            $token = str_pad(rand(0, 99999), 6, '0', STR_PAD_LEFT);

            // Simpan transaksi utama
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'TRK' . date('YmdHis').rand(1000,9999),
                'penerima_id'     => $request->penerima_id,
                'penerima_tipe'   => Siswa::class,
                'jenis_transaksi' => 'penarikan_tabungan',
                'jumlah'          => $request->jumlah,
                'keterangan'      => $request->keterangan,
                'metode' => 'Tunai',
                'token'           => $token,
                'token_expired_at'=> now()->addDay(),
                'status_approval' => 'pending',
                'created_by'      => Auth::id(),
            ]);
            // Jurnal Debit (akun siswa berkurang → debit 0, kredit jumlah)
            Jurnals::create([
                'transaksi_id' => $transaksi->id,
                'akun_id'      => $akun_id,
                'debit'        => $request->jumlah,
                'kredit'       => 0,
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

            return redirect()->route('tabungan.show', $siswa->id)
                ->with('success', 'Penarikan tabungan berhasil!')
                ->with('token', $token)
                ->with('transaksi_id', $transaksi->id);
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

            // Update status approval
            $transaksi->update([
                'status_approval' => $request->action === 'approve' ? 'approved' : 'rejected',
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            // Jika ditolak, rollback saldo
            if ($request->action === 'reject') {
                $siswa = Siswa::findOrFail($transaksi->penerima_id);
                $saldoSiswa = Saldo_keuangan::where('user_id', $siswa->user->id)->first();

                if ($saldoSiswa) {
                    if ($transaksi->jenis_transaksi === 'setoran_tabungan') {
                        // Rollback setoran: kurangi saldo
                        $saldoSiswa->decrement('saldo_akhir', $transaksi->jumlah);
                    } elseif ($transaksi->jenis_transaksi === 'penarikan_tabungan') {
                        // Rollback penarikan: kembalikan saldo
                        $saldoSiswa->increment('saldo_akhir', $transaksi->jumlah);
                    }
                }
            }

            // Log activity
            Keuangan_transaksi_logs::create([
                'transaksi_id'   => $transaksi->id,
                'aksi'           => $request->action,
                'data_lama'      => json_encode(['status_approval' => 'pending']),
                'data_baru'      => json_encode(['status_approval' => $transaksi->status_approval]),
                'dilakukan_oleh' => Auth::id(),
                'dilakukan_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $request->action === 'approve'
                    ? 'Transaksi berhasil disetujui!'
                    : 'Transaksi berhasil ditolak dan saldo telah dikembalikan.'
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

        $transaksis = Siswa::with('tahun_ajaran','user.saldo','kelas','unit')
            ->when($request->filled('unit_id') && $request->unit_id != '', function ($query) use ($request) {
                $query->where('unit_id', $request->unit_id);
            })
            ->when(!$request->filled('unit_id') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                $query->whereHas('unit', function($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            })
            ->when(!$request->filled('unit_id') && Auth::user()->unit_id, function ($query) {
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nisn', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('kelas', function($q) use ($search) {
                          $q->where('nama_kelas', 'like', "%{$search}%");
                      });
                });
            })
            ->where('status','1')
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
            ->where(function($query) {
                $query->where('status_approval','=', 'approve')
                      ->orWhere('status_approval','=', 'approved');
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

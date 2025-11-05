<?php

namespace App\Http\Controllers;

use App\Models\Jurnals;
use App\Models\Kategoritagihan;
use App\Models\Kelas;
use App\Models\Keuangan_transaksi;
use App\Models\Keuangan_transaksi_logs;
use App\Models\setting_akun;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Tagihanitem;
use App\Models\Tagihansiswa;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    public function create()
    {
        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
        if (Auth::user()->yayasan_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $kelas = Kelas::whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
            $kategoriTagihan = Kategoritagihan::whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
            $kelas = Kelas::where('unit_id', Auth::user()->unit_id)->where('status', '1')->get();
            $kategoriTagihan = Kategoritagihan::where('unit_id', Auth::user()->unit_id)->where('status', '1')->get();
        } else {
            $units = Unit::where('status', '1')->get();
            $kelas = Kelas::where('status', '1')->get();
            $kategoriTagihan = Kategoritagihan::where('status', '1')->get();
        }

        return view('pages.tagihan.create', compact('units', 'kelas', 'kategoriTagihan'));
    }
    public function index()
    {
        //        $tagihans = TagihanSiswa::with(['siswa', 'tagihan.unit', 'tagihan.kelas', 'tagihan.items.kategori'])->get();

        $tagihans = Tagihan::with([
            'unit',
            'kelas',
            'items.kategori',
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.siswa.pembayaranTagihan'
        ])
        ->when(Auth::user()->yayasan_id, function ($query) {
            $query->whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        })
        ->when(!Auth::user()->yayasan_id && Auth::user()->unit_id, function ($query, $unit_id) {
            $query->where('unit_id', $unit_id);
        })
        ->get();

        //        dd($tagihans);
        $summary = [
            'jumlah_data' => $tagihans->count(),
            'nominal_tagihan' => $tagihans->sum(function ($t) {
                $total = $t->items->sum('nominal') * ($t->periode ?? 1);
                return $total * $t->tagihanSiswa->count(); // total tagihan semua siswa
            }),
            'sudah_dibayar' => $tagihans->sum(function ($t) {
                return $t->tagihanSiswa->sum(function ($ts) {
                    return $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                });
            }),
            'belum_dibayar' => $tagihans->sum(function ($t) {
                $total_tagihan = $t->items->sum('nominal') * ($t->periode ?? 1);
                return $t->tagihanSiswa->sum(function ($ts) use ($total_tagihan) {
                    $sudah_bayar = $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                    return $total_tagihan - $sudah_bayar;
                });
            }),
        ];
        return view('pages.tagihan.index', compact('tagihans', 'summary'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'kelas' => 'nullable|exists:kelas,id',
            'target' => 'required|in:all,per',
            'periode' => 'nullable|integer',
            'nominal_bebas' => 'nullable|numeric',
            'bulan_mulai' => 'required|integer|min:1|max:12',
            'tahun_mulai' => 'required|integer',
            'siswa.*' => 'nullable|exists:siswas,id',
        ]);

        DB::beginTransaction();

        if ($request->jenis_tagihan == '') {
            $request->jenis_tagihan = 'bebas';
        }

        try {
            // 1. Simpan tagihan utama
            $tagihan = Tagihan::create([
                'unit_id' => $request->unit_id,
                'kelas_id' => $request->kelas ?? null,
                'target' => $request->target,
                'jenis_tagihan' => $request->jenis_tagihan,
                'periode' => $request->jenis_tagihan === 'bulanan' ? $request->periode : null,
                'nominal_bebas' => $request->jenis_tagihan === 'bebas' ? $request->nominal_bebas : null,
                'bulan_mulai' => $request->bulan_mulai,
                'tahun_mulai' => $request->tahun_mulai,
            ]);

            // 2. Simpan item tagihan
            $total_tagihan = 0;
            foreach ($request->items as $item) {
                if (!empty($item['id'])) {
                    $kategori = KategoriTagihan::find($item['id']);
                    $nominal_item = $item['nominal'] ?? $kategori->biaya_tagihan;
                    Tagihanitem::create([
                        'tagihan_id' => $tagihan->id,
                        'kategori_id' => $item['id'],
                        'nominal' => $nominal_item,
                    ]);
                    $total_tagihan += $nominal_item;
                }
            }
            $kategoriIds = collect($request->items)->pluck('id')->filter()->all();

            // Ambil siswa target
            $siswaList = [];
            if ($request->target === 'per' && $request->has('siswa')) {
                $siswaList = Siswa::whereIn('id', $request->siswa)->get();
            } elseif ($request->target === 'all' && $request->kelas) {
                $siswaList = Siswa::where('kelas_id', $request->kelas)->get();
            }

            foreach ($siswaList as $siswa) {
                $sudahAda = Tagihansiswa::where('siswa_id', $siswa->id)
                    ->where('status', '!=', '1') // belum lunas
                    ->whereHas('tagihan.items', function ($q) use ($kategoriIds) {
                        $q->whereIn('kategori_id', $kategoriIds);
                    })
                    ->exists();
                if ($sudahAda) {
                    DB::rollBack();
                    return back()->withInput()->with('danger', "Siswa {$siswa->nama} masih punya tagihan aktif dengan kategori yang sama.");
                }
            }
            // 3. Simpan tagihan_siswa dan jurnal
            $settings = setting_akun::where('kategori', 'tagihan-masuk');

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

            $settings = $settings->where('status','1')->get();

            $akun_debit = $settings->where('debit', 1)->first()?->akun_id; // piutang siswa
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id; // pendapatan sekolah

            if (!$akun_debit || !$akun_kredit) {
                throw new \Exception("Setting akun untuk kategori tagihan-masuk belum lengkap.");
            }

            foreach ($siswaList as $siswa) {
                if ($tagihan->jenis_tagihan === 'bulanan' && $tagihan->periode) {
                    // generate sesuai jumlah bulan
                    for ($i = 1; $i <= $tagihan->periode; $i++) {
                        Tagihansiswa::create([
                            'tagihan_id'    => $tagihan->id,
                            'siswa_id'      => $siswa->id,
                            'bulan_ke'      => $i,
                            'tanggal_bayar' => null,
                            'sisa_nominal'  => $total_tagihan,
                            'status'        => '0'
                        ]);
                    }
                } else {
                    // jenis bebas → cuma 1 row
                    Tagihansiswa::create([
                        'tagihan_id'    => $tagihan->id,
                        'siswa_id'      => $siswa->id,
                        'bulan_ke'      => null,
                        'tanggal_bayar' => null,
                        'sisa_nominal'  => $total_tagihan,
                    ]);
                }

                // Transaksi utama
                $transaksi = Keuangan_transaksi::create([
                    'penerima_id'      => $siswa->id,
                    'penerima_tipe'    => Siswa::class,
                    'jenis_transaksi'  => 'tagihan',
                    'jumlah'           => $total_tagihan,
                    'keterangan'       => "Tagihan ID: {$tagihan->id}",
                    'created_by'       => Auth::id(),
                ]);

                // Jurnal debit
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_debit,
                    'debit'        => $total_tagihan,
                    'kredit'       => 0,
                    'keterangan'   => "Tagihan siswa ID: {$siswa->id}",
                ]);

                // Jurnal kredit
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_kredit,
                    'debit'        => 0,
                    'kredit'       => $total_tagihan,
                    'keterangan'   => "Tagihan siswa ID: {$siswa->id}",
                ]);

                // Log transaksi
                Keuangan_transaksi_logs::create([
                    'transaksi_id'   => $transaksi->id,
                    'aksi'           => 'create_tagihan',
                    'data_lama'      => null,
                    'data_baru'      => json_encode([
                        'tagihan_id' => $tagihan->id,
                        'jumlah'     => $total_tagihan,
                    ]),
                    'dilakukan_oleh' => Auth::id(),
                    'dilakukan_pada' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('tagihan.index')->with('success', 'Tagihan berhasil dibuat dan jurnal dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function show($id, $siswaId)
    {
        $tagihanSiswa = Tagihansiswa::with([
            'siswa.user',
            'tagihan.unit',
            'tagihan.kelas',
            'tagihan.items.kategori',
            'siswa.pembayaranTagihan.tagihanSiswa',
            'siswa.pembayaranTagihan.user', // tambahkan relasi user dari pembayaran
        ])
            ->where('tagihan_id', $id)
            ->where('siswa_id', $siswaId)
            ->orderBy('bulan_ke')
            ->get();

        if ($tagihanSiswa->isEmpty()) {
            return view('pages.tagihan.show', [
                'tagihanSiswa'    => collect(),
                'dataPerbulan'    => collect(),
                'pembayaranSiswa' => collect(),
            ]);
        }

        $firstTagihan   = $tagihanSiswa->first()->tagihan;
        $siswa          = $tagihanSiswa->first()->siswa;

        $nominal        = $firstTagihan->items->sum('nominal');
        $namaKategori   = $firstTagihan->items->pluck('kategori.nama_kategori')->implode(', ');

        $bulanMulai     = (int) $firstTagihan->bulan_mulai;
        $tahunMulai     = (int) $firstTagihan->tahun_mulai;

        $dataPerbulan = $tagihanSiswa->map(function ($ts) use ($bulanMulai, $tahunMulai, $nominal, $namaKategori) {
            $date = Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($ts->bulan_ke - 1);

            return [
                'id'            => $ts->id,
                'tagihan_id'    => $ts->tagihan_id,
                'nama_kategori' => $namaKategori,
                'bulan'         => $date->translatedFormat('F'),
                'tahun'         => $date->year,
                'nominal'       => $nominal,
                'status'        => $ts->status == 1 ? 'Lunas' : 'Belum Lunas',
                'tanggal_bayar' => $ts->tanggal_bayar
            ];
        });

        $pembayaranSiswa = $siswa->pembayaranTagihan->map(function ($p) use ($bulanMulai, $tahunMulai) {
            $ts = $p->tagihanSiswa;
            $bulanKe = $ts?->bulan_ke;

            $date = $bulanKe
                ? Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($bulanKe - 1)
                : null;

            return [
                'id'            => $p->id,
                'tanggal_bayar' => $p->tanggal_bayar,
                'jumlah_bayar'  => $p->jumlah_bayar,
                'bulan_ke'      => $bulanKe,
                'bulan'         => $date ? $date->translatedFormat('F') : null,
                'tahun'         => $date ? $date->year : null,
                'create_by'     => $p->user?->name,  // ambil nama user
            ];
        });

        return view('pages.tagihan.show', compact('tagihanSiswa', 'dataPerbulan', 'pembayaranSiswa'));
    }


    /**
     * Get tagihan perbulan untuk siswa tertentu (semua tagihan)
     */
    public function perbulanAll($siswaId)
    {
        $tagihanSiswa = Tagihansiswa::with([
            'siswa',
            'tagihan.items.kategori',
            'potonganSiswa.potongan'
        ])
            ->where('siswa_id', $siswaId)
            ->whereHas('tagihan', function($query) {
                $query->where('jenis_tagihan', 'bulanan');
            })
            ->orderBy('bulan_ke')
            ->get();

        if ($tagihanSiswa->isEmpty()) {
            return response()->json([
                'belum_lunas' => [],
                'sudah_lunas' => []
            ]);
        }

        // Bagi menjadi dua kelompok
        $belumLunas = [];
        $sudahLunas = [];
        $counter = 1;

        foreach ($tagihanSiswa as $ts) {
            $tagihan = $ts->tagihan;
            $nominal = $tagihan->items->sum('nominal');
            $namaKategori = $tagihan->items->pluck('kategori.nama_kategori')->implode(', ');
            $bulanMulai = (int) $tagihan->bulan_mulai;
            $tahunMulai = (int) $tagihan->tahun_mulai;

            $date = \Carbon\Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($ts->bulan_ke - 1);

            // Total potongan untuk tagihan ini
            $totalPotongan = $ts->potonganSiswa->sum('nominal');

            $jumlahTagihan = $nominal - $totalPotongan;
            $jumlahDibayar = $ts->sisa_nominal;

            $row = [
                'no'                => $counter++,
                'id'                => $ts->id,
                'periode'           => $date->translatedFormat('F'),
                'tahun'             => $date->year,
                'tagihan_kelas'     => $namaKategori,
                'rincian_tagihan'   => (int) $nominal,
                'jumlah_potongan'   => (int) $totalPotongan,
                'jumlah_tagihan'    => (int) $jumlahTagihan,
                'jumlah_dibayar'    => (int) $jumlahDibayar,
                'nominal_pembayaran'=> (int) ($jumlahTagihan - $jumlahDibayar),
                'catatan'           => $ts->catatan ?? '',
                'status'            => $ts->status,
                'kategori_id'       => $tagihan->items->first()->kategori_id ?? 1,
            ];

            if ($ts->status == 1) {
                $sudahLunas[] = $row;
            } else {
                $belumLunas[] = $row;
            }
        }

        return response()->json([
            'belum_lunas' => $belumLunas,
            'sudah_lunas' => $sudahLunas
        ]);
    }

    public function perbulan($siswaId, $tagihanId)
    {
        $tagihanSiswa = Tagihansiswa::with([
            'siswa',
            'tagihan.items.kategori',
            'potonganSiswa.potongan'
        ])
            ->where('tagihan_id', $tagihanId)
            ->where('siswa_id', $siswaId)
            ->orderBy('bulan_ke')
            ->get();

        if ($tagihanSiswa->isEmpty()) {
            return response()->json([
                'belum_lunas' => [],
                'sudah_lunas' => []
            ]);
        }

        $firstTagihan = $tagihanSiswa->first()->tagihan;
        $nominal = $firstTagihan->items->sum('nominal');
        $namaKategori = $firstTagihan->items->pluck('kategori.nama_kategori')->implode(', ');
        $bulanMulai = (int) $firstTagihan->bulan_mulai;
        $tahunMulai = (int) $firstTagihan->tahun_mulai;

        // Total potongan semua bulan
        $totalPotonganSemuaBulan = $tagihanSiswa->flatMap(function ($ts) {
            return $ts->potonganSiswa;
        })->sum('nominal');

        // Bagi menjadi dua kelompok
        $belumLunas = [];
        $sudahLunas = [];

        foreach ($tagihanSiswa as $index => $ts) {
            $date = \Carbon\Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($ts->bulan_ke - 1);

            $jumlahTagihan = $nominal - $totalPotonganSemuaBulan;
            $jumlahSudahDibayar = $ts->siswa->pembayaranTagihan->where('tagihan_siswa_id', $ts->id)->sum('jumlah_bayar');
            $jumlahDibayar = $ts->sisa_nominal;
            $jumlahTunggakan = $ts->sisa_nominal;

            $row = [
                'no'                => $index + 1,
                'id'                => $ts->id,
                'periode'           => $date->translatedFormat('F Y'),
                'tagihan_kelas'     => $namaKategori,
                'rincian_tagihan'   => (int) $nominal,
                'jumlah_potongan'   => (int) $totalPotonganSemuaBulan,
                'jumlah_tagihan'    => (int) $jumlahTagihan,
                'jumlah_dibayar'    => (int) $jumlahDibayar,
                'jumlah_tunggakan'  => (int) $jumlahTunggakan,
                'nominal_pembayaran' => (int) $jumlahSudahDibayar,
                'catatan'           => $ts->catatan ?? '',
                'status'            => $ts->status,
            ];

            if ($ts->status == 1) {
                $sudahLunas[] = $row;
            } else {
                $belumLunas[] = $row;
            }
        }

        return response()->json([
            'belum_lunas' => $belumLunas,
            'sudah_lunas' => $sudahLunas
        ]);
    }




    public function daftarTagihan($siswaId)
    {
        $tagihanSiswa = Tagihansiswa::with('tagihan.items.kategori')
            ->where('siswa_id', $siswaId)
            ->whereHas('tagihan', function ($query) {
                $query->where('jenis_tagihan', 'bulanan');
            })
            ->get()
            ->groupBy('tagihan_id'); // distinct by tagihan

        if ($tagihanSiswa->isEmpty()) {
            return response()->json([]);
        }

        $data = $tagihanSiswa->map(function ($rows) {
            $first = $rows->first();

            return [
                'id'            => $first->tagihan->id,
                'jenis_tagihan' => $first->tagihan->jenis_tagihan,
                'nominal'       => $first->tagihan->items->sum('nominal'),
                'kategori'      => $first->tagihan->items->map(function ($item) {
                    return [
                        'id'            => $item->kategori->id,
                        'nama_kategori' => $item->kategori->nama_kategori ?? '-',
                        'kode_kategori' => $item->kategori->kode_kategori ?? '-',
                        'nominal'       => $item->nominal,
                    ];
                }),
                'jumlah_bulan'  => $rows->count(), // total bulan dari periode
                'sudah_lunas'   => $rows->where('status', 'lunas')->count(),
                'belum_lunas'   => $rows->where('status', 'belum')->count(),
            ];
        })->values(); // reset index biar array rapi

        return response()->json([
            'detail'        => $data
        ]);
    }

    public function daftarTagihanBebas($siswaId)
    {
        $tagihanSiswa = Tagihansiswa::with('tagihan.items.kategori')
            ->where('siswa_id', $siswaId)
            ->whereHas('tagihan', function ($query) {
                $query->where('jenis_tagihan', 'bebas')
                    ->where('status_tagihan', 0);
            })
            ->get()
            ->groupBy('tagihan_id'); // distinct by tagihan

        if ($tagihanSiswa->isEmpty()) {
            return response()->json([]);
        }

        $data = $tagihanSiswa->map(function ($rows) {
            $first = $rows->first();

            return [
                'id'            => $first->tagihan->id,
                'jenis_tagihan' => $first->tagihan->jenis_tagihan,
                'nominal'       => $first->tagihan->items->sum('nominal'),
                'kategori'      => $first->tagihan->items->map(function ($item) {
                    return [
                        'id'            => $item->kategori->id,
                        'nama_kategori' => $item->kategori->nama_kategori ?? '-',
                        'kode_kategori' => $item->kategori->kode_kategori ?? '-',
                        'nominal'       => $item->nominal,
                    ];
                }),
                'jumlah_bulan'  => $rows->count(), // total bulan dari periode
                'sudah_lunas'   => $rows->where('status', 'lunas')->count(),
                'belum_lunas'   => $rows->where('status', 'belum')->count(),
            ];
        })->values(); // reset index biar array rapi

        return response()->json([
            'detail'        => $data
        ]);
    }
    public function tagihanBebas($siswaId)
    {
        $data = Tagihansiswa::with('tagihan.items.kategori')
            ->where('siswa_id', $siswaId)
            ->whereHas('tagihan', function ($q) {
                $q->where('jenis_tagihan', 'bebas');
            })
            ->get()
            ->map(function ($ts) {
                return [
                    'id' => $ts->id,
                    'nama_kategori' => optional($ts->tagihan->items->first()->kategori)->nama_kategori,
                    'nominal' => $ts->tagihan->items->sum('nominal'),
                    'status' => $ts->status,
                ];
            });

        return response()->json($data);
    }
    public function simpanCatatan(Request $request)
    {
        $request->validate([
            'tagihan_id' => 'required|exists:tagihan_siswa,id',
            'catatan' => 'required|string|max:1000',
        ]);

        try {
            $tagihan = \App\Models\Tagihansiswa::findOrFail($request->tagihan_id);
            $tagihan->catatan = $request->catatan;
            $tagihan->save();

            return response()->json(['status' => 1, 'message' => 'Catatan berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Gagal menyimpan catatan: ' . $e->getMessage()]);
        }
    }
}

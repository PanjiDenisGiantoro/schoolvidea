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
use App\Models\DataRekening;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class TagihanController extends Controller
{
    public function create()
    {
        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan semua unit dalam yayasan
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $kelas = Kelas::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
            $kategoriTagihan = Kategoritagihan::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
            $datarekening = DataRekening::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id (tapi tidak punya yayasan_id), tampilkan hanya data dari unit tersebut
            $units = Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
            $kelas = Kelas::where('unit_id', Auth::user()->unit_id)->where('status', '1')->get();
            $kategoriTagihan = Kategoritagihan::where('unit_id', Auth::user()->unit_id)->where('status', '1')->get();
            $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)->where('status', '1')->get();
        } else {
            // Super admin - tampilkan semua
            $units = Unit::where('status', '1')->get();
            $kelas = Kelas::where('status', '1')->get();
            $kategoriTagihan = Kategoritagihan::where('status', '1')->get();
            $datarekening = DataRekening::where('status', '1')->get();
        }

        return view('pages.tagihan.create', compact('units', 'kelas', 'kategoriTagihan', 'datarekening'));
    }
    public function index(Request $request)
    {
        //        $tagihans = TagihanSiswa::with(['siswa', 'tagihan.unit', 'tagihan.kelas', 'tagihan.items.kategori'])->get();

        $perPage = $request->get('per_page', 15);

        $tagihans = Tagihan::with([
            'unit',
            'kelas',
            'items.kategori',
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.siswa.pembayaranTagihan',
            'tagihanSiswa.potonganSiswa'
        ])
            ->when($request->filled('unit_id') && $request->unit_id != '', function ($query) use ($request) {
                $query->where('unit_id', $request->unit_id);
            })
//            ->when(!$request->filled('unit_id') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
//                // Jika user punya yayasan_id, filter tagihan dari semua unit dalam yayasan
//                $query->whereHas('unit', function ($q) {
//                    $q->where('yayasan_id', Auth::user()->yayasan_id);
//                });
//            })
            ->when(!$request->filled('unit_id') && Auth::user()->unit_id, function ($query) {
                // Jika user punya unit_id (tapi tidak punya yayasan_id), filter tagihan dari unit tersebut
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->sampai_tanggal);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama_tagihan', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%")
                      ->orWhereHas('kelas', function ($q) use ($search) {
                          $q->where('nama_kelas', 'like', "%{$search}%");
                      });
                });
            })
            ->paginate($perPage)
            ->appends($request->except('page'));

        //        dd($tagihans);
        $summary = [
            'jumlah_data' => $tagihans->count(),
            'nominal_tagihan' => $tagihans->sum(function ($t) {
                $total = $t->items->sum('nominal');
                return $total * $t->tagihanSiswa->count(); // total tagihan semua siswa
            }),
            'sudah_dibayar' => $tagihans->sum(function ($t) {
                return $t->tagihanSiswa->sum(function ($ts) {
                    return $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                });
            }),
            'belum_dibayar' => $tagihans->sum(function ($t) {
                $total_tagihan = $t->items->sum('nominal') ;
                return $t->tagihanSiswa->sum(function ($ts) use ($total_tagihan) {
                    $sudah_bayar = $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                    return $total_tagihan - $sudah_bayar;
                });
            }),
        ];

        // Get units for filter
        if (Auth::user()->yayasan_id && !Auth::user()->unit_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->orderBy('nama_unit')->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
        } else {
            $units = Unit::where('status', '1')->orderBy('nama_unit')->get();
        }

        return view('pages.tagihan.index', compact('tagihans', 'summary', 'units'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
//            'kelas' => 'nullable|exists:kelas,id',
            'target' => 'required|in:all,per',
            'periode' => 'nullable|integer',
            'nominal_bebas' => 'nullable|numeric',
            'bulan_mulai' => 'required|integer|min:1|max:12',
            'tahun_mulai' => 'required|integer',
//            'siswa.*' => 'nullable|exists:siswas,id',
        ]);

//        DB::beginTransaction();

        if ($request->jenis_tagihan == '') {
            $request->jenis_tagihan = 'bebas';
        }

        try {
            // 1. Kumpulkan data items terlebih dahulu
            $itemsData = [];
            foreach ($request->items as $item) {
                if (!empty($item['id'])) {
                    $kategori = KategoriTagihan::find($item['id']);
                    $nominal_item = $item['nominal'] ?? $kategori->biaya_tagihan;
                    $itemsData[] = [
                        'kategori_id' => $item['id'],
                        'nominal' => $nominal_item,
                        'kategori' => $kategori,
                    ];
                }
            }

            if (empty($itemsData)) {
                throw new \Exception("Tidak ada item tagihan yang valid.");
            }

            $kategoriIds = collect($itemsData)->pluck('kategori_id')->filter()->all();

            // Ambil siswa target
            $siswaList = [];
            if ($request->target === 'per' && $request->has('siswa')) {
                $siswaList = Siswa::whereIn('id', $request->siswa)->get();
            } elseif ($request->target === 'all' && $request->kelas) {
                $siswaList = Siswa::where('kelas_id', $request->kelas)->get();
            }

            // Cek apakah siswa sudah punya tagihan aktif untuk kategori yang sama
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

            // 2. Ambil setting akun untuk jurnal
            $settings = setting_akun::where('kategori', 'tagihan-masuk');

            // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
            if (Auth::user()->yayasan_id) {
                $settings->whereHas('unit', function($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            } elseif (Auth::user()->unit_id) {
                $settings->where('unit_id', Auth::user()->unit_id);
            } elseif ($request->filled('unit_id')) {
                $settings->where('unit_id', $request->unit_id);
            }

            $settings = $settings->where('status','1')->get();
            $akun_debit = $settings->where('debit', 1)->first()?->akun_id; // piutang siswa
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id; // pendapatan sekolah

            if (!$akun_debit || !$akun_kredit) {
                throw new \Exception("Setting akun untuk kategori tagihan-masuk belum lengkap.");
            }

            // 3. LOOP SETIAP ITEM - BUAT TAGIHAN TERPISAH
            foreach ($itemsData as $itemData) {
                // Buat tagihan baru untuk SETIAP item
                $tagihan = Tagihan::create([
                    'unit_id' => $request->unit_id,
                    'kelas_id' => $request->kelas ?? null,
                    'target' => $request->target,
                    'jenis_tagihan' => $request->jenis_tagihan,
                    'periode' => $request->jenis_tagihan === 'bulanan' ? $request->periode : null,
                    'nominal_bebas' => $request->jenis_tagihan === 'bebas' ? $itemData['nominal'] : null,
                    'bulan_mulai' => $request->bulan_mulai,
                    'tahun_mulai' => $request->tahun_mulai,
//                    'rekening_id' => $request->rekening_id,
                ]);

                // Buat tagihan item untuk tagihan ini
                $tagihanItem = Tagihanitem::create([
                    'tagihan_id' => $tagihan->id,
                    'kategori_id' => $itemData['kategori_id'],
                    'nominal' => $itemData['nominal'],
                ]);

                // Loop setiap siswa untuk item ini
                foreach ($siswaList as $siswa) {
                    if ($tagihan->jenis_tagihan === 'bulanan' && $tagihan->periode) {
                        // generate sesuai jumlah bulan untuk item ini
                        for ($i = 1; $i <= $tagihan->periode; $i++) {
                            Tagihansiswa::create([
                                'tagihan_id'     => $tagihan->id,
                                'tagihanitem_id' => $tagihanItem->id,
                                'siswa_id'       => $siswa->id,
                                'bulan_ke'       => $i,
                                'tanggal_bayar'  => null,
                                'sisa_nominal'   => $itemData['nominal'],
                                'status'         => '0'
                            ]);
                        }
                    } else {
                        // jenis bebas → 1 row per siswa per item
                        Tagihansiswa::create([
                            'tagihan_id'     => $tagihan->id,
                            'tagihanitem_id' => $tagihanItem->id,
                            'siswa_id'       => $siswa->id,
                            'bulan_ke'       => null,
                            'tanggal_bayar'  => null,
                            'sisa_nominal'   => $itemData['nominal'],
                        ]);
                    }

                    // Transaksi per siswa per item
                    $transaksi = Keuangan_transaksi::create([
                        'penerima_id'      => $siswa->id,
                        'penerima_tipe'    => Siswa::class,
                        'jenis_transaksi'  => 'tagihan',
                        'jumlah'           => $itemData['nominal'],
                        'keterangan'       => "Tagihan {$itemData['kategori']->nama_kategori} - ID: {$tagihan->id}",
                        'created_by'       => Auth::id(),
                    ]);

                    // Jurnal debit
                    Jurnals::create([
                        'transaksi_id' => $transaksi->id,
                        'akun_id'      => $akun_debit,
                        'debit'        => $itemData['nominal'],
                        'kredit'       => 0,
                        'keterangan'   => "Tagihan siswa ID: {$siswa->id} - {$itemData['kategori']->nama_kategori}",
                    ]);

                    // Jurnal kredit
                    Jurnals::create([
                        'transaksi_id' => $transaksi->id,
                        'akun_id'      => $akun_kredit,
                        'debit'        => 0,
                        'kredit'       => $itemData['nominal'],
                        'keterangan'   => "Tagihan siswa ID: {$siswa->id} - {$itemData['kategori']->nama_kategori}",
                    ]);

                    // Log transaksi
                    Keuangan_transaksi_logs::create([
                        'transaksi_id'   => $transaksi->id,
                        'aksi'           => 'create_tagihan',
                        'data_lama'      => null,
                        'data_baru'      => json_encode([
                            'tagihan_id' => $tagihan->id,
                            'item_id'    => $tagihanItem->id,
                            'kategori'   => $itemData['kategori']->nama_kategori,
                            'jumlah'     => $itemData['nominal'],
                        ]),
                        'dilakukan_oleh' => Auth::id(),
                        'dilakukan_pada' => now(),
                    ]);
                }
            }

//            DB::commit();
            return redirect()->route('tagihan.index')->with('success', 'Tagihan berhasil dibuat dan jurnal dicatat.');
        } catch (\Exception $e) {
//            DB::rollBack();
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
            'potonganSiswa.potongan', // potongan untuk tagihan
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
        $kodeKategori   = $firstTagihan->items->pluck('kategori.kode_kategori')->implode(', ');

        $bulanMulai     = (int) $firstTagihan->bulan_mulai;
        $tahunMulai     = (int) $firstTagihan->tahun_mulai;

        $dataPerbulan = $tagihanSiswa->map(function ($ts) use ($bulanMulai, $tahunMulai, $nominal, $namaKategori, $kodeKategori, $id) {
            $date = Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($ts->bulan_ke - 1);

            // Total potongan untuk tagihan ini
            $totalPotongan = $ts->potonganSiswa->sum('nominal');

            // Nominal setelah potongan
            $nominalSetelahPotongan = $nominal - $totalPotongan;

            return [
                'id'            => $ts->id,
                'tagihan_id'    => $ts->tagihan_id,
                'kode_kategori' => $kodeKategori,
                'nama_kategori' => $namaKategori,
                'kode_tagihan'  => 'TAG-' . str_pad($id, 5, '0', STR_PAD_LEFT),
                'bulan'         => $date->translatedFormat('F'),
                'tahun'         => $date->year,
                'nominal'       => $nominal,
                'potongan'      => $totalPotongan,
                'nominal_akhir' => $nominalSetelahPotongan,
                'status'        => $ts->status == 1 ? 'Lunas' : 'Belum Lunas',
                'tanggal_bayar' => $ts->tanggal_bayar
            ];
        });

        // Filter pembayaran hanya untuk tagihan_id yang sedang ditampilkan
        $pembayaranSiswa = $siswa->pembayaranTagihan
            ->filter(function ($p) use ($id) {
                return $p->tagihanSiswa && $p->tagihanSiswa->tagihan_id == $id;
            })
            ->map(function ($p) use ($bulanMulai, $tahunMulai, $kodeKategori, $id) {
                $ts = $p->tagihanSiswa;
                $bulanKe = $ts?->bulan_ke;

                $date = $bulanKe
                    ? Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($bulanKe - 1)
                    : null;

                return [
                    'id'                => $p->id,
                    'kode_kategori'     => $kodeKategori,
                    'kode_tagihan'      => 'TAG-' . str_pad($id, 5, '0', STR_PAD_LEFT),
                    'potongan'          => $ts ? $ts->potonganSiswa->sum('nominal') : 0,
                    'tanggal_bayar'     => $p->tanggal_bayar,
                    'waktu_transaksi'   => $p->created_at,
                    'jumlah_bayar'      => $p->jumlah_bayar,
                    'bulan_ke'          => $bulanKe,
                    'bulan'             => $date ? $date->translatedFormat('F') : null,
                    'tahun'             => $date ? $date->year : null,
                    'metode_bayar'      => $p->metode_bayar ?? 'cash',
                    'status_approval'   => $p->status_approval ?? 'pending',
                    'create_by'         => $p->user?->name,  // ambil nama user
                ];
            });

        return view('pages.tagihan.show', compact('tagihanSiswa', 'dataPerbulan', 'pembayaranSiswa'));
    }


    /**
     * Get tagihan perbulan untuk siswa tertentu (semua tagihan)
     * SETIAP ITEM TAGIHAN DITAMPILKAN TERPISAH (TIDAK DI-MERGE)
     */
    public function perbulanAll($siswaId)
    {
        $tagihanSiswa = Tagihansiswa::with([
            'siswa',
            'tagihan',
            'tagihanItem.kategori', // Load item spesifik via relasi
            'potonganSiswa.potongan'
        ])
            ->where('siswa_id', $siswaId)
            ->whereHas('tagihan', function ($query) {
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
            $tagihanItem = $ts->tagihanItem; // Item spesifik untuk record ini

            // Ambil nominal dari item spesifik, bukan sum semua item
            $nominal = $tagihanItem ? $tagihanItem->nominal : 0;
            $namaKategori = $tagihanItem && $tagihanItem->kategori
                ? $tagihanItem->kategori->nama_kategori
                : '-';

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
                'tagihan_kelas'     => $namaKategori, // Nama kategori spesifik
                'rincian_tagihan'   => (int) $nominal, // Nominal item spesifik
                'jumlah_potongan'   => (int) $totalPotongan,
                'jumlah_tagihan'    => (int) $jumlahTagihan,
                'jumlah_dibayar'    => (int) $jumlahDibayar,
                'nominal_pembayaran' => (int) ($jumlahTagihan - $jumlahDibayar),
                'catatan'           => $ts->catatan ?? '',
                'status'            => $ts->status,
                'kategori_id'       => $tagihanItem ? $tagihanItem->kategori_id : null,
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
            'tagihan',
            'tagihanItem.kategori', // Load item spesifik
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
        $bulanMulai = (int) $firstTagihan->bulan_mulai;
        $tahunMulai = (int) $firstTagihan->tahun_mulai;

        // Bagi menjadi dua kelompok
        $belumLunas = [];
        $sudahLunas = [];

        foreach ($tagihanSiswa as $index => $ts) {
            $tagihanItem = $ts->tagihanItem;

            // Ambil nominal dari item spesifik
            $nominal = $tagihanItem ? $tagihanItem->nominal : 0;
            $namaKategori = $tagihanItem && $tagihanItem->kategori
                ? $tagihanItem->kategori->nama_kategori
                : '-';

            $date = \Carbon\Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($ts->bulan_ke - 1);

            // Total potongan untuk tagihan siswa ini
            $totalPotongan = $ts->potonganSiswa->sum('nominal');

            $jumlahTagihan = $nominal - $totalPotongan;
            $jumlahDibayar = $ts->sisa_nominal;
            $jumlahTunggakan = $ts->sisa_nominal;

            $row = [
                'no'                => $index + 1,
                'id'                => $ts->id,
                'periode'           => $date->translatedFormat('F Y'),
                'tagihan_kelas'     => $namaKategori, // Nama kategori spesifik
                'rincian_tagihan'   => (int) $nominal, // Nominal item spesifik
                'jumlah_potongan'   => (int) $totalPotongan,
                'jumlah_tagihan'    => (int) $jumlahTagihan,
                'jumlah_dibayar'    => (int) $jumlahDibayar,
                'jumlah_tunggakan'  => (int) $jumlahTunggakan,
                'nominal_pembayaran' => (int) $jumlahTagihan,
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

    /**
     * Print laporan tagihan menggunakan mPDF
     */
    public function printLaporan(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $tagihans = Tagihan::with([
            'unit',
            'kelas',
            'items.kategori',
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.siswa.pembayaranTagihan',
            'tagihanSiswa.potonganSiswa'
        ])
            ->when($request->filled('unit_id') && $request->unit_id != '', function ($query) use ($request) {
                $query->where('unit_id', $request->unit_id);
            })
            ->when(!$request->filled('unit_id') && Auth::user()->yayasan_id && !Auth::user()->unit_id, function ($query) {
                // Jika user punya yayasan_id, filter tagihan dari semua unit dalam yayasan
                $query->whereHas('unit', function ($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            })
            ->when(!$request->filled('unit_id') && Auth::user()->unit_id, function ($query) {
                // Jika user punya unit_id (tapi tidak punya yayasan_id), filter tagihan dari unit tersebut
                $query->where('unit_id', Auth::user()->unit_id);
            })
            ->when($request->filled('dari_tanggal'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->dari_tanggal);
            })
            ->when($request->filled('sampai_tanggal'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->sampai_tanggal);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama_tagihan', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%")
                      ->orWhereHas('kelas', function ($q) use ($search) {
                          $q->where('nama_kelas', 'like', "%{$search}%");
                      });
                });
            })
            ->get();

        $summary = [
            'jumlah_data' => $tagihans->count(),
            'nominal_tagihan' => $tagihans->sum(function ($t) {
                $total = $t->items->sum('nominal');
                return $total * $t->tagihanSiswa->count(); // total tagihan semua siswa
            }),
            'sudah_dibayar' => $tagihans->sum(function ($t) {
                return $t->tagihanSiswa->sum(function ($ts) {
                    return $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                });
            }),
            'belum_dibayar' => $tagihans->sum(function ($t) {
                $total_tagihan = $t->items->sum('nominal') ;
                return $t->tagihanSiswa->sum(function ($ts) use ($total_tagihan) {
                    $sudah_bayar = $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                    return $total_tagihan - $sudah_bayar;
                });
            }),
        ];

        $dari_tanggal = $request->dari_tanggal ?? '';
        $sampai_tanggal = $request->sampai_tanggal ?? '';

        // Generate HTML dari view
        $html = view('pages.tagihan.pdf_laporan', compact(
            'tagihans',
            'summary',
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

        $mpdf->SetTitle('Laporan Kelola Tagihan');
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Laporan-Tagihan-' . date('Ymd') . '.pdf', 'I');
    }
}

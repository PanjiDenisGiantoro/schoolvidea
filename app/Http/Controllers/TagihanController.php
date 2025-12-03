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
    /**
     * Helper function to parse date from DD/MM/YYYY format to YYYY-MM-DD
     */
    private function parseDateFormat($date)
    {
        if (!$date) {
            return null;
        }

        try {
            if (strpos($date, '/') !== false) {
                $parts = explode('/', $date);
                if (count($parts) === 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
                    $day = (int)$parts[0];
                    $month = (int)$parts[1];
                    $year = (int)$parts[2];
                    if ($day > 0 && $day <= 31 && $month > 0 && $month <= 12 && $year > 1900) {
                        return sprintf('%04d-%02d-%02d', $year, $month, $day);
                    }
                }
            } else {
                return $date;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper function to apply base filters (unit_id, yayasan_id)
     */
    private function applyBaseFilters($query, $request)
    {
        // Priority: request unit_id > user unit_id > yayasan_id > super admin (all)
        if ($request->filled('unit_id') && $request->unit_id != '' && $request->unit_id != 'all') {
            $query->whereHas('tagihan', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        } elseif (Auth::user()->unit_id) {
            $query->whereHas('tagihan', function ($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        } elseif (Auth::user()->yayasan_id) {
            $query->whereHas('tagihan.unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        }
        // Else: super admin - show all

        return $query;
    }

    /**
     * Helper function to apply common filters
     */
    private function applyCommonFilters($query, $request)
    {
        // Filter Search - Nama siswa atau NISN
        if ($request->filled('nama_siswa')) {
            $search = $request->nama_siswa;
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa.user', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })->orWhereHas('siswa', function ($sq) use ($search) {
                    $sq->where('nisn', 'like', "%{$search}%");
                });
            });
        }

        // Filter Search - Nama tagihan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('tagihan', function ($sq) use ($search) {
                    $sq->where('nama_tagihan', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%");
                })
                    ->orWhereHas('siswa.user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('siswa', function ($sq) use ($search) {
                        $sq->where('nisn', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tagihan.kelas', function ($sq) use ($search) {
                        $sq->where('nama_kelas', 'like', "%{$search}%");
                    });
            });
        }

        // Filter Kelas
        if ($request->filled('kelas_id')) {
            $query->whereHas('tagihan', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Filter Jenis Tagihan
        if ($request->filled('jenis_tagihan')) {
            $query->whereHas('tagihan', function ($q) use ($request) {
                $q->where('jenis_tagihan', $request->jenis_tagihan);
            });
        }

        // Filter Status Tagihan
        if ($request->filled('status_tagihan')) {
            $status = $request->status_tagihan;
            if ($status === 'lunas' || $status === '1') {
                $query->where('status', '1');
            } elseif ($status === 'belum_lunas' || $status === '0') {
                $query->where('status', '0');
            } elseif ($status === 'cicilan' || $status === '2') {
                $query->where('status', '2');
            }
        }

        // Filter Dari Tanggal
        if ($request->filled('dari_tanggal')) {
            $parsedDate = $this->parseDateFormat($request->dari_tanggal);
            if ($parsedDate) {
                $query->whereHas('tagihan', function ($q) use ($parsedDate) {
                    $q->whereDate('created_at', '>=', $parsedDate);
                });
            }
        }

        // Filter Sampai Tanggal
        if ($request->filled('sampai_tanggal')) {
            $parsedDate = $this->parseDateFormat($request->sampai_tanggal);
            if ($parsedDate) {
                $query->whereHas('tagihan', function ($q) use ($parsedDate) {
                    $q->whereDate('created_at', '<=', $parsedDate);
                });
            }
        }

        return $query;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        // Query distinct per siswa-tagihan (tidak per bulan_ke)
        $subquery = DB::table('tagihan_siswa')
            ->selectRaw('MIN(id) as id')
            ->groupBy('tagihan_id', 'siswa_id');

        $tagihans = Tagihansiswa::with([
            'siswa.user',
            'tagihan.unit',
            'tagihan.kelas',
            'tagihan.items.kategori',
            'siswa.pembayaranTagihan',
            'potonganSiswa'
        ])
            ->joinSub($subquery, 'grouped', function ($join) {
                $join->on('tagihan_siswa.id', '=', 'grouped.id');
            });

        // Apply base filters (unit_id, yayasan_id)
        $tagihans = $this->applyBaseFilters($tagihans, $request);

        // Apply common filters
        $tagihans = $this->applyCommonFilters($tagihans, $request);

        $tagihans = $tagihans
            ->orderBy('tagihan_siswa.created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->except('page'));

        // Hitung summary dari Tagihansiswa yang sudah di-group (dengan filter yang sama)
        $subqueryAll = DB::table('tagihan_siswa')
            ->selectRaw('MIN(id) as id')
            ->groupBy('tagihan_id', 'siswa_id');

        $allTagihansQuery = Tagihansiswa::with([
            'siswa.pembayaranTagihan',
            'tagihan.items'
        ])
            ->joinSub($subqueryAll, 'grouped', function ($join) {
                $join->on('tagihan_siswa.id', '=', 'grouped.id');
            });

        // Apply same base filters for summary
        $allTagihansQuery = $this->applyBaseFilters($allTagihansQuery, $request);
        $allTagihansQuery = $this->applyCommonFilters($allTagihansQuery, $request);

        $allTagihans = $allTagihansQuery->get();

        $summary = [
            'jumlah_data' => $allTagihans->count(),
            'nominal_tagihan' => $allTagihans->sum(function ($ts) {
                return $ts->tagihan->items->sum('nominal');
            }),
            'sudah_dibayar' => $allTagihans->sum(function ($ts) {
                return $ts->siswa->pembayaranTagihan
                    ->where('status_approval', 'approved')
                    ->sum('jumlah_bayar');
            }),
            'belum_dibayar' => Tagihansiswa::whereIn('id', $allTagihans->pluck('id'))
                ->sum('sisa_nominal'),

        ];

        // Get units for filter
        if (Auth::user()->yayasan_id && !Auth::user()->unit_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->orderBy('nama_unit')->get();
            $kelas = Kelas::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->where('status', '1')->orderBy('nama_kelas')->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
            $kelas = Kelas::where('unit_id', Auth::user()->unit_id)->where('status', '1')->orderBy('nama_kelas')->get();
        } else {
            $units = Unit::where('status', '1')->orderBy('nama_unit')->get();
            $kelas = Kelas::where('status', '1')->orderBy('nama_kelas')->get();
        }

        return view('pages.tagihan.index', compact('tagihans', 'summary', 'units', 'kelas'));
    }

    /**
     * Get data untuk DataTable (AJAX endpoint)
     */
    public function datatable(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        // Base query
        $subquery = DB::table('tagihan_siswa')
            ->selectRaw('MIN(id) as id')
            ->groupBy('tagihan_id', 'siswa_id');

        $query = Tagihansiswa::with([
            'siswa.user',
            'tagihan.unit',
            'tagihan.kelas',
            'tagihan.items.kategori',
            'siswa.pembayaranTagihan'
        ])
            ->joinSub($subquery, 'grouped', function ($join) {
                $join->on('tagihan_siswa.id', '=', 'grouped.id');
            });

        // Apply unit filter untuk auth
        if (Auth::user()->unit_id) {
            $query->whereHas('tagihan', function ($q) {
                $q->where('unit_id', Auth::user()->unit_id);
            });
        }

        // Get all results
        $allResults = $query->get();

        // Reset index dan convert ke array
        $filtered = collect($allResults)->values();

        // Total count setelah filter
        $totalFiltered = $filtered->count();

        // Paginate hasil
        $paginatedResults = $filtered->slice($start, $length)->values();

        // Sort by tagihan ID DESC
        $paginatedResults = $paginatedResults->sortByDesc(function ($item) {
            return $item->tagihan->id;
        })->values();

        // Data untuk display
        $data = $paginatedResults->map(function ($tagihanSiswa, $index) use ($start) {
            $siswa = $tagihanSiswa->siswa;
            $tagihan = $tagihanSiswa->tagihan;

            // 1. JUMLAH TAGIHAN (berapa kali)
            // Count total record tagihan_siswa untuk siswa dan tagihan ini
            $jml_tagihan_kali = Tagihansiswa::where('tagihan_id', $tagihan->id)
                ->where('siswa_id', $siswa->id)
                ->count();

            // 2. JUMLAH DIBAYAR (berapa bulan/record sudah lunas)
            // Count jumlah record dengan status = 1 (lunas)
            $jml_dibayar_bulan = Tagihansiswa::where('tagihan_id', $tagihan->id)
                ->where('siswa_id', $siswa->id)
                ->where('status', '1')
                ->count();

            // 3. JUMLAH TUNGGAKAN
            // Sum sisa_nominal dari record dengan status = 0
            $tunggakan_status_0 = Tagihansiswa::where('tagihan_id', $tagihan->id)
                ->where('siswa_id', $siswa->id)
                ->where('status', '0')
                ->sum('sisa_nominal');

            // Jika ada status = 2, ambil dari keuangan_transaksi
            $tunggakan_status_2 = 0;
            $tagihans_status_2 = Tagihansiswa::where('tagihan_id', $tagihan->id)
                ->where('siswa_id', $siswa->id)
                ->where('status', '2')
                ->get();

            foreach ($tagihans_status_2 as $ts2) {
                // Cari di keuangan_transaksi berdasarkan referensi_tagihan_id
                $keuangan = Keuangan_transaksi::where('penerima_id', $siswa->id)
                    ->where('penerima_tipe', Siswa::class)
                    ->where('jenis_transaksi', 'PEMBAYARAN')
                    ->first();

                if ($keuangan) {
                    $tunggakan_status_2 += $keuangan->jumlah;
                }
            }

            $total_tunggakan = $tunggakan_status_0 + $tunggakan_status_2;

            $nama_kategori = $tagihan->items->pluck('kategori.nama_kategori')->filter()->implode(', ') ?? '-';

            // Status berdasarkan perbandingan jml_tagihan vs jml_dibayar
            $status_badge = ($jml_tagihan_kali == $jml_dibayar_bulan)
                ? '<span class="badge bg-success rounded-pill">Lunas</span>'
                : '<span class="badge bg-warning text-dark rounded-pill">Belum Lunas</span>';

            $detail_btn = $siswa
                ? '<a href="' . route('tagihan.show', [$tagihan->id, $siswa->id]) . '" class="btn btn-sm btn-primary rounded-pill"><i class="ri-eye-line"></i></a>'
                : '-';

            // Format created_at
            $created_at = $tagihan->created_at ? $tagihan->created_at->format('d/m/Y H:i') : '-';

            return [
                'no' => $start + $index + 1,
                'nisn' => $siswa?->nisn ?? '-',
                'nama_siswa' => $siswa?->user->name ?? '-',
                'unit' => $tagihan->unit->nama_unit ?? '-',
                'kelas' => $tagihan->kelas->nama_kelas ?? '-',
                'nama_tagihan' => $nama_kategori,
                'jml_tagihan' => $jml_tagihan_kali . 'x',
                'jml_dibayar' => $jml_dibayar_bulan . ' bulan',
                'jml_tunggakan' => 'Rp ' . number_format($total_tunggakan, 0, ',', '.'),
                'created_at' => $created_at,
                'status' => $status_badge,
                'action' => $detail_btn,
            ];
        })->values()->toArray();

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalFiltered,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
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

        DB::beginTransaction();

        if ($request->jenis_tagihan == '') {
            $request->jenis_tagihan = 'bebas';
        }

        try {
        // 1. Kumpulkan data items & rekening kombinasi
        $itemRekeningData = [];
        if ($request->has('items') && $request->has('rekening')) {
            $items = $request->items ?? [];
            $rekeningen = $request->rekening ?? [];

            // Combine items dengan rekening berdasarkan index
            foreach ($items as $index => $item) {
                if (!empty($item['id']) && !empty($rekeningen[$index]['id'])) {
                    $kategori = KategoriTagihan::find($item['id']);
                    $rekeningObj = DataRekening::find($rekeningen[$index]['id']);

                    // Validasi kategori ada
                    if (!$kategori) {
                        throw new \Exception("Kategori Tagihan dengan ID {$item['id']} tidak ditemukan.");
                    }

                    // Validasi rekening ada
                    if (!$rekeningObj) {
                        throw new \Exception("Rekening Pembayaran dengan ID {$rekeningen[$index]['id']} tidak ditemukan.");
                    }

                    $nominal_item = $item['nominal'] ?? $kategori->biaya_tagihan;
                    $itemRekeningData[] = [
                        'kategori_id' => $item['id'],
                        'nominal' => $nominal_item,
                        'kategori' => $kategori,
                        'rekening_id' => $rekeningen[$index]['id'],
                    ];
                }
            }
        }

        if (empty($itemRekeningData)) {
            throw new \Exception("Tidak ada kombinasi item & rekening yang valid. Pastikan setiap item memiliki rekening pembayaran.");
        }

        $kategoriIds = collect($itemRekeningData)->pluck('kategori_id')->filter()->all();

        // Ambil siswa target
        $siswaList = [];
        if ($request->target === 'per' && $request->has('siswa')) {
            $query = Siswa::whereIn('id', $request->siswa)
                ->where('status','1');

            // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
            if (Auth::user()->yayasan_id) {
                $query->whereHas('kelas.unit', function ($q) {
                    $q->where('yayasan_id', Auth::user()->yayasan_id);
                });
            } elseif (Auth::user()->unit_id) {
                $query->whereHas('kelas.unit', function ($q) {
                    $q->where('id', Auth::user()->unit_id);
                });
            }

            $siswaList = $query->get();
        } elseif ($request->target === 'all' && $request->kelas) {
            $query = Siswa::where('kelas_id', $request->kelas)
                ->where('status','1');
            // Filter berdasarkan prioritas: yayasan_id > unit_id > admin
            if (Auth::user()->yayasan_id) {
                $query->whereHas('kelas.unit', function ($q) {
                    $q->where('id', Auth::user()->unit_id);
                });
            } elseif (Auth::user()->unit_id) {
                $query->whereHas('kelas.unit', function ($q) {
                    $q->where('id', Auth::user()->unit_id);
                });
            }

            $siswaList = $query->get();
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

        // 3. LOOP SETIAP ITEM & REKENING KOMBINASI - BUAT TAGIHAN TERPISAH
        foreach ($itemRekeningData as $itemData) {
            // Buat tagihan baru untuk SETIAP kombinasi item & rekening
            $tagihan = Tagihan::create([
                'unit_id' => $request->unit_id,
                'kelas_id' => $request->kelas ?? null,
                'target' => $request->target,
                'jenis_tagihan' => $request->jenis_tagihan,
                'periode' => $request->jenis_tagihan === 'bulanan' ? $request->periode : null,
                'nominal_bebas' => $request->jenis_tagihan === 'bebas' ? $itemData['nominal'] : null,
                'bulan_mulai' => $request->bulan_mulai,
                'tahun_mulai' => $request->tahun_mulai,
                'rekening_id' => $itemData['rekening_id'], // Simpan rekening_id langsung
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

            }
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

        $dataPerbulan = $tagihanSiswa->map(function ($ts) use ($bulanMulai, $tahunMulai, $nominal, $namaKategori, $kodeKategori, $id, $siswa) {
            $date = Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($ts->bulan_ke - 1);

            // Total potongan untuk tagihan ini
            $totalPotongan = $ts->potonganSiswa->sum('nominal');

            // Nominal setelah potongan
            $nominalSetelahPotongan = $nominal - $totalPotongan;

            // Generate kode tagihan yang unique per transaksi dengan bulan_ke
            $kodeTagihanUnique = 'TAG-' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($ts->bulan_ke, 2, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd');

            // Generate no invoice (unique untuk belum bayar)
            $noInvoice = 'INV-' . str_pad($siswa->id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($ts->bulan_ke, 2, '0', STR_PAD_LEFT);

            // Cek pembayaran untuk mendapatkan kode pembayaran jika sudah lunas
            $kodePembayaran = '';
            if ($ts->status == 1) {
                // Ambil kode pembayaran dari tabel pembayaran_tagihan
                $pembayaranTagihan = \App\Models\Pembayarantagihan::where('tagihan_siswa_id', $ts->id)
                    ->where('status_approval', 'approved')
                    ->first();

                if ($pembayaranTagihan && $pembayaranTagihan->code_pembayaran) {
                    $kodePembayaran = $pembayaranTagihan->code_pembayaran;
                }
            }

            // Hitung total yang sudah dibayar (sum dari pembayaran_tagihan yang approved)
            $totalBayar = \App\Models\Pembayarantagihan::where('tagihan_siswa_id', $ts->id)
                ->where('status_approval', 'approved')
                ->sum('jumlah_bayar');

            return [
                'id'            => $ts->id,
                'tagihan_id'    => $ts->tagihan_id,
                'kode_kategori' => $kodeKategori,
                'nama_kategori' => $namaKategori,
                'kode_tagihan'  => $kodeTagihanUnique,
                'no_invoice'    => $noInvoice,
                'kode_pembayaran' => $kodePembayaran,
                'bulan'         => $date->translatedFormat('F'),
                'tahun'         => $date->year,
                'nominal'       => $nominal,
                'potongan'      => $totalPotongan,
                'nominal_akhir' => $nominalSetelahPotongan,
                'total_bayar'   => $totalBayar,
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

                // Generate kode tagihan yang sama dengan dataPerbulan (unik per bulan_ke)
                $kodeTagihanUnique = 'TAG-' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($bulanKe, 2, '0', STR_PAD_LEFT);

                return [
                    'id'                => $p->id,
                    'tagihan_siswa_id'  => $ts?->id,
                    'kode_kategori'     => $kodeKategori,
                    'kode_tagihan'      => $kodeTagihanUnique,
                    'kode_pembayaran'   => $p->code_pembayaran ?? '-',
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
            'potonganSiswa.potongan',
            'pembayaranTagihan' // Load pembayaran untuk get keuangan_transaksi
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

            // Get keuangan_transaksi id dari pembayaran yang related
            $keuanganTransaksiId = null;
            if ($ts->pembayaranTagihan && $ts->pembayaranTagihan->count() > 0) {
                // Ambil pembayaran pertama dan cari keuangan_transaksi nya
                $pembayaran = $ts->pembayaranTagihan->first();
                $keuanganTransaksi = \App\Models\Keuangan_transaksi::where('referensi_tagihan_id', $pembayaran->id)->first();
                if ($keuanganTransaksi) {
                    $keuanganTransaksiId = $keuanganTransaksi->id;
                }
            }

            $row = [
                'no'                => $counter++,
                'id'                => $ts->id,
                'keuangan_transaksi_id' => $keuanganTransaksiId,
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
            'potonganSiswa.potongan',
            'pembayaranTagihan' // Load pembayaran untuk get keuangan_transaksi
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

            // Get keuangan_transaksi id dari pembayaran yang related
            $keuanganTransaksiId = null;
            if ($ts->pembayaranTagihan && $ts->pembayaranTagihan->count() > 0) {
                // Ambil pembayaran pertama dan cari keuangan_transaksi nya
                $pembayaran = $ts->pembayaranTagihan->first();
                $keuanganTransaksi = \App\Models\Keuangan_transaksi::where('referensi_tagihan_id', $pembayaran->id)->first();
                if ($keuanganTransaksi) {
                    $keuanganTransaksiId = $keuanganTransaksi->id;
                }
            }

            $row = [
                'no'                => $index + 1,
                'id'                => $ts->id,
                'keuangan_transaksi_id' => $keuanganTransaksiId,
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
                'created_at' => $first->tagihan->created_at,
                'sisa_nominal' => $first->tagihan->items->sum('sisa_nominal'),
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

    /**
     * Cetak Struk Tagihan (PDF)
     * Digunakan untuk cetak struk dari tabel "Tagihan Seluruh Periode" maupun "Riwayat Pembayaran"
     */
    public function cetakStruk_tagihan($tagihanSiswaId, $type = 'tagihan')
    {
        // Load data tagihan siswa
        $tagihanSiswa = Tagihansiswa::with([
            'siswa.user',
            'tagihan.unit',
            'tagihan.kelas',
            'tagihan.items.kategori',
            'potonganSiswa.potongan'
        ])->findOrFail($tagihanSiswaId);

        $tagihan = $tagihanSiswa->tagihan;
        $siswa = $tagihanSiswa->siswa;
        $unit = $tagihan->unit;
        $nominal = $tagihan->items->sum('nominal');
        $totalPotongan = $tagihanSiswa->potonganSiswa->sum('nominal');
        $nominalAkhir = $nominal - $totalPotongan;

        // Hitung bulan dan tahun
        $bulanMulai = (int)$tagihan->bulan_mulai;
        $tahunMulai = (int)$tagihan->tahun_mulai;
        $date = Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($tagihanSiswa->bulan_ke - 1);

        $bulan = $date->translatedFormat('F');
        $tahun = $date->year;
        $namaKategori = $tagihan->items->pluck('kategori.nama_kategori')->implode(', ');

        // Generate kode tagihan unique per bulan_ke
        $kodeTagihanUnique = 'TAG-' . str_pad($tagihan->id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($tagihanSiswa->bulan_ke, 2, '0', STR_PAD_LEFT);

        // Ambil kode surat dari unit
        $kodeSurat = $unit->code ?? 'UNIT-' . str_pad($unit->id, 3, '0', STR_PAD_LEFT);

        // Ambil kode pembayaran jika sudah approved
        $kodePembayaran = '';
        $pembayaranTagihan = \App\Models\Pembayarantagihan::where('tagihan_siswa_id', $tagihanSiswaId)
            ->where('status_approval', 'approved')
            ->first();

        if ($pembayaranTagihan && $pembayaranTagihan->code_pembayaran) {
            $kodePembayaran = $pembayaranTagihan->code_pembayaran;
        }

        // Status pembayaran
        $status = $tagihanSiswa->status == 1 ? 'LUNAS' : 'BELUM LUNAS';
        $statusBadge = $tagihanSiswa->status == 1
            ? '<span style="color: green; font-weight: bold;">LUNAS</span>'
            : '<span style="color: red; font-weight: bold;">BELUM LUNAS</span>';

        // Build HTML untuk PDF
        $html = '
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px;">
                <h2 style="margin: 0; padding: 5px;">STRUK PEMBAYARAN</h2>
                <p style="margin: 5px 0; font-size: 11px; color: #666;">Kode Surat: <strong>' . $kodeSurat . '</strong></p>
            </div>
        </div>

        <div style="margin-bottom: 15px; font-size: 12px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 30%;"><strong>Nama Siswa:</strong></td>
                    <td style="width: 70%;">' . ($siswa->user->name ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>NISN:</strong></td>
                    <td>' . ($siswa->nisn ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Kelas:</strong></td>
                    <td>' . ($tagihan->kelas->nama_kelas ?? '-') . '</td>
                </tr>
                <tr>
                    <td><strong>Unit:</strong></td>
                    <td>' . ($tagihan->unit->nama_unit ?? '-') . '</td>
                </tr>
            </table>
        </div>

        <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 10px 0; margin-bottom: 15px; font-size: 12px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 30%;"><strong>Kode Tagihan:</strong></td>
                    <td style="width: 70%;">' . $kodeTagihanUnique . '</td>
                </tr>
                ' . ($kodePembayaran ? '<tr>
                    <td><strong>Kode Pembayaran:</strong></td>
                    <td style="color: green; font-weight: bold;">' . $kodePembayaran . '</td>
                </tr>' : '') . '
                <tr>
                    <td><strong>Jenis Tagihan:</strong></td>
                    <td>' . $namaKategori . '</td>
                </tr>
                <tr>
                    <td><strong>Periode:</strong></td>
                    <td>' . $bulan . ' ' . $tahun . '</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Cetak:</strong></td>
                    <td>' . now()->format('d/m/Y H:i:s') . '</td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 15px; font-size: 12px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #f0f0f0;">
                    <td style="width: 50%; padding: 8px; border: 1px solid #ddd;"><strong>Rincian Tagihan</strong></td>
                    <td style="width: 50%; padding: 8px; border: 1px solid #ddd; text-align: right;"><strong>Rp ' . number_format($nominal, 0, ',', '.') . '</strong></td>
                </tr>
                <tr>
                    <td style="width: 50%; padding: 8px; border: 1px solid #ddd;"><strong>Potongan</strong></td>
                    <td style="width: 50%; padding: 8px; border: 1px solid #ddd; text-align: right;"><strong>Rp ' . number_format($totalPotongan, 0, ',', '.') . '</strong></td>
                </tr>
                <tr style="background-color: #fff3cd;">
                    <td style="width: 50%; padding: 8px; border: 2px solid #000;"><strong>TOTAL TAGIHAN</strong></td>
                    <td style="width: 50%; padding: 8px; border: 2px solid #000; text-align: right;"><strong style="font-size: 14px;">Rp ' . number_format($nominalAkhir, 0, ',', '.') . '</strong></td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; padding: 15px; background-color: #f9f9f9; margin-bottom: 15px; border: 2px solid ' . ($tagihanSiswa->status == 1 ? '#28a745' : '#dc3545') . ';">
            <h3 style="margin: 0; color: ' . ($tagihanSiswa->status == 1 ? '#28a745' : '#dc3545') . ';">STATUS: ' . $status . '</h3>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 11px; color: #666;">
            <p>Terima kasih telah melakukan pembayaran.</p>
            <p>Simpan struk ini sebagai bukti pembayaran Anda.</p>
            <hr style="border: none; border-top: 1px dashed #999; margin: 10px 0;">
            <p style="font-size: 10px;">Dicetak oleh: ' . Auth::user()->name . ' | ' . now()->format('d-m-Y H:i') . '</p>
            <p style="font-size: 9px; margin: 5px 0;">Kode Surat: ' . $kodeSurat . ' | Ref: ' . $kodeTagihanUnique . '</p>
        </div>
        ';

        // Generate PDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $mpdf->SetTitle('Struk Tagihan - ' . $kodeTagihanUnique);
        $mpdf->SetAuthor(Auth::user()->name);
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Struk-' . $kodeTagihanUnique . '-' . date('Ymd') . '.pdf', 'I');
    }

    /**
     * Cetak Invoice untuk tagihan yang sudah lunas
     */
    public function cetakInvoice(Request $request, $tagihanSiswaId)
    {
        // Load data tagihan siswa dengan relasi pembayaran
        $tagihanSiswa = Tagihansiswa::with([
            'siswa.user',
            'tagihan.unit',
            'tagihan.kelas',
            'tagihan.items.kategori',
            'potonganSiswa.potongan'
        ])->findOrFail($tagihanSiswaId);

        // Ambil pembayaran tagihan untuk mendapat kode pembayaran
        $pembayaranTagihan = \App\Models\Pembayarantagihan::where('tagihan_siswa_id', $tagihanSiswaId)
            ->where('status_approval', 'approved')
            ->first();

        if (!$pembayaranTagihan) {
            return response()->json(['error' => 'Pembayaran tidak ditemukan'], 404);
        }

        $tagihan = $tagihanSiswa->tagihan;
        $siswa = $tagihanSiswa->siswa;
        $unit = $tagihan->unit;
        $nominal = $tagihan->items->sum('nominal');
        $totalPotongan = $tagihanSiswa->potonganSiswa->sum('nominal');
        $nominalAkhir = $nominal - $totalPotongan;

        // Hitung bulan dan tahun
        $bulanMulai = (int)$tagihan->bulan_mulai;
        $tahunMulai = (int)$tagihan->tahun_mulai;
        $date = Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($tagihanSiswa->bulan_ke - 1);

        $bulan = $date->translatedFormat('F');
        $tahun = $date->year;
        $namaKategori = $tagihan->items->pluck('kategori.nama_kategori')->implode(', ');

        // Generate kode tagihan unique dengan tanggal lengkap
        // Jika dikirim dari query parameter, gunakan itu. Jika tidak, generate sendiri
        $kodeTagihanUnique = $request->query('kode_tagihan');

        if (empty($kodeTagihanUnique)) {
            // Fallback: generate kode tagihan dengan tanggal hari ini
            $kodeTagihanUnique = 'TAG-' . str_pad($tagihan->id, 5, '0', STR_PAD_LEFT) . '-' . str_pad($tagihanSiswa->bulan_ke, 2, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd');
        }

        // Ambil kode surat dari unit
        $kodeSurat = $unit->code ?? 'UNIT-' . str_pad($unit->id, 3, '0', STR_PAD_LEFT);

        // Kode pembayaran dari pembayaran_tagihan
        $kodePembayaran = $pembayaranTagihan->code_pembayaran ?? '-';

        // Build HTML untuk Invoice PDF
        $html = '
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
                <h1 style="margin: 5px 0; font-size: 24px;">INVOICE</h1>
                <p style="margin: 5px 0; font-size: 14px; color: #666;">Kode Surat: ' . $kodeSurat . '</p>
            </div>
        </div>

        <div style="margin-bottom: 20px; font-size: 11px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <h4 style="margin: 0 0 10px 0; border-bottom: 1px solid #999; padding-bottom: 5px;">DARI:</h4>
                        <p style="margin: 5px 0;"><strong>' . ($unit->nama_unit ?? '-') . '</strong></p>
                        <p style="margin: 5px 0;">' . ($unit->alamat ?? '') . '</p>
                        <p style="margin: 5px 0;">Email: ' . ($unit->email ?? '-') . '</p>
                        <p style="margin: 5px 0;">Telp: ' . ($unit->no_hp ?? '-') . '</p>
                    </td>
                    <td style="width: 50%; vertical-align: top; padding-left: 20px;">
                        <h4 style="margin: 0 0 10px 0; border-bottom: 1px solid #999; padding-bottom: 5px;">UNTUK:</h4>
                        <p style="margin: 5px 0;"><strong>' . ($siswa->user->name ?? '-') . '</strong></p>
                        <p style="margin: 5px 0;">NISN: ' . ($siswa->nisn ?? '-') . '</p>
                        <p style="margin: 5px 0;">Kelas: ' . ($tagihan->kelas->nama_kelas ?? '-') . '</p>
                        <p style="margin: 5px 0;">Unit: ' . ($unit->nama_unit ?? '-') . '</p>
                    </td>
                </tr>
            </table>
        </div>

        <div style="border: 2px solid #000; padding: 10px; margin-bottom: 20px; font-size: 11px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #f0f0f0;">
                    <td style="width: 30%; padding: 8px; border-bottom: 1px solid #ddd;"><strong>No. Invoice</strong></td>
                    <td style="width: 70%; padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . $kodePembayaran . '</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Tanggal Invoice</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">' . now()->format('d/m/Y') . '</td>
                </tr>
                <tr style="background-color: #fff3cd;">
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Tanggal Pembayaran</strong></td>
                    <td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . ($tagihanSiswa->tanggal_bayar ? \Carbon\Carbon::parse($tagihanSiswa->tanggal_bayar)->format('d/m/Y') : '-') . '</strong></td>
                </tr>
            </table>
        </div>

        <div style="margin-bottom: 20px; font-size: 11px;">
            <h4 style="margin: 0 0 10px 0; border-bottom: 2px solid #000; padding-bottom: 5px;">DETAIL PEMBAYARAN</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #f0f0f0;">
                    <td style="width: 50%; padding: 8px; border: 1px solid #ddd;"><strong>Deskripsi</strong></td>
                    <td style="width: 50%; padding: 8px; border: 1px solid #ddd; text-align: right;"><strong>Jumlah</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">Jenis Tagihan: ' . $namaKategori . '</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">Rp ' . number_format($nominal, 0, ',', '.') . '</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">Periode: ' . $bulan . ' ' . $tahun . '</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">-</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">Potongan/Diskon</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">- Rp ' . number_format($totalPotongan, 0, ',', '.') . '</td>
                </tr>
                <tr style="background-color: #28a745; color: white;">
                    <td style="padding: 10px; border: 2px solid #000; font-weight: bold;">TOTAL YANG DIBAYARKAN</td>
                    <td style="padding: 10px; border: 2px solid #000; text-align: right; font-weight: bold; font-size: 13px;">Rp ' . number_format($nominalAkhir, 0, ',', '.') . '</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; padding: 15px; background-color: #d4edda; border: 2px solid #28a745; margin-bottom: 20px;">
            <h3 style="margin: 0; color: #28a745;">✓ PEMBAYARAN LUNAS</h3>
            <p style="margin: 5px 0; font-size: 11px;">Terima kasih atas pembayaran Anda</p>
        </div>

        <div style="margin-top: 30px; font-size: 10px;">
            <table style="width: 100%; text-align: center;">
                <tr>
                    <td style="width: 50%;">
                        <p style="margin-bottom: 40px;">Diketahui,</p>
                        <p>________________________</p>
                        <p style="margin: 5px 0; font-size: 9px;">Kepala Unit</p>
                    </td>
                    <td style="width: 50%;">
                        <p style="margin-bottom: 40px;">Penerima,</p>
                        <p>________________________</p>
                        <p style="margin: 5px 0; font-size: 9px;">' . ($siswa->user->name ?? 'Siswa') . '</p>
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 9px; color: #666; border-top: 1px dashed #999; padding-top: 10px;">
            <p>Dokumen ini dicetak oleh sistem pada ' . now()->format('d/m/Y H:i:s') . '</p>
            <p>Invoice ID: ' . $kodePembayaran . ' | Kode Surat: ' . $kodeSurat . '</p>
        </div>
        ';

        // Generate PDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 5,
            'margin_footer' => 5,
        ]);

        $mpdf->SetTitle('Invoice - ' . $kodePembayaran);
        $mpdf->SetAuthor($unit->nama_unit ?? 'Sistem');
        $mpdf->SetSubject('Invoice Pembayaran Tagihan');
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Invoice-' . $kodePembayaran . '-' . date('Ymd') . '.pdf', 'I');
    }

    /**
     * Cetak struk pembayaran tagihan (format thermal printer)
     */
    public function cetakStruk($pembayaranId)
    {
        // Ambil data pembayaran tagihan
        $pembayaran = \App\Models\Pembayarantagihan::with([
            'tagihanSiswa.siswa.user',
            'tagihanSiswa.siswa.kelas',
            'tagihanSiswa.tagihan.items.kategori',
            'tagihanSiswa.siswa.unit',
            'user' // kasir yang melakukan transaksi
        ])->findOrFail($pembayaranId);

        // Buat objek transaksi yang kompatibel dengan view struk
        $transaksi = new \stdClass();
        $transaksi->code_pembayaran = $pembayaran->code_pembayaran;
        $transaksi->tanggal_transaksi = $pembayaran->waktu_transaksi;
        $transaksi->jumlah = $pembayaran->jumlah_bayar;
        $transaksi->metode = strtoupper($pembayaran->metode_bayar);
        $transaksi->status_verifikasi = $pembayaran->status_approval;
        $transaksi->jenis_transaksi = 'pembayaran';

        // Creator/kasir
        $transaksi->creator = $pembayaran->user;

        // Penerima (siswa)
        $transaksi->penerima = $pembayaran->tagihanSiswa->siswa;
        $transaksi->penerima->unit = $pembayaran->tagihanSiswa->siswa->unit;
        $transaksi->penerima->kelas = $pembayaran->tagihanSiswa->siswa->kelas;

        // Pembayaran tagihan
        $transaksi->pembayaranTagihan = $pembayaran;
        $transaksi->pembayaranTagihan->tagihanSiswa->tagihan = $pembayaran->tagihanSiswa->tagihan;

        // Generate HTML dari view struk yang sama
        $html = view('pages.keuangan.transaksi.struk_pembayaran', compact('transaksi'))->render();

        // Konfigurasi mPDF untuk ukuran struk thermal (80mm)
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [80, 200], // 80mm width, dynamic height
            'orientation' => 'P',
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_top' => 3,
            'margin_bottom' => 3,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $mpdf->SetTitle('Struk Pembayaran - ' . $pembayaran->code_pembayaran);
        $mpdf->SetAuthor(Auth::user()->name ?? 'System');
        $mpdf->WriteHTML($html);

        // Output PDF ke browser
        return $mpdf->Output('Struk-' . $pembayaran->code_pembayaran . '.pdf', 'I');
    }
}

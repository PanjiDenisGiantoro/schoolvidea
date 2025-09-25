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
use App\Models\TagihanItem;
use App\Models\TagihanSiswa;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    public function create()
    {
        $units = Unit::all();
        $kelas = Kelas::all();
        $kategoriTagihan = Kategoritagihan::all();

        return view('pages.tagihan.create', compact('units','kelas','kategoriTagihan'));
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
        ])->get();

//        dd($tagihans);
        $summary = [
            'jumlah_data' => $tagihans->count(),
            'nominal_tagihan' => $tagihans->sum(function($t) {
                $total = $t->items->sum('nominal') * ($t->periode ?? 1);
                return $total * $t->tagihanSiswa->count(); // total tagihan semua siswa
            }),
            'sudah_dibayar' => $tagihans->sum(function($t) {
                return $t->tagihanSiswa->sum(function($ts) {
                    return $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                });
            }),
            'belum_dibayar' => $tagihans->sum(function($t) {
                $total_tagihan = $t->items->sum('nominal') * ($t->periode ?? 1);
                return $t->tagihanSiswa->sum(function($ts) use ($total_tagihan) {
                    $sudah_bayar = $ts->siswa->pembayaranTagihan->sum('jumlah_bayar');
                    return $total_tagihan - $sudah_bayar;
                });
            }),
        ];
        return view('pages.tagihan.index', compact('tagihans','summary'));
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

        if($request->jenis_tagihan == ''){
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
                    TagihanItem::create([
                        'tagihan_id' => $tagihan->id,
                        'kategori_id' => $item['id'],
                        'nominal' => $nominal_item,
                    ]);
                    $total_tagihan += $nominal_item;
                }
            }

            // Ambil siswa target
            $siswaList = [];
            if ($request->target === 'per' && $request->has('siswa')) {
                $siswaList = Siswa::whereIn('id', $request->siswa)->get();
            } elseif ($request->target === 'all' && $request->kelas) {
                $siswaList = Siswa::where('kelas_id', $request->kelas)->get();
            }

            // 3. Simpan tagihan_siswa dan jurnal
            $settings = setting_akun::where('kategori', 'tagihan-masuk')->get();
            $akun_debit = $settings->where('debit', 1)->first()?->akun_id; // piutang siswa
            $akun_kredit = $settings->where('kredit', 1)->first()?->akun_id; // pendapatan sekolah

            if (!$akun_debit || !$akun_kredit) {
                throw new \Exception("Setting akun untuk kategori tagihan-masuk belum lengkap.");
            }

            foreach ($siswaList as $siswa) {
                if ($tagihan->jenis_tagihan === 'bulanan' && $tagihan->periode) {
                    // generate sesuai jumlah bulan
                    for ($i = 1; $i <= $tagihan->periode; $i++) {
                        TagihanSiswa::create([
                            'tagihan_id'    => $tagihan->id,
                            'siswa_id'      => $siswa->id,
                            'bulan_ke'      => $i,
                            'tanggal_bayar' => null,
                        ]);
                    }
                } else {
                    // jenis bebas → cuma 1 row
                    TagihanSiswa::create([
                        'tagihan_id'    => $tagihan->id,
                        'siswa_id'      => $siswa->id,
                        'bulan_ke'      => null,
                        'tanggal_bayar' => null,
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
        $tagihanSiswa = TagihanSiswa::with([
            'siswa.user',
            'tagihan.unit',
            'tagihan.kelas',
            'tagihan.items.kategori',
            'siswa.pembayaranTagihan'
        ])
            ->where('tagihan_id', $id)
            ->where('siswa_id', $siswaId)
            ->get(); // ambil semua bulan


        return view('pages.tagihan.show', compact('tagihanSiswa'));
    }

    public function perbulan($siswaId,$tagihanId)
    {
        $tagihanSiswa = TagihanSiswa::with('siswa','tagihan.items.kategori')
            ->where('tagihan_id', $tagihanId)
            ->where('siswa_id', $siswaId)
            ->orderBy('bulan_ke')
            ->get();

        if ($tagihanSiswa->isEmpty()) {
            return response()->json([]);
        }

        $firstTagihan = $tagihanSiswa->first()->tagihan;
        $nominal = $firstTagihan->items->sum('nominal');
        $namaKategori = $firstTagihan->items->pluck('kategori.nama_kategori')->implode(', ');

        $bulanMulai = (int) $firstTagihan->bulan_mulai;
        $tahunMulai = (int) $firstTagihan->tahun_mulai;

        $data = $tagihanSiswa->map(function ($ts) use ($bulanMulai, $tahunMulai, $nominal, $namaKategori) {
            $date = Carbon::createFromDate($tahunMulai, $bulanMulai, 1)->addMonths($ts->bulan_ke - 1);

            return [
                'id'            => $ts->id,
                'tagihan_id'    => $ts->tagihan_id,
                'nama_kategori' => $namaKategori,
                'bulan'         => $date->translatedFormat('F'),
                'tahun'         => $date->year,
                'nominal'       => $nominal,
                'status'        => $ts->status == 1 ? 'Lunas' : 'Belum Lunas',
                'tanggal_bayar' => $ts->tanggal_bayar,
            ];
        });

        return response()->json($data);
    }

    public function daftarTagihan($siswaId)
    {
        $tagihanSiswa = TagihanSiswa::with('tagihan.items.kategori')
            ->where('siswa_id', $siswaId)
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
            'detail'        => $data,
            'total_tagihan' => $data->sum('nominal'),
        ]);
    }

}

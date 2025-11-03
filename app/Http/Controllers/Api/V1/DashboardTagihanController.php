<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Tagihansiswa;
use App\Models\Pembayarantagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardTagihanController extends Controller
{
    /**
     * Get dashboard summary untuk tagihan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        $unitId = $request->get('unit_id');
        $kelasId = $request->get('kelas_id');
        $tahunAjaranId = $request->get('tahun_ajaran_id');

        // Query dasar untuk tagihan siswa
        $query = Tagihansiswa::with(['tagihan.items.kategori', 'siswa', 'pembayaranTagihan']);

        // Filter berdasarkan siswa
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }

        // Filter berdasarkan unit, kelas, tahun ajaran via relasi tagihan
        if ($unitId || $kelasId || $tahunAjaranId) {
            $query->whereHas('tagihan', function ($q) use ($unitId, $kelasId, $tahunAjaranId) {
                if ($unitId) {
                    $q->where('unit_id', $unitId);
                }
                if ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                }
                if ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                }
            });
        }

        $tagihanSiswa = $query->get();

        // Hitung total tagihan
        $totalTagihan = $tagihanSiswa->sum('total_tagihan');

        // Hitung total yang sudah dibayar (lunas)
        $totalLunas = $tagihanSiswa->where('status_pembayaran', 'lunas')->sum('total_tagihan');

        // Hitung total belum lunas
        $totalBelumLunas = $tagihanSiswa->whereIn('status_pembayaran', ['belum_bayar', 'cicilan'])->sum(function ($item) {
            return $item->total_tagihan - $item->total_dibayar;
        });

        // Hitung jumlah tagihan
        $jumlahTagihan = $tagihanSiswa->count();
        $jumlahLunas = $tagihanSiswa->where('status_pembayaran', 'lunas')->count();
        $jumlahBelumLunas = $tagihanSiswa->whereIn('status_pembayaran', ['belum_bayar', 'cicilan'])->count();

        // Hitung persentase selesai
        $persenSelesai = $jumlahTagihan > 0 ? round(($jumlahLunas / $jumlahTagihan) * 100, 2) : 0;

        // Hitung total jatuh tempo (yang sudah lewat tanggal jatuh tempo dan belum lunas)
        $now = now();
        $tagihanJatuhTempo = $tagihanSiswa->filter(function ($item) use ($now) {
            return $item->tanggal_jatuh_tempo &&
                   $item->tanggal_jatuh_tempo < $now &&
                   in_array($item->status_pembayaran, ['belum_bayar', 'cicilan']);
        });

        $totalJatuhTempo = $tagihanJatuhTempo->sum(function ($item) {
            return $item->total_tagihan - $item->total_dibayar;
        });
        $jumlahJatuhTempo = $tagihanJatuhTempo->count();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_tagihan' => $totalTagihan,
                    'total_lunas' => $totalLunas,
                    'total_belum_lunas' => $totalBelumLunas,
                    'total_jatuh_tempo' => $totalJatuhTempo,
                ],
                'jumlah' => [
                    'jumlah_tagihan' => $jumlahTagihan,
                    'jumlah_lunas' => $jumlahLunas,
                    'jumlah_belum_lunas' => $jumlahBelumLunas,
                    'jumlah_jatuh_tempo' => $jumlahJatuhTempo,
                ],
                'persentase_selesai' => $persenSelesai
            ]
        ]);
    }

    /**
     * Get list tagihan dengan detail
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listTagihan(Request $request)
    {
        $siswaId = $request->get('siswa_id');
        $unitId = $request->get('unit_id');
        $kelasId = $request->get('kelas_id');
        $tahunAjaranId = $request->get('tahun_ajaran_id');
        $status = $request->get('status'); // lunas, belum_bayar, cicilan, jatuh_tempo
        $perPage = $request->get('per_page', 15);

        // Query dasar untuk tagihan siswa
        $query = Tagihansiswa::with([
            'tagihan.items.kategori',
            'siswa',
            'pembayaranTagihan'
        ]);

        // Filter berdasarkan siswa
        if ($siswaId) {
            $query->where('siswa_id', $siswaId);
        }

        // Filter berdasarkan unit, kelas, tahun ajaran via relasi tagihan
        if ($unitId || $kelasId || $tahunAjaranId) {
            $query->whereHas('tagihan', function ($q) use ($unitId, $kelasId, $tahunAjaranId) {
                if ($unitId) {
                    $q->where('unit_id', $unitId);
                }
                if ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                }
                if ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                }
            });
        }

        // Filter berdasarkan status
        if ($status) {
            if ($status === 'jatuh_tempo') {
                $query->where('tanggal_jatuh_tempo', '<', now())
                      ->whereIn('status_pembayaran', ['belum_bayar', 'cicilan']);
            } else {
                $query->where('status_pembayaran', $status);
            }
        }

        $tagihan = $query->paginate($perPage);

        // Transform data untuk response yang lebih clean
        $tagihan->getCollection()->transform(function ($item) {
            $kategoriList = $item->tagihan->items->map(function ($tagihanItem) {
                return [
                    'id' => $tagihanItem->kategori->id ?? null,
                    'nama_kategori' => $tagihanItem->kategori->nama_kategori ?? null,
                ];
            })->unique('id')->values();

            $isJatuhTempo = $item->tanggal_jatuh_tempo &&
                           $item->tanggal_jatuh_tempo < now() &&
                           in_array($item->status_pembayaran, ['belum_bayar', 'cicilan']);

            return [
                'id' => $item->id,
                'nama_tagihan' => $item->tagihan->nama_tagihan ?? null,
                'kategori_tagihan' => $kategoriList,
                'nominal_tagihan' => $item->total_tagihan,
                'total_dibayar' => $item->total_dibayar,
                'sisa_tagihan' => $item->total_tagihan - $item->total_dibayar,
                'status' => $item->status_pembayaran,
                'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                'is_jatuh_tempo' => $isJatuhTempo,
                'siswa' => [
                    'id' => $item->siswa->id ?? null,
                    'nisn' => $item->siswa->nisn ?? null,
                    'nama' => $item->siswa->name ?? null,
                ],
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tagihan
        ]);
    }

    /**
     * Get detail tagihan by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailTagihan($id)
    {
        $tagihanSiswa = Tagihansiswa::with([
            'tagihan.items.kategori',
            'siswa.kelas',
            'pembayaranTagihan',
            'potonganSiswa'
        ])->find($id);

        if (!$tagihanSiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan tidak ditemukan'
            ], 404);
        }

        // Transform kategori tagihan
        $kategoriList = $tagihanSiswa->tagihan->items->map(function ($item) {
            return [
                'id' => $item->kategori->id ?? null,
                'nama_kategori' => $item->kategori->nama_kategori ?? null,
                'nominal' => $item->nominal ?? 0,
            ];
        });

        // Transform pembayaran
        $pembayaranList = $tagihanSiswa->pembayaranTagihan->map(function ($pembayaran) {
            return [
                'id' => $pembayaran->id,
                'jumlah_dibayar' => $pembayaran->jumlah_dibayar,
                'tanggal_bayar' => $pembayaran->tanggal_bayar,
                'metode_pembayaran' => $pembayaran->metode_pembayaran,
                'keterangan' => $pembayaran->keterangan,
                'created_at' => $pembayaran->created_at,
            ];
        });

        // Transform potongan
        $potonganList = $tagihanSiswa->potonganSiswa->map(function ($potongan) {
            return [
                'id' => $potongan->id,
                'jumlah_potongan' => $potongan->jumlah_potongan,
                'keterangan' => $potongan->keterangan,
                'created_at' => $potongan->created_at,
            ];
        });

        $isJatuhTempo = $tagihanSiswa->tanggal_jatuh_tempo &&
                       $tagihanSiswa->tanggal_jatuh_tempo < now() &&
                       in_array($tagihanSiswa->status_pembayaran, ['belum_bayar', 'cicilan']);

        $response = [
            'id' => $tagihanSiswa->id,
            'nama_tagihan' => $tagihanSiswa->tagihan->nama_tagihan ?? null,
            'kategori_tagihan' => $kategoriList,
            'nominal_tagihan' => $tagihanSiswa->total_tagihan,
            'total_dibayar' => $tagihanSiswa->total_dibayar,
            'total_potongan' => $potonganList->sum('jumlah_potongan'),
            'sisa_tagihan' => $tagihanSiswa->total_tagihan - $tagihanSiswa->total_dibayar,
            'status' => $tagihanSiswa->status_pembayaran,
            'tanggal_jatuh_tempo' => $tagihanSiswa->tanggal_jatuh_tempo,
            'is_jatuh_tempo' => $isJatuhTempo,
            'siswa' => [
                'id' => $tagihanSiswa->siswa->id ?? null,
                'nisn' => $tagihanSiswa->siswa->nisn ?? null,
                'nama' => $tagihanSiswa->siswa->name ?? null,
                'kelas' => $tagihanSiswa->siswa->kelas->nama_kelas ?? null,
            ],
            'riwayat_pembayaran' => $pembayaranList,
            'riwayat_potongan' => $potonganList,
            'created_at' => $tagihanSiswa->created_at,
            'updated_at' => $tagihanSiswa->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }
}

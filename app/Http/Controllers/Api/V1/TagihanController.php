<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Tagihansiswa;
use App\Models\Pembayarantagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagihanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $unitId = $request->get('unit_id');
        $kelasId = $request->get('kelas_id');

        $query = Tagihan::with(['kategori', 'unit', 'kelas', 'tahunAjaran']);

        if ($search) {
            $query->where('nama_tagihan', 'like', "%{$search}%");
        }

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $tagihan = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tagihan
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_tagihan' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_tagihans,id',
            'unit_id' => 'required|exists:units,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'jumlah' => 'required|numeric|min:0',
            'jenis' => 'required|in:bulanan,bebas',
            'tanggal_jatuh_tempo' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tagihan = Tagihan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tagihan created successfully',
            'data' => $tagihan->load(['kategori', 'unit', 'kelas', 'tahunAjaran'])
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $tagihan = Tagihan::with(['kategori', 'unit', 'kelas', 'tahunAjaran', 'tagihanSiswa'])->find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tagihan
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $tagihan = Tagihan::find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_tagihan' => 'sometimes|required|string|max:255',
            'kategori_id' => 'sometimes|required|exists:kategori_tagihans,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tahun_ajaran_id' => 'sometimes|required|exists:tahun_ajarans,id',
            'jumlah' => 'sometimes|required|numeric|min:0',
            'jenis' => 'sometimes|required|in:bulanan,bebas',
            'tanggal_jatuh_tempo' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tagihan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tagihan updated successfully',
            'data' => $tagihan->load(['kategori', 'unit', 'kelas', 'tahunAjaran'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $tagihan = Tagihan::find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan not found'
            ], 404);
        }

        $tagihan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tagihan deleted successfully'
        ]);
    }

    /**
     * Get tagihan by siswa.
     *
     * @param int $siswaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySiswa($siswaId)
    {
        $tagihan = Tagihansiswa::with(['tagihan', 'siswa', 'pembayaran'])
            ->where('siswa_id', $siswaId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tagihan
        ]);
    }

    /**
     * Get daftar tagihan bulanan untuk siswa tertentu (distinct by tagihan_id)
     *
     * @param int $siswaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function daftarTagihan($siswaId)
    {
        $tagihanSiswa = Tagihansiswa::with('tagihan.items.kategori', 'pembayaranTagihan')
            ->where('siswa_id', $siswaId)
            ->whereHas('tagihan', function ($query) {
                $query->where('jenis_tagihan', 'bulanan');
            })
            ->get()
            ->groupBy('tagihan_id'); // distinct by tagihan

        if ($tagihanSiswa->isEmpty()) {
            return response()->json([
                'success' => true,
                'detail' => []
            ]);
        }

        $data = $tagihanSiswa->map(function ($rows) {
            $first = $rows->first();

            // Helper function to determine status
            $statusMap = [
                '0' => 'Belum Lunas',
                '1' => 'Lunas',
                '2' => 'Cicilan'
            ];

            // Count sudah_lunas dan belum_lunas berdasarkan pembayaran yang sudah diverifikasi/approve
            $sudahLunas = 0;
            $belumLunas = 0;

            foreach ($rows as $ts) {
                // Check jika ada pembayaran yang sudah approved (status_approval = approved)
                $hasApprovedPayment = Pembayarantagihan::where('tagihan_siswa_id', $ts->id)
                    ->where('status_approval', 'approved')
                    ->exists();

                if ($hasApprovedPayment) {
                    $sudahLunas++;
                } else {
                    $belumLunas++;
                }
            }

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
                'sudah_lunas'   => $sudahLunas,
                'belum_lunas'   => $belumLunas,
                'status'        => $sudahLunas === $rows->count() ? 'Lunas' : ($sudahLunas > 0 ? 'Cicilan' : 'Belum Lunas'),
                'created_at'    => $first->created_at->format('Y-m-d H:i:s'),
                'updated_at'    => $first->updated_at->format('Y-m-d H:i:s'),
            ];
        })->values(); // reset index biar array rapi

        return response()->json([
            'success' => true,
            'detail' => $data
        ]);
    }

    /**
     * Get tagihan perbulan untuk siswa dan tagihan tertentu
     *
     * @param int $siswaId
     * @param int $tagihanId
     * @return \Illuminate\Http\JsonResponse
     */
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
                'success' => true,
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

            // Hitung jumlah yang sudah dibayar
            // Jika status = 1 (lunas), maka sudah dibayar = jumlahTagihan
            // Jika status != 1 (belum lunas), maka sudah dibayar = 0
            $jumlahDibayar = ($ts->status == 1) ? $jumlahTagihan : 0;

            // Tunggakan = sisa nominal yang masih harus dibayar
            $jumlahTunggakan = $ts->sisa_nominal;

            $row = [
                'no'                => $index + 1,
                'id'                => $ts->id,
                'periode'           => $date->translatedFormat('F Y'),
                'tahun'             => $date->year,
                'tagihan_kelas'     => $namaKategori,
                'rincian_tagihan'   => (int) $nominal,
                'jumlah_potongan'   => (int) $totalPotonganSemuaBulan,
                'jumlah_tagihan'    => (int) $jumlahTagihan,
                'jumlah_dibayar'    => (int) $jumlahDibayar,
                'jumlah_tunggakan'  => (int) $jumlahTunggakan,
                'nominal_pembayaran'=> (int) $jumlahTagihan,
                'catatan'           => $ts->catatan ?? '',
                'status'            => $ts->status,
                'kategori_id'       => $firstTagihan->items->first()->kategori_id ?? 1,
            ];

            if ($ts->status == 1) {
                $sudahLunas[] = $row;
            } else {
                $belumLunas[] = $row;
            }
        }

        return response()->json([
            'success' => true,
            'belum_lunas' => $belumLunas,
            'sudah_lunas' => $sudahLunas
        ]);
    }
}
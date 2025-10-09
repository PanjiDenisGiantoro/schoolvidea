<?php

namespace App\Http\Controllers;

use App\Models\Kategoritagihan;
use App\Models\Tagihan;
use App\Models\Tagihansiswa;
use App\Models\Unit;
use Illuminate\Http\Request;

use App\Models\Potongan;
use App\Models\PotonganSiswa;
class PotonganController extends Controller
{
    public function index()
    {        $potongans = Potongan::with('unit', 'kelas', 'kategoriTagihan')->get();

        return view('pages.potongan.potongan', compact('potongans'));
    }
    public function create()
    {
        $units = Unit::get();
        $kategoriTagihan = Kategoritagihan::get();
        return view('pages.potongan.potongan_create',compact('units','kategoriTagihan'));

    }
    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'kelas_id' => 'required|exists:kelas,id',
            'kategori_tagihan_id' => 'required',
            'tipe_potongan' => 'required|in:nominal,persentase',
            'nilai' => 'required|numeric',
            'keterangan' => 'nullable|string|max:64',
            'siswa_id' => 'required|array', // array of selected siswa ids
            'siswa_id.*' => 'exists:siswas,id', // Validate each siswa_id
        ]);

        try {
            // Store the Potongan data
            $potongan = Potongan::create([
                'unit_id' => $request->unit_id,
                'kelas_id' => $request->kelas_id,
                'kategori_tagihan_id' => $request->kategori_tagihan_id,
                'tipe_potongan' => $request->tipe_potongan,
                'nilai' => $request->nilai,
                'keterangan' => $request->keterangan,
            ]);

            // Store PotonganSiswa for each selected siswa
            foreach ($request->siswa_id as $siswaId) {
                // Fetch the related tagihan_id from the tagihan_siswa table
                $tagihanSiswa = Tagihansiswa::where('siswa_id', $siswaId)
                    ->whereHas('tagihan', function($query) use ($request) {
                        $query->where('kelas_id', $request->kelas_id);
                    })
                    ->first();

                // Ensure tagihan_siswa record is found
                if ($tagihanSiswa) {
                    // Now get the tagihan using tagihan_siswa
                    $tagihan = $tagihanSiswa->tagihan;

                    // Calculate the nominal for the discount (potongan)
                    $nominal = $this->calculateNominal($potongan, $tagihan);

                    // Store the PotonganSiswa entry
                    PotonganSiswa::create([
                        'potongan_id' => $potongan->id,
                        'tagihan_id' => $tagihan->id,
                        'tagihan_siswa_id' => $tagihanSiswa->id,
                        'nominal' => $nominal,
                    ]);
                }
            }

            return redirect()->route('potongan.index')->with('success', 'Potongan successfully created.');
        } catch(\Exception $e) {
            dd($e->getMessage());
        }
    }
    private function calculateNominal(Potongan $potongan, Tagihan $tagihan)
    {
        if ($potongan->tipe_potongan == 'nominal') {
            return $potongan->nilai; // Fixed value for Harga type
        } elseif ($potongan->tipe_potongan == 'persentase') {
            return ($potongan->nilai / 100) * $tagihan->nominal; // Percentage of the tagihan nominal
        }
        return 0;
    }
    }

<?php

namespace App\Http\Controllers;

use App\Models\Kategoritagihan;
use App\Models\Tagihan;
use App\Models\Unit;
use Illuminate\Http\Request;

use App\Models\Potongan;
use App\Models\PotonganSiswa;
class PotonganController extends Controller
{
    public function index()
    {
        return view('pages.potongan.potongan');
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
//            'siswa_id' => 'required|array', // array of selected siswa ids
//            'siswa_id.*' => 'exists:siswas,id', // Validate each siswa_id
        ]);

        try {
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
                // Here you can define how to calculate nominal (e.g., based on tagihan)
                $tagihan = Tagihan::where('siswa_id', $siswaId)->where('kelas_id', $request->kelas_id)->first();
                $nominal = $this->calculateNominal($potongan, $tagihan);

                PotonganSiswa::create([
                    'potongan_id' => $potongan->id,
                    'tagihan_id' => $tagihan->id,
                    'tagihan_siswa_id' => $tagihan->siswa_id,
                    'nominal' => $nominal,
                ]);
            }

        }catch(\Exception $e){
            dd($e->getMessage());
        }
        // Store Potongan data

        return redirect()->route('potongan.index')->with('success', 'Potongan successfully created.');
    }
    private function calculateNominal(Potongan $potongan, Tagihan $tagihan)
    {
        if ($potongan->tipe_potongan == 'Harga') {
            return $potongan->nilai; // Fixed value for Harga type
        } elseif ($potongan->tipe_potongan == 'Persentase') {
            return ($potongan->nilai / 100) * $tagihan->nominal; // Percentage of the tagihan nominal
        }
        return 0;
    }
    }

<?php

namespace App\Http\Controllers;

use App\Models\Kategoritagihan;
use App\Models\Kelas;
use App\Models\Tagihan;
use App\Models\Tagihansiswa;
use App\Models\Unit;
use Illuminate\Http\Request;

use App\Models\Potongan;
use App\Models\Potongansiswa;
use Illuminate\Support\Facades\Auth;

class PotonganController extends Controller
{
    public function index()
    {
        $potongans = Potongan::with('unit', 'kelas', 'kategoriTagihan')->get();

        return view('pages.potongan.potongan', compact('potongans'));
    }


    public function create()
    {
        $units = Unit::get();
        $kelas = Kelas::when(Auth::user()->unit_id, function ($query, $unit_id) {
            $query->where('unit_id', $unit_id);
        })->where('status', '1')->get();
        $kategoriTagihan = Kategoritagihan::when(Auth::user()->unit_id, function ($unit, $query) {
            $query('unit_id', $query->unit_id);
        })->get();
        return view('pages.potongan.potongan_create', compact('units', 'kategoriTagihan','kelas'));

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
                $tagihanSiswa = Tagihansiswa::with('siswa')->where('siswa_id', $siswaId)
                    ->whereHas('tagihan', function ($query) use ($request) {
                        $query->where('kelas_id', $request->kelas_id);
                    })->where('status', '=', '0')
                    ->orWhere('status', '=', '2')
                    ->first();

                // Ensure tagihan_siswa record is found
                if ($tagihanSiswa) {
                    // Now get the tagihan using tagihan_siswa
                    $tagihan = $tagihanSiswa->tagihan;

                    // Calculate the nominal for the discount (potongan)
                    $nominal = $this->calculateNominal($potongan, $tagihanSiswa->sisa_nominal);

                    // Store the PotonganSiswa entry
                    Potongansiswa::create([
                        'potongan_id' => $potongan->id,
                        'tagihan_id' => $tagihan->id,
                        'tagihan_siswa_id' => $tagihanSiswa->siswa->id,
                        'nominal' => $nominal,
                    ]);
                    Tagihansiswa::where('siswa_id', $tagihanSiswa->siswa_id)
                        ->where('tagihan_id', $tagihan->id)
                        ->where('status', '=', '0')
                        ->orWhere('status', '=', '2')
                        ->update([
                            'sisa_nominal' => $tagihanSiswa->sisa_nominal - $nominal,
                        ]);
                }
            }

            return redirect()->route('potongan.index')->with('success', 'Potongan successfully created.');
        } catch (\Exception $e) {
            return redirect()->route('potongan.index')->with('danger', $e->getMessage());

        }
    }

    private function calculateNominal($potongan, $tagihan)
    {
        if ($potongan->tipe_potongan == 'nominal') {
            return $potongan->nilai; // Fixed value for Harga type
        } elseif ($potongan->tipe_potongan == 'persentase') {
            return ($potongan->nilai / 100) * $tagihan; // Percentage of the tagihan nominal
        }
        return 0;
    }

    public function show($id)
    {
        // Find the Potongan by ID and eager load related data
        $potongan = Potongan::with(['unit', 'kelas', 'kategoriTagihan', 'potonganSiswa.tagihanSiswa', 'potonganSiswa.tagihan', 'tagihan'])
            ->findOrFail($id);


        // Return the show view with the Potongan data
        return view('pages.potongan.show', compact('potongan'));
    }

    public function edit($id)
    {

        $potongan = Potongan::with(['unit', 'kelas', 'kategoriTagihan', 'potonganSiswa.tagihanSiswa', 'potonganSiswa.tagihan', 'tagihan'])
            ->findOrFail($id);
        $units = Unit::get();
        $kelas = Kelas::when(Auth::user()->unit_id, function ($query, $unit_id) {
            $query->where('unit_id', $unit_id);
        })->where('status', '1')->get();
        $kategoriTagihan = Kategoritagihan::when(Auth::user()->unit_id, function ($unit, $query) {
            $query('unit_id', $query->unit_id);
        })->get();
        return view('pages.potongan.potongan_create', compact('potongan', 'units', 'kategoriTagihan', 'kelas'));
    }

    public function destroy($id)
    {
        $potongan = Potongan::findOrFail($id);

        $tagihansiswa = Potongansiswa::where('potongan_id', $id)->get();
        foreach ($tagihansiswa as $tagihansiswa) {
            Tagihansiswa::where('id', $tagihansiswa->tagihan_siswa_id)
                ->update([
                    'sisa_nominal' => $tagihansiswa->sisa_nominal + $tagihansiswa->nominal,
                ]);
        }

        $potongan->delete();
        return redirect()->route('potongan.index')->with('success', 'Potongan successfully deleted.');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nilai' => 'required|numeric',
            'keterangan' => 'nullable|string|max:64',
        ]);

        try {
            // Update potongan
            $potongan = Potongan::findOrFail($id);
            $potongan->nilai = $request->nilai;
            $potongan->keterangan = $request->keterangan;
            $potongan->save();

            // Update setiap potongan_siswa dan tagihan_siswa terkait
            $potonganSiswas = Potongansiswa::where('potongan_id', $potongan->id)->get();
            foreach ($potonganSiswas as $potonganSiswa) {
                $tagihanSiswa = Tagihansiswa::where('siswa_id', $potonganSiswa->tagihan_siswa_id)->where('tagihan_id', $potongan->kategori_tagihan_id)->first();
                $nominalPotonganSiswa = intval($potonganSiswa->nominal); // atau gunakan (int) $potonganSiswa->nominal
                $nominalTagihanSisa = intval($tagihanSiswa->sisa_nominal);

                $nominalBaru = $this->calculateNominal($potongan, $nominalTagihanSisa + $nominalPotonganSiswa);
                // Update nominal di potongan_siswa
                $potonganSiswa->nominal = $nominalBaru;
                $potonganSiswa->save();

                // Update sisa_nominal di tagihan_siswa
                $tagihanSiswa->sisa_nominal = (intval($tagihanSiswa->sisa_nominal) + intval($potonganSiswa->nominal)) - $nominalBaru;
                $tagihanSiswa->save();
            }

            return redirect()->route('potongan.index')->with('success', 'Potongan dan potongan siswa berhasil diupdate.');
        } catch (\Exception $e) {
            return redirect()->route('potongan.index')->with('danger', $e->getMessage());
        }
    }
}

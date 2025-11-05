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
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query
        $query = Potongan::with('unit', 'kelas', 'kategoriTagihan');

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $query->whereHas('unit', function($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tipe_potongan', 'like', "%{$search}%")
                  ->orWhere('nilai', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('unit', function($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kelas', function($q) use ($search) {
                      $q->where('nama_kelas', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kategoriTagihan', function($q) use ($search) {
                      $q->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $potongans = $query->paginate(15)->appends($request->except('page'));

        return view('pages.potongan.potongan', compact('potongans', 'units'));
    }


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
        return view('pages.potongan.potongan_create', compact('units', 'kategoriTagihan','kelas'));

    }

    public function store(Request $request)
    {
        // Validate the incoming data
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'kelas_switch' => 'required|in:all,select',
            'kelas_id' => 'nullable',
            'kategori_tagihan_id' => 'required',
            'tipe_potongan' => 'required|in:nominal,persentase',
            'nilai' => 'required|numeric',
            'keterangan' => 'nullable|string|max:64',
            'siswa_id' => 'required_if:kelas_switch,select|nullable|array', // array of selected siswa ids
            'siswa_id.*' => 'exists:siswas,id', // Validate each siswa_id
        ]);

        try {
            // Determine if we're applying to all classes or specific class
            if ($request->kelas_switch === 'all') {
                // Get all kelas in the selected unit
                $kelasList = Kelas::where('unit_id', $request->unit_id)->where('status', '1')->get();

                foreach ($kelasList as $kelas) {
                    // Create a potongan for each class
                    $potongan = Potongan::create([
                        'unit_id' => $request->unit_id,
                        'kelas_id' => $kelas->id,
                        'kategori_tagihan_id' => $request->kategori_tagihan_id,
                        'tipe_potongan' => $request->tipe_potongan,
                        'nilai' => $request->nilai,
                        'keterangan' => $request->keterangan,
                    ]);

                    // Get all siswa in this kelas
                    $siswaList = $kelas->siswas;

                    // Apply potongan to all siswa in this kelas
                    foreach ($siswaList as $siswa) {
                        $this->applyPotonganToSiswa($potongan, $siswa->id, $kelas->id);
                    }
                }
            } else {
                // Store the Potongan data for specific class
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
                    $this->applyPotonganToSiswa($potongan, $siswaId, $request->kelas_id);
                }
            }

            return redirect()->route('potongan.index')->with('success', 'Potongan successfully created.');
        } catch (\Exception $e) {
            return redirect()->route('potongan.index')->with('danger', $e->getMessage());
        }
    }

    private function applyPotonganToSiswa($potongan, $siswaId, $kelasId)
    {
        // Fetch the related tagihan_id from the tagihan_siswa table
        $tagihanSiswa = Tagihansiswa::with('siswa')->where('siswa_id', $siswaId)
            ->whereHas('tagihan', function ($query) use ($kelasId) {
                $query->where('kelas_id', $kelasId);
            })
            ->where(function ($query) {
                $query->where('status', '=', '0')
                    ->orWhere('status', '=', '2');
            })
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

            // Update sisa_nominal in tagihan_siswa
            Tagihansiswa::where('siswa_id', $tagihanSiswa->siswa_id)
                ->where('tagihan_id', $tagihan->id)
                ->where(function ($query) {
                    $query->where('status', '=', '0')
                        ->orWhere('status', '=', '2');
                })
                ->update([
                    'sisa_nominal' => $tagihanSiswa->sisa_nominal - $nominal,
                ]);
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

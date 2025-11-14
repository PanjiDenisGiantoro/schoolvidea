<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Officer;
use App\Models\Siswa;
use App\Models\Tahun_ajaran;
use App\Models\Unit;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query
        $query = Kelas::with('unit', 'officer.user', 'jurusan');

        // Filter berdasarkan prioritas: yayasan_id > unit_id > admin filter
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan kelas dari semua unit di yayasan tersebut
            $query->whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            });
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan kelas dari unit tersebut saja
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            // Admin user filtering by unit
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality across all columns
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhere('kode_kelas', 'like', "%{$search}%")
                  ->orWhereHas('unit', function ($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  })
                  ->orWhereHas('officer.user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('jurusan', function ($q) use ($search) {
                      $q->where('nama_jurusan', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $kelas = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->except('page'));

        $headers = [
            'No',
            'Nama Unit',
            'Nama Kelas',
            'Wali Kelas',
            'Jurusan',
            'Status',
            'Action'
        ];

        return view('pages.data_master.kelas.kelas', compact('kelas', 'headers', 'units'));
    }
    public function create()
    {
        // Filter berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $yayasan = Yayasan::where('id', Auth::user()->yayasan_id)->get();
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->get();
            $wali = Officer::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $jurusan = Jurusan::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $yayasan = Yayasan::with('units')
                ->when(Auth::user()->unit_id, function ($query, $unitId) {
                    $query->whereHas('units', function ($q) use ($unitId) {
                        $q->where('id', $unitId);
                    });
                })->get();
            $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('id', $unitId);
            })->get();
            $wali = Officer::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })->get();
            $jurusan = Jurusan::when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })->get();
        } else {
            // Admin bisa melihat semua
            $yayasan = Yayasan::get();
            $units = Unit::get();
            $wali = Officer::get();
            $jurusan = Jurusan::get();
        }

        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.kelas.kelas_create', compact('jurusan', 'yayasan', 'units', 'wali', 'tahun_ajaran', 'tahun_ajaran_selected'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas'      => 'required|string|max:255',
            'kode_kelas'      => 'required',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'unit_id'         => 'required|exists:units,id',
            'officer_id'      => 'nullable',
            'status'          => 'required|in:0,1',
            'jurusan_id'      => 'nullable',
        ]);

        try {
            Kelas::create($validated);

            return redirect()->route('kelas.index')
                ->with('success', 'Data kelas berhasil ditambahkan: ' . $validated['nama_kelas']);
            dd($request->all());

        } catch (\Exception $e) {
            Log::error($e);

            return back()->with('error', 'Terjadi kesalahan saat menambahkan kelas.');
        }
    }

    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);

        // Filter berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $yayasan = Yayasan::where('id', Auth::user()->yayasan_id)->get();
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->get();
            $wali = Officer::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $jurusan = Jurusan::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $yayasan = Yayasan::active()->get();
            $units = Unit::where('id', Auth::user()->unit_id)->get();
            $wali = Officer::where('unit_id', Auth::user()->unit_id)->get();
            $jurusan = Jurusan::where('unit_id', Auth::user()->unit_id)->get();
        } else {
            // Admin bisa melihat semua
            $yayasan = Yayasan::active()->get();
            $units = Unit::get();
            $wali = Officer::get();
            $jurusan = Jurusan::get();
        }

        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.kelas.kelas_create', compact('jurusan', 'tahun_ajaran', 'kelas', 'yayasan', 'units', 'wali', 'tahun_ajaran_selected'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas'      => 'required|string|max:255',
            'tahun_ajaran_id' => 'required',
            'kode_kelas' => 'required',
            'unit_id'         => 'required',
            'officer_id'      => 'nullable',
            'status'          => 'required|in:0,1',
            'jurusan_id'      => 'nullable',
        ]);
        $data = $request->all();
        $kelas = Kelas::findOrFail($id);
        $kelas->update($data);

        return redirect()->route('kelas.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();
        return redirect()->route('kelas.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);

        // Filter berdasarkan user access
        if (Auth::user()->yayasan_id) {
            // Jika user punya yayasan_id, tampilkan data dari semua unit di yayasan tersebut
            $yayasan = Yayasan::where('id', Auth::user()->yayasan_id)->get();
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->get();
            $wali = Officer::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $jurusan = Jurusan::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
        } elseif (Auth::user()->unit_id) {
            // Jika user punya unit_id, tampilkan data dari unit tersebut saja
            $yayasan = Yayasan::active()->get();
            $units = Unit::where('id', Auth::user()->unit_id)->get();
            $wali = Officer::where('unit_id', Auth::user()->unit_id)->get();
            $jurusan = Jurusan::where('unit_id', Auth::user()->unit_id)->get();
        } else {
            // Admin bisa melihat semua
            $yayasan = Yayasan::active()->get();
            $units = Unit::get();
            $wali = Officer::get();
            $jurusan = Jurusan::get();
        }

        $tahun_ajaran = Tahun_ajaran::orderBy('id', 'desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $show = true;

        return view('pages.data_master.kelas.kelas_create', compact('jurusan', 'kelas', 'show', 'yayasan', 'units', 'wali', 'tahun_ajaran', 'tahun_ajaran_selected'));
    }
    public function getSiswa($id)
    {
        $siswa = Siswa::where('kelas_id', $id)
            ->where('status', '1')
            ->with('user:id,name') // ambil nama dari relasi user
            ->get(['id','user_id','kelas_id','nisn']);

        return response()->json($siswa);
    }

    public function getWaliKelasByUnit($unitId)
    {
        $waliKelas = Officer::where('unit_id', $unitId)
            ->with('user:id,name')
            ->get();

        return response()->json($waliKelas);
    }

    public function getJurusanByUnit($unitId)
    {
        $jurusan = Jurusan::where('unit_id', $unitId)
            ->where('status', '1')
            ->get();

        return response()->json($jurusan);
    }

    public function getKelasByUnit($unitId)
    {
        $kelas = Kelas::where('unit_id', $unitId)
            ->where('status', '1')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'kode_kelas']);

        return response()->json($kelas);
    }

}

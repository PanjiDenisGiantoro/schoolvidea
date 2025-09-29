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

class KelasController extends Controller
{
    public function index(){
        $kelas = Kelas::with('unit','officer.user','jurusan')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->where('unit_id', $unitId);
            })
            ->get();

        $headers = [
            'No',
            'Nama Unit',
            'Nama Kelas',
            'Wali Kelas',
            'Jurusan',
            'Status',
            'Action'
        ];

        return view('pages.data_master.kelas.kelas', compact('kelas','headers'));
    }
    public function create()
    {
        $yayasan = Yayasan::with('units')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('units', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })
        ->get();
        $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })->get();
        $wali = Officer::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->get();

        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $jurusan = Jurusan::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->get();

        return view('pages.data_master.kelas.kelas_create', compact('jurusan','yayasan','units','wali','tahun_ajaran','tahun_ajaran_selected'));
    }
    public function store(Request $request)
    {
        try{
            $request->validate([
                'nama_kelas'      => 'required|string|max:255',
                'tahun_ajaran_id' => 'required',
                'unit_id'         => 'required',
                'officer_id'      => 'required',
                'status'          => 'required|in:0,1', // atau 0/1 kalau status disimpan angka
                'jurusan_id'      => 'required',
            ]);

            Kelas::create([
                'nama_kelas'      => $request->nama_kelas,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'unit_id'         => $request->unit_id,
                'officer_id'      => $request->officer_id,
                'status'          => $request->status,
                'jurusan_id'      => $request->jurusan_id,
            ]);

            return redirect()->route('kelas.index')
                ->with('success', 'Data kelas berhasil ditambahkan: ' . $request->nama_kelas);

        }catch (\Exception $e){
            dd($e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan data kelas: ' . $e->getMessage());
        }

    }
    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
        $yayasan = Yayasan::active()->get();
        $units = Unit::get();
        $wali = Officer::get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $jurusan = Jurusan::get();
        return view('pages.data_master.kelas.kelas_create', compact('jurusan','tahun_ajaran','kelas','yayasan','units','wali','tahun_ajaran_selected'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas'      => 'required|string|max:255',
            'tahun_ajaran_id' => 'required',
            'unit_id'         => 'required',
            'officer_id'      => 'required',
            'status'          => 'required|in:0,1',
            'jurusan_id'      => 'required',
        ]);
        $data = $request->all();
        $kelas = Kelas::findOrFail($id);
        $kelas->update($data);
        return redirect()->route('kelas.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $kelas = Tahun_ajaran::findOrFail($id);
        $kelas->delete();
        return redirect()->route('kelas.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $kelas = Kelas::findOrFail($id);
        $yayasan = Yayasan::active()->get();
        $units = Unit::get();
        $wali = Officer::get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();
        $jurusan = Jurusan::get();

        $show = true;
        return view('pages.data_master.kelas.kelas_create', compact('jurusan','kelas','show','yayasan','units','wali','tahun_ajaran','tahun_ajaran_selected'));
    }
    public function getSiswa($id)
    {
        $siswa = Siswa::where('kelas_id', $id)
            ->with('user:id,name') // ambil nama dari relasi user
            ->get(['id','user_id','kelas_id','nisn']);

        return response()->json($siswa);
    }

}

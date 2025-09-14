<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Officer;
use App\Models\Tahun_ajaran;
use App\Models\Unit;
use App\Models\Yayasan;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(){
        $kelas = Kelas::with('unit','officer.user')->get();
        $headers = [
            'No',
            'Nama Unit',
            'Nama Kelas',
            'Wali Kelas',
            'Status',
            'Action'
        ];

        return view('pages.data_master.kelas.kelas', compact('kelas','headers'));
    }
    public function create()
    {
        $yayasan = Yayasan::active()->get();
        $units = Unit::get();
        $wali = Officer::wali()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.kelas.kelas_create', compact('yayasan','units','wali','tahun_ajaran','tahun_ajaran_selected'));
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
            ]);

            Kelas::create([
                'nama_kelas'      => $request->nama_kelas,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'unit_id'         => $request->unit_id,
                'officer_id'      => $request->officer_id,
                'status'          => $request->status,
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
        $kelas = Tahun_ajaran::findOrFail($id);
        $yayasan = Yayasan::active()->get();
        $units = Unit::get();
        $wali = Officer::wali()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        return view('pages.data_master.kelas.kelas_create', compact('tahun_ajaran','kelas','yayasan','units','wali','tahun_ajaran_selected'));
    }
    public function update(Request $request, $id)
    {
        $kelas = Tahun_ajaran::findOrFail($id);
        $data = $request->all();
        $data['tanggal_mulai']   = $request->tanggal_mulai ? $request->tanggal_mulai . '-01' : null;
        $data['tanggal_selesai'] = $request->tanggal_selesai ? $request->tanggal_selesai . '-01' : null;

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
        $wali = Officer::wali()->get();
        $tahun_ajaran = Tahun_ajaran::orderBy('id','desc')->get();
        $tahun_ajaran_selected = Tahun_ajaran::isactive()->first();

        $show = true;
        return view('pages.data_master.kelas.kelas_create', compact('kelas','show','yayasan','units','wali','tahun_ajaran','tahun_ajaran_selected'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Tahun_ajaran;
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
        return view('pages.data_master.kelas.kelas_create', compact('yayasan'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|max:255|unique:tahun_ajarans,tahun_ajaran',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'semester' => 'required',
            'status' => 'required|in:0,1',
        ]);
        $tanggal_mulai = $request->tanggal_mulai ? $request->tanggal_mulai . '-01' : null;
        $tanggal_selesai = $request->tanggal_selesai ? $request->tanggal_selesai . '-01' : null;

        Tahun_ajaran::create([
            'tahun_ajaran' => $request->tahun_ajaran,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'semester' => $request->semester,
            'status' => $request->status,
        ]);

        return redirect()->route('kelas.index')
            ->with('success', 'Data berhasil ditambahkan dengan Tahun Ajaran' . $request->tahun_ajaran);
    }
    public function edit($id)
    {
        $kelas = Tahun_ajaran::findOrFail($id);
        return view('pages.data_master.kelas.kelas_create', compact('tahun_ajaran'));
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
        $kelas = Tahun_ajaran::findOrFail($id);
        $show = true;
        return view('pages.data_master.kelas.kelas_create', compact('kelas','show'));
    }
}

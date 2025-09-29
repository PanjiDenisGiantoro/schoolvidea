<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Akun;
use Illuminate\Support\Facades\Auth;

class AkunController extends Controller
{
    public function index()
    {
        $akuns = Akun::with('parent', 'unit')
            ->when(Auth::user()->unit_id,function ($query, $unit_id){
                $query->where('unit_id',$unit_id);
            })
            ->get(); // ambil relasi parent & unit
        $headers = [
            'No',
            'Kode Akun',
            'Nama Akun',
            'Kategori',
            'Tipe',
            'Parent',
            'Unit',
            'Status',
            'Action'
        ];

        return view('pages.data_master.akun.akun', compact('akuns', 'headers'));
    }

    public function create()
    {
        $parents = Akun::when(Auth::user()->unit_id,function ($query,$unitId){
            $query->where('unit_id',$unitId);
        })->where('status','1')->get(); // opsi parent
        $units = \App\Models\Unit::when(Auth::user()->unit_id,function ($query, $unit_id){
          $query->where('id',$unit_id);
        })
        ->where('status','1')
            ->get();// ambil data unit
        return view('pages.data_master.akun.akun_create', compact('parents','units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_akun' => 'required|unique:akuns,kode_akun',
            'nama_akun' => 'required',
            'tipe' => 'required|in:ASET,LIABILITAS,EKUITAS,PENDAPATAN,BEBAN',
            'kategori_akun' => 'nullable'
        ]);

        Akun::create([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'tipe' => $request->tipe,
            'parent_id' => $request->parent_id,
            'unit_id' => $request->unit_id,
            'status' => $request->status ?? '1',
            'kategori_akun' => $request->kategori_akun
        ]);

        return redirect()->route('akun.index')
            ->with('success', 'Data akun berhasil ditambahkan');
    }

    public function edit($id)
    {
        $akun = Akun::findOrFail($id);
        $parents = Akun::where('id', '!=', $id)
            ->when(Auth::user()->unit_id,function ($query,$unit_id){
                $query->where('unit_id',$unit_id);
            })
            ->where('status','1')
            ->get(); // exclude diri sendiri
        $units = \App\Models\Unit::when(Auth::user()->unit_id,function ($query,$unit_id){
            $query->where('id',$unit_id);
        })
            ->where('status','1')->get();
        return view('pages.data_master.akun.akun_create', compact('akun', 'parents', 'units'));
    }

    public function update(Request $request, $id)
    {
        $akun = Akun::findOrFail($id);
        $request->validate([
            'kode_akun' => 'required|unique:akuns,kode_akun,' . $akun->id,
            'nama_akun' => 'required',
            'tipe' => 'required|in:ASET,LIABILITAS,EKUITAS,PENDAPATAN,BEBAN',
            'kategori_akun' => 'nullable'
        ]);

        $akun->update([
            'kode_akun' => $request->kode_akun,
            'nama_akun' => $request->nama_akun,
            'tipe' => $request->tipe,
            'parent_id' => $request->parent_id,
            'unit_id' => $request->unit_id,
            'status' => $request->status ?? '1',
            'kategori_akun' => $request->kategori_akun
        ]);

        return redirect()->route('akun.index')
            ->with('success', 'Data akun berhasil diupdate');
    }

    public function destroy($id)
    {
        $akun = Akun::findOrFail($id);
        $akun->delete();

        return redirect()->route('akun.index')
            ->with('success', 'Data akun berhasil dihapus');
    }

    public function show($id)
    {
        $akun = Akun::findOrFail($id);
        $show = true;
        return view('pages.data_master.akun.akun_create', compact('akun','show'));
    }
}

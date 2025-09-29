<?php

namespace App\Http\Controllers;

use App\Models\Tahun_ajaran;
use App\Models\Tipeunit;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TipeunitController extends Controller
{
    public function index(){
        $tipe_unit = Tipeunit::get();
        $headers = [
            'No',
            'Nama Tipe Unit',
            'Status',
            'Action'
        ];

        return view('pages.data_master.tipe_unit.tipe_unit', compact('tipe_unit','headers'));
    }
    public function create()
    {
        return view('pages.data_master.tipe_unit.tipe_unit_create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_tipe_unit' => 'required',
            'status' => 'required',
        ]);

        Tipeunit::create([
            'nama_tipe_unit' => $request->nama_tipe_unit,
            'status' => $request->status,
        ]);

        return redirect()->route('tipe_unit.index')
            ->with('success', 'Data berhasil ditambahkan');
    }
    public function edit($id)
    {
        $tipe_unit = Tipeunit::findOrFail($id);
        return view('pages.data_master.tipe_unit.tipe_unit_create', compact('tipe_unit'));
    }
    public function update(Request $request, $id)
    {
        $tipe_unit = Tipeunit::findOrFail($id);
        $data = $request->all();
        $tipe_unit->update($data);
        return redirect()->route('tipe_unit.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $tipe_unit = Tipeunit::findOrFail($id);
        $tipe_unit->delete();
        return redirect()->route('tipe_unit.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $tipe_unit = Tipeunit::findOrFail($id);
        $show = true;
        return view('pages.data_master.tipe_unit.tipe_unit_create', compact('tipe_unit','show'));
    }
}

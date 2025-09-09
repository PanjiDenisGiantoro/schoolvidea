<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TahunajaranController extends Controller
{
    public function index(){
        $unit = Unit::get();
        $headers = [
            'No',
            'Nama Yayasan',
            'Nama Unit',
            'Code Unit',
            'Logo',
            'No Telp',
            'Email',
            'Alamat',
            'Website',
            'Status',
            'Action'
        ];

        return view('pages.data_master.unit.unit', compact('unit','headers'));
    }
    public function create()
    {
        $yayasan = Yayasan::active()->get();
        return view('pages.data_master.unit.unit_create', compact('yayasan'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_unit' => 'required|string|max:255|unique:units,nama_unit',
            'image' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'website' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);
        // generate central_code 7 huruf acak
        $centralCode = strtoupper(Str::random(5));
        Unit::create([
            'nama_unit' => $request->nama_unit,
            'code' => 'U'.$centralCode,
            'image' => $request->image,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'website' => $request->website,
            'yayasan_id' => $request->yayasan_id,
            'status' => $request->status,
        ]);

        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil ditambahkan dengan Central Code: ' . $centralCode);
    }
    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $yayasan = Yayasan::active()->get();
        return view('pages.data_master.unit.unit_create', compact('unit','yayasan'));
    }
    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);
        $unit->update($request->all());
        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $unit = Unit::findOrFail($id);
        $show = true;
        return view('pages.data_master.unit.unit_create', compact('unit','show'));
    }
}

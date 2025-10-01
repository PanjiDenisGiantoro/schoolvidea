<?php

namespace App\Http\Controllers;

use App\Models\Tipeunit;
use App\Models\Unit;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UnitController extends Controller
{
    public function index(){
        $unit = Unit::with('tipe_unit')->
        when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('id', $unitId);
        })
        ->get();
        $headers = [
            'No',
            'Nama Yayasan',
            'Tipe Unit',
            'Nama Unit',
            'Code Unit',
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
        $yayasan = Yayasan::with('units')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('units', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })->where('status', '1')->get();

        $tipeunit = Tipeunit::where('status','1')->get();
        return view('pages.data_master.unit.unit_create', compact('yayasan','tipeunit'));
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
            'tipe_unit_id' => 'nullable'
        ]);

        $centralCode = $request->code;
        if (empty($centralCode)) {
            $centralCode = 'U' . strtoupper(Str::random(7));
        }
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
            'tipe_unit_id' => $request->tipe_unit_id
        ]);

        return redirect()->route('unit.index')
            ->with('success', 'Data berhasil ditambahkan dengan Central Code: ' . $centralCode);
    }
    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $yayasan = Yayasan::where('status','1')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('units', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })->get();
        $tipeunit = Tipeunit::where('status','1')->get();

        return view('pages.data_master.unit.unit_create', compact('unit','yayasan','tipeunit'));
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
        $yayasan = Yayasan::with('units')
            ->when(Auth::user()->unit_id, function ($query, $unitId) {
                $query->whereHas('units', function ($q) use ($unitId) {
                    $q->where('id', $unitId);
                });
            })->where('status', '1')->get();
        $tipeunit = Tipeunit::where('status','1')->get();

        return view('pages.data_master.unit.unit_create', compact('unit','show','yayasan','tipeunit'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $file = $request->file('file');
        $filename = Str::random(15) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/unit', $filename, 'public');

        return response()->json([
            'success' => true,
            'filepath' => 'storage/' . $path
        ]);
    }

}

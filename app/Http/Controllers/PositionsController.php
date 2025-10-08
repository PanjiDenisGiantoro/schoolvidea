<?php

namespace App\Http\Controllers;

use App\Models\Tahun_ajaran;
use App\Models\Positions;
use App\Models\Yayasan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PositionsController extends Controller
{
    public function index(){
        $positions = Positions::get();
        $headers = [
            'No',
            'Nama Jabatan',
            'Status',
            'Action'
        ];

        return view('pages.data_master.positions.positions', compact('positions','headers'));
    }
    public function create()
    {
        return view('pages.data_master.positions.positions_create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'positions_name' => 'required',
            'status' => 'required',
        ]);

        Positions::create([
            'positions_name' => $request->positions_name,
            'status' => $request->status,
        ]);

        return redirect()->route('positions.index')
            ->with('success', 'Data berhasil ditambahkan');
    }
    public function edit($id)
    {
        $positions = Positions::findOrFail($id);
        return view('pages.data_master.positions.positions_create', compact('positions'));
    }
    public function update(Request $request, $id)
    {
        $positions = Positions::findOrFail($id);
        $data = $request->all();
        $positions->update($data);
        return redirect()->route('positions.index')
            ->with('success', 'Data berhasil diupdate');
    }
    public function destroy($id)
    {
        $positions = Positions::findOrFail($id);
        $positions->delete();
        return redirect()->route('positions.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function show($id)
    {
        $positions = Positions::findOrFail($id);
        $show = true;
        return view('pages.data_master.positions.positions_create', compact('positions','show'));
    }
}

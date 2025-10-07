<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    public function index()
    {
        $rekenings = Rekening::with( 'unit')->get();

        $headers = [
            'No',
            'Type Rekening',
            'Nama Rekening',
            'User',
            'Unit',
            'No Rekening',
            'Bank',
            'KCP',
            'Status',
            'Action'
        ];

        return view('pages.data_master.rekening.rekening', compact('rekenings','headers'));
    }

    public function create()
    {
        $users = User::all();
        $units = Unit::all();
        return view('pages.data_master.rekening.rekening_create', compact('users','units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_rekening' => 'required|string|max:255',
            'nama_rekening' => 'required|string|max:255',
            'nama_pemilik_rekening'       => 'nullable|string|max:255',
            'unit_id'       => 'nullable|exists:units,id',
            'no_rekening'   => 'nullable|string|max:255',
            'bank'          => 'nullable|string|max:255',
            'KCP'           => 'nullable|string|max:255',
            'status'        => 'required|in:1,0',
        ]);

        Rekening::create([
            'type_rekening' => $request->type_rekening,
            'nama_rekening' => $request->nama_rekening,
            'nama_pemilik_rekening'       => $request->nama_pemilik_rekening,
            'unit_id'       => $request->unit_id,
            'no_rekening'   => $request->no_rekening,
            'bank'          => $request->bank,
            'KCP'           => $request->KCP,
            'status'        => $request->status,
            'data'          => $request->data ? json_encode($request->data) : null,
        ]);

        return redirect()->route('rekening.index')
            ->with('success', 'Data rekening berhasil ditambahkan');
    }

    public function edit($id)
    {
        $rekening = Rekening::findOrFail($id);
        $users = User::all();
        $units = Unit::all();
        return view('pages.data_master.rekening.rekening_create', compact('rekening','users','units'));
    }

    public function update(Request $request, $id)
    {
        $rekening = Rekening::findOrFail($id);

        $request->validate([
            'type_rekening' => 'required|string|max:255',
            'nama_rekening' => 'required|string|max:255',
            'nama_pemilik_rekening'       => 'nullable',
            'unit_id'       => 'nullable',
            'no_rekening'   => 'nullable|string|max:255',
            'bank'          => 'nullable|string|max:255',
            'KCP'           => 'nullable|string|max:255',
            'status'        => 'required|in:1,0',
        ]);

        $rekening->update([
            'type_rekening' => $request->type_rekening,
            'nama_rekening' => $request->nama_rekening,
            'nama_pemilik_rekening'       => $request->nama_pemilik_rekening,
            'unit_id'       => $request->unit_id,
            'no_rekening'   => $request->no_rekening,
            'bank'          => $request->bank,
            'KCP'           => $request->KCP,
            'status'        => $request->status,
            'data'          => $request->data ? json_encode($request->data) : null,
        ]);

        return redirect()->route('rekening.index')
            ->with('success', 'Data rekening berhasil diupdate');
    }

    public function destroy($id)
    {
        $rekening = Rekening::findOrFail($id);
        $rekening->delete();
        return redirect()->route('rekening.index')
            ->with('success', 'Data rekening berhasil dihapus');
    }

    public function show($id)
    {
        $rekening = Rekening::with(['user','unit'])->findOrFail($id);
        $show = true;
        return view('pages.data_master.rekening.rekening_create', compact('rekening','show'));
    }
}

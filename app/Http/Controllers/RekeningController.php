<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RekeningController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        // Build query
        $query = Rekening::with('unit');

        // Filter by unit_id if user has unit_id OR if admin selects a unit
        if (Auth::user()->unit_id) {
            $query->where('unit_id', Auth::user()->unit_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type_rekening', 'like', "%{$search}%")
                  ->orWhere('nama_rekening', 'like', "%{$search}%")
                  ->orWhere('no_rekening', 'like', "%{$search}%")
                  ->orWhere('bank', 'like', "%{$search}%")
                  ->orWhere('KCP', 'like', "%{$search}%")
                  ->orWhere('nama_pemilik_rekening', 'like', "%{$search}%")
                  ->orWhereHas('unit', function ($q) use ($search) {
                      $q->where('nama_unit', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $rekenings = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->except('page'));

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

        return view('pages.data_master.rekening.rekening', compact('rekenings', 'headers', 'units'));
    }

    public function create()
    {
        $users = User::all();
        $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->get();
        return view('pages.data_master.rekening.rekening_create', compact('users', 'units'));
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
        return view('pages.data_master.rekening.rekening_create', compact('rekening', 'users', 'units'));
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
        $rekening = Rekening::with(['unit'])->findOrFail($id);
        $units = Unit::when(Auth::user()->unit_id, function ($query, $unitId) {
            $query->where('unit_id', $unitId);
        })->get();
        $show = true;
        return view('pages.data_master.rekening.rekening_create', compact('rekening', 'show', 'units'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\PayrollComponents;
use Illuminate\Http\Request;

class PayrollComponentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payroll_components = PayrollComponents::orderBy('created_at', 'desc')->paginate(10);
        $headers = [
            'No',
            'Nama Komponen',
            'Nilai Komponen',
            'Status',
            'Aksi'
        ];

        return view('pages.penggajian.payroll_components.component', compact('payroll_components', 'headers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.penggajian.payroll_components.component_create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'status' => 'required|in:0,1',
            'description' => 'nullable|string'
        ]);

        PayrollComponents::create($validated);
        return redirect()->route('payroll_components.index')
            ->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payroll_components = PayrollComponents::findOrFail($id);
        $show = true;
        return view('pages.penggajian.payroll_components.component_create', compact('payroll_components', 'show'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $payroll_components = PayrollComponents::findOrFail($id);
        return view('pages.penggajian.payroll_components.component_create', compact('payroll_components'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'status' => 'required|in:0,1',
            'description' => 'nullable|string'
        ]);

        $payroll_components = PayrollComponents::findOrFail($id);
        $payroll_components->update($validated);

        return redirect()->route('payroll_components.index')
            ->with('success', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payroll_components = PayrollComponents::findOrFail($id);
        $payroll_components->delete();

        return redirect()->route('payroll_components.index')
            ->with('success', 'Data berhasil dihapus');
    }
    public function getByOfficer($officerId)
    {
        // Ambil jabatan guru/staff
        $officer = Officer::with('position')->find($officerId);
        if (!$officer || !$officer->position) {
            return response()->json(['status' => 'error', 'message' => 'Officer atau jabatan tidak ditemukan']);
        }

        $positionId = $officer->position->id;

        // Ambil semua komponen gaji berdasarkan jabatan
        $components = PayrollComponents::where('position_id', $positionId)
            ->get(['id', 'name', 'value']);

        return response()->json([
            'status' => 'success',
            'position_name' => $officer->position->name,
            'components' => $components,
        ]);
    }
}

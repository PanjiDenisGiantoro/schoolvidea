<?php

namespace App\Http\Controllers;

use App\Models\PayrollDeductions;
use Illuminate\Http\Request;

class PayrollDeductionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payroll_deductions = PayrollDeductions::all();
        $headers = [
            'No',
            'Nama Potongan',
            'Jenis Potongan',
            'Nilai Potongan',
            'Status',
            'Aksi'
        ];

        return view('pages.penggajian.payroll_deductions.deduction', compact('payroll_deductions', 'headers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.penggajian.payroll_deductions.deduction_create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'type' => 'required',
            'status' => 'required|in:0,1',
            'description' => 'nullable|string'
        ]);

        PayrollDeductions::create($validated);
        return redirect()->route('payroll_deductions.index')
            ->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payroll_deductions = PayrollDeductions::findOrFail($id);
        $show = true;
        return view('pages.penggajian.payroll_deductions.deduction_create', compact('payroll_deductions', 'show'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $payroll_deductions = PayrollDeductions::findOrFail($id);
        return view('pages.penggajian.payroll_deductions.deduction_create', compact('payroll_deductions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'type' => 'required',
            'status' => 'required|in:0,1',
            'description' => 'nullable|string'
        ]);

        $payroll_deductions = PayrollDeductions::findOrFail($id);
        $payroll_deductions->update($validated);

        return redirect()->route('payroll_deductions.index')
            ->with('success', 'Data berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payroll_deductions = PayrollDeductions::findOrFail($id);
        $payroll_deductions->delete();

        return redirect()->route('payroll_deductions.index')
            ->with('success', 'Data berhasil dihapus');
    }
}

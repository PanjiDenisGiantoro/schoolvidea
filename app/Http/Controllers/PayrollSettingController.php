<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\PayrollSetting;
use App\Models\PayrollComponents;
use App\Models\PayrollDeductions;
use App\Models\Unit;
use Illuminate\Http\Request;

class PayrollSettingController extends Controller
{
    /**
     * Halaman utama setting payroll.
     */
    public function index()
    {
        $headers = [
            'No',
            'Unit',
            'Nama Guru & Staff',
            'Aksi'
        ];
        // Ambil semua data PayrollSetting beserta relasinya
        $settings = PayrollSetting::with(['unit', 'officer.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.penggajian.payroll_setting.index', compact('settings', 'headers'));
    }

    /**
     * Halaman form create payroll.
     */
    public function create()
    {
        $units = Unit::all();
        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();
        $officers = Officer::with('user:id,name')->get();

        return view('pages.penggajian.payroll_setting.payroll_setting', compact(
            'units',
            'components',
            'deductions',
            'officers'
        ));
    }

    /**
     * Simpan atau update data payroll.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'units_id' => 'required|exists:units,id',
            'officers_id' => 'required|exists:officers,id',
            'teaching_hours' => 'nullable|numeric',
            'salary' => 'nullable|numeric',
            'transport_allowance' => 'nullable|numeric',
            'meal_allowance' => 'nullable|numeric',
            'communication_allowance' => 'nullable|numeric',
            'other_allowance' => 'nullable|numeric',
            'billing_period' => 'nullable|string',
            'start_month' => 'nullable|string',
            'start_year' => 'nullable|integer',

            'components_id' => 'array',
            'component_value' => 'array',
            'deductions_id' => 'array',
            'deduction_value' => 'array',
        ]);

        try {
            $mainData = collect($validated)->except([
                'components_id',
                'component_value',
                'deductions_id',
                'deduction_value'
            ])->toArray();

            $payrollSetting = PayrollSetting::updateOrCreate(
                [
                    'units_id' => $validated['units_id'],
                    'officers_id' => $validated['officers_id'],
                ],
                $mainData
            );

            // === Simpan komponen gaji ===
            if ($request->filled('components_id')) {
                $syncComponents = [];
                foreach ($request->components_id as $index => $compId) {
                    if ($compId) {
                        $syncComponents[$compId] = [
                            'value' => $request->component_value[$index] ?? 0,
                        ];
                    }
                }
                $payrollSetting->components()->sync($syncComponents);
            }

            // === Simpan potongan ===
            if ($request->filled('deductions_id')) {
                $syncDeductions = [];
                foreach ($request->deductions_id as $index => $deductId) {
                    if ($deductId) {
                        $syncDeductions[$deductId] = [
                            'value' => $request->deduction_value[$index] ?? 0,
                        ];
                    }
                }
                $payrollSetting->deductions()->sync($syncDeductions);
            }

            return redirect()->route('payroll_settings.index')->with('success', 'Data payroll berhasil disimpan.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update payroll setting (gunakan logika store).
     */
    public function update(Request $request, $id)
    {
        return $this->store($request);
    }

    /**
     * Tampilkan detail satu payroll setting.
     */
    public function show($id)
    {
        $units = Unit::all();
        $setting = PayrollSetting::with(['unit', 'officer.user', 'components', 'deductions'])
            ->findOrFail($id);
        $officers = Officer::with('user:id,name')->get();
        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view('pages.penggajian.payroll_setting.payroll_setting', compact('setting', 'officers', 'units', 'components', 'deductions'))->with('show', true);
    }

    /**
     * Halaman edit payroll setting.
     */
    public function edit($id)
    {
        $setting = PayrollSetting::with(['components', 'deductions'])->findOrFail($id);
        $units = Unit::all();
        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();
        $officers = Officer::with('user:id,name')->get();

        return view('pages.penggajian.payroll_setting.payroll_setting', compact(
            'setting',
            'units',
            'components',
            'deductions',
            'officers'
        ));
    }

    /**
     * Hapus payroll setting.
     */
    public function destroy($id)
    {
        try {
            // Cari data payroll_setting berdasarkan id
            $setting = PayrollSetting::findOrFail($id);

            // Hapus data
            $setting->delete();

            // Kembali ke halaman index dengan pesan sukses
            return redirect()->route('payroll_settings.index')
                ->with('success', 'Data payroll berhasil dihapus.');
        } catch (\Throwable $e) {
            // Jika terjadi error (misalnya data tidak ditemukan)
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }



    /**
     * Ambil data payroll berdasarkan officer (guru/staff).
     * Jika tidak ada payroll_setting, ambil dari master components & deductions.
     */
    public function fetch($officerId)
    {
        $officer = Officer::with('position')->find($officerId);

        if (!$officer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru/staff tidak ditemukan.',
            ], 404);
        }

        $setting = PayrollSetting::with(['components', 'deductions'])
            ->where('officers_id', $officerId)
            ->first();

        if ($setting) {
            $components = $setting->components->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'value' => $c->pivot->value,
                ];
            });

            $deductions = $setting->deductions->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'value' => $d->pivot->value,
                ];
            });

            return response()->json([
                'status' => 'found',
                'position_name' => $officer->position->name ?? '-',
                'components' => $components,
                'deductions' => $deductions,
            ]);
        }

        // Default ambil master data
        $defaultComponents = PayrollComponents::select('id', 'name', 'price as value')->get();
        $defaultDeductions = PayrollDeductions::select('id', 'name', 'price as value')->get();

        return response()->json([
            'status' => 'default',
            'position_name' => $officer->position->name ?? '-',
            'components' => $defaultComponents,
            'deductions' => $defaultDeductions,
        ]);
    }

    /**
     * Ambil officer berdasarkan unit.
     */
    public function getByUnit($unit_id)
    {
        $officers = Officer::where('unit_id', $unit_id)
            ->with('user:id,name')
            ->get(['id', 'user_id']);

        return response()->json($officers);
    }

    /**
     * Ambil semua komponen gaji (untuk dropdown dinamis).
     */
    public function getComponents()
    {
        $components = PayrollComponents::select('id', 'name', 'price')->get();
        return response()->json($components);
    }
}

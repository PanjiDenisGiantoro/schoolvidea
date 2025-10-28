<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\PayrollSetting;
use App\Models\PayrollComponents;
use App\Models\PayrollDeductions;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'Jabatan', // Tambahkan kolom Jabatan di headers
            'Aksi'
        ];

        // PERBAIKAN: Tambahkan 'officer.position'
        $settings = PayrollSetting::with(['unit', 'officer.user', 'officer.position'])
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

        // PERBAIKAN: Tambahkan 'position' pada eager load Officer
        $officers = Officer::with(['user:id,name', 'position'])->get();

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
        // ... (Logika validasi dan penyimpanan tidak berubah)
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
            Log::error('Payroll Setting Store Error: ' . $e->getMessage());
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
        // PERBAIKAN: Tambahkan 'officer.position'
        $setting = PayrollSetting::with(['unit', 'officer.user', 'officer.position', 'components', 'deductions'])
            ->findOrFail($id);

        $data_period = PayrollSetting::findOrFail($id);
        $startMonth = $data_period->start_month;
        $startYear = $data_period->start_year;
        $billingPeriod = $data_period->billing_period ?? 1;
        $period_awal = \Carbon\Carbon::createFromDate($startYear, $startMonth, 1);
        $period_details = collect(range(0, $billingPeriod - 1))->map(function ($i) use ($period_awal) {
            $period = $period_awal->copy()->addMonth($i);
            return [
                'bulan' => $period->translatedFormat('F'),
                'tahun' => $period->year
            ];
        });

        // PERBAIKAN: Tambahkan 'position' pada eager load Officer
        $officers = Officer::with(['user:id,name', 'position'])->get();
        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view('pages.penggajian.payroll_setting.payroll_setting', compact('setting', 'officers', 'units', 'components', 'deductions', 'period_details'))->with('show', true);
    }

    /**
     * Halaman edit payroll setting.
     */
    public function edit($id)
    {
        // PERBAIKAN: Tambahkan 'officer.position'
        $setting = PayrollSetting::with(['officer.position', 'components', 'deductions'])->findOrFail($id);
        $units = Unit::all();
        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        // PERBAIKAN: Tambahkan 'position' pada eager load Officer
        $officers = Officer::with(['user:id,name', 'position'])->get();

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
            Log::error('Payroll Setting Destroy Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Ambil data payroll berdasarkan officer (guru/staff).
     */
    public function fetch($officerId)
    {
        // Logika ini sudah benar karena memuat 'position'
        $officer = Officer::with(['position'])->find($officerId);

        if (!$officer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data guru/staff tidak ditemukan.',
            ], 404);
        }

        $setting = PayrollSetting::with(['components', 'deductions'])
            ->where('officers_id', $officerId)
            ->first();

        // Menggunakan optional chaining (?->) untuk akses data yang aman
        $positionName = $officer->position?->positions_name ?? 'Tidak ada jabatan';

        $data = [
            'status' => $setting ? 'found' : 'default',
            'position_id' => $officer->position_id,
            'positions_name' => $positionName,
        ];

        if ($setting) {
            // Jika setting ditemukan, ambil komponen dan potongan dari relasi many-to-many
            $data['components'] = $setting->components->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'value' => $c->pivot->value,
                ];
            });

            $data['deductions'] = $setting->deductions->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'value' => $d->pivot->value,
                ];
            });
        } else {
            // Jika setting tidak ditemukan, ambil data default
            $data['components'] = PayrollComponents::select('id', 'name', 'price as value')->get();
            $data['deductions'] = PayrollDeductions::select('id', 'name', 'price as value')->get();
        }

        return response()->json($data);
    }

    /**
     * Ambil officer berdasarkan unit.
     */
    public function getByUnit($unit_id)
    {
        $officers = Officer::whereHas('unit', function ($query) use ($unit_id) {
            $query->where('id', $unit_id);
        })
            // PERBAIKAN: Tambahkan 'position' untuk form
            ->with(['user:id,name', 'position'])
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

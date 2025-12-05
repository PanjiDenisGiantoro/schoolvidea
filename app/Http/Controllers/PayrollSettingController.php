<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\PayrollComponents;
use App\Models\PayrollDeductions;
use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
            'Jabatan',
            'Aksi',
        ];

        // Filter settings berdasarkan user access
        $query = PayrollSetting::with([
            'unit',
            'officer.user',
            'officer.position',
        ]);

        if (Auth::user()->unit_id) {
            $query->where('units_id', Auth::user()->unit_id);
        }

        $settings = $query->orderBy('created_at', 'desc')->get();

        return view(
            'pages.penggajian.payroll_setting.index',
            compact('settings', 'headers'),
        );
    }

    /**
     * Halaman form create payroll.
     */
    public function create()
    {
        // Filter units berdasarkan user access
        if (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->get();
            $officers = Officer::with(['user:id,name', 'position'])
                ->where('unit_id', Auth::user()->unit_id)
                ->get();
        } else {
            $units = Unit::all();
            $officers = Officer::with(['user:id,name', 'position'])->get();
        }

        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view(
            'pages.penggajian.payroll_setting.payroll_setting',
            compact('units', 'components', 'deductions', 'officers'),
        );
    }

    // Simpan atau update data payroll.
    public function store(Request $request)
    {
        $validated = $request->validate([
            'units_id' => 'required|exists:units,id',
            'officers_id' => [
                'required', 'exists:officers,id',
                Rule::unique('payroll_settings', 'officers_id'),
            ],
            'teaching_hours' => 'nullable|numeric',
            'teaching_hours_total' => 'nullable|numeric',
            'salary' => 'nullable|numeric',

            'transport_allowance' => 'nullable|numeric',
            'meal_allowance' => 'nullable|numeric',
            'staff_allowance' => 'nullable|numeric',
            'other_allowance' => 'nullable|numeric',

            'billing_period' => 'required|integer',
            'start_month' => 'required|integer',
            'start_year' => 'required|integer',

            'components_id' => 'array',
            'component_value' => 'array',
            'deductions_id' => 'array',
            'deduction_value' => 'array',
            'deduction_type' => 'array',
        ], [
            'officers_id.unique' => 'Guru & Staff Sudah Terdaftar!',
            'officers_id.exists' => 'Guru & Staff Tidak Ditemukan!',
            'officers_id.required' => 'Guru & Staff Wajib Dipilih!',
        ]);

        try {
            Log::info('PAYROLL DEBUG START', $validated);

            if ($request->teaching_hours_total !== null) {
                $validated['teaching_hours_total'] = $request->teaching_hours_total;
                $validated['teaching_hours'] = null;
            } elseif ($request->teaching_hours !== null) {
                $validated['teaching_hours_total'] = null;
                $validated['teaching_hours'] = $request->teaching_hours;
            } else {
                $validated['teaching_hours_total'] = null;
                $validated['teaching_hours'] = null;
            }
            // === Simpan payroll_settings ===
            $payrollSetting = PayrollSetting::updateOrCreate(
                [
                    'units_id' => $validated['units_id'],
                    'officers_id' => $validated['officers_id'],
                    'type' => 'gaji',
                ],
                $validated
            );

            // === Sync Komponen ===
            $componentsData = [];
            $totalComponentValue = 0;

            if ($request->filled('components_id')) {
                foreach ($request->components_id as $i => $compId) {
                    $value = $request->component_value[$i] ?? 0;
                    $totalComponentValue += $value;
                    $componentsData[$compId] = ['value' => $value];
                }
                $payrollSetting->components()->sync($componentsData);
            }

            // === Sync Potongan ===
            $deductionsData = [];
            $totalDeductions = 0;

            if ($request->filled('deductions_id')) {
                foreach ($request->deductions_id as $i => $deductId) {
                    $value = $request->deduction_value[$i] ?? 0;
                    $type = $request->deduction_type[$i] ?? '';
                    $totalDeductions += $value;
                    $deductionsData[$deductId] = ['value' => $value, 'type' => $type];
                }
                $payrollSetting->deductions()->sync($deductionsData);
            }

            // === Hitung allowance & deduction ===
            // $allowanceTotal =
            //     ($payrollSetting->transport_allowance ?? 0) +
            //     ($payrollSetting->meal_allowance ?? 0) +
            //     ($payrollSetting->other_allowance ?? 0);

            // $deductions = $payrollSetting->deductions;
            // $salaryPerUnit = $request->salary ?? 0;
            // $tht = $validated['teaching_hours_total'] ?? $validated['teaching_hours'];
            // $baseSalary1 = ($salaryPerUnit * $tht) + ($allowanceTotal ?? 0) + ($totalComponentValue ?? 0);
            $totalDeductions = 0;

            // if ($deductions->isEmpty()) {
            //     $totalDeductions = 0;
            // } else {
            //     foreach ($deductions as $deduction) {
            //         $type = $deduction->pivot->type;
            //         $value = $deduction->pivot->value;

            //         if ($type === 'nominal') {
            //             $totalDeductions += $value;
            //         } elseif ($type === 'persen') {
            //             $nominalPotongan = ($value / 100) * $baseSalary1;
            //             $totalDeductions += $nominalPotongan;
            //         }
            //     }
            // }

            // === Variabel inti ===
            $billingPeriod = (int) $validated['billing_period'];
            $startMonth = (int) $validated['start_month'];
            $startYear = (int) $validated['start_year'];
            $rows = [];
            $backup = $payrollSetting->load(['components', 'deductions'])->toArray();
            $backup['total_deductions'] = $totalDeductions;

            // === Loop komponen → bulan ===

            for ($i = 0; $i < $billingPeriod; $i++) {
                // Hitung bulan berjalan
                $month = $startMonth + $i;
                $year = $startYear;

                if ($month > 12) {
                    $month -= 12;
                    $year++;
                }

                // Format bulan
                $paymentMonth = sprintf('%02d-%04d', $month, $year);

                // Gaji pokok (per komponen dihitung sama)
                $basicSalary = ($request->teaching_hours ?? $request->teaching_hours_total) * ($request->salary ?? 0);

                // Total pendapatan per komponen
                $totalEarnings = $basicSalary + $totalComponentValue;

                // Total bersih
                $netPayment = $totalEarnings - $totalDeductions;

                $rows[] = [
                    'unit_id' => $validated['units_id'],
                    'officer_id' => $validated['officers_id'],
                    'payroll_setting_id' => $payrollSetting->id,
                    'type' => 'gaji',

                    'teaching_hour_week' => $request->teaching_hours ?? 0,
                    'teaching_hour_month' => $request->teaching_hours_total ?? 0,

                    'total_earnings' => $totalEarnings,
                    'total_deductions' => $totalDeductions,
                    'net_payment' => $netPayment,

                    'payment_month' => $month,
                    'payment_year' => $year,
                    'notes' => null,
                    'status' => 'pending',
                    'details' => $backup,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach ($rows as $data) {
                PayrollPayment::create($data);
            }

            // Insert sekali (super cepat)
            // PayrollPayment::insert($rows);

            return redirect()
                ->route('payroll_settings.index')
                ->with('success', 'Data Berhasil Disimpan');
        } catch (\Throwable $e) {
            Log::error('PAYROLL ERROR: ' . $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'units_id' => 'required|exists:units,id',
            'officers_id' => 'required|exists:officers,id',
            'teaching_hours' => 'nullable|numeric',
            'teaching_hours_total' => 'nullable|numeric',
            'salary' => 'nullable|numeric',
            'type' => 'required|string',

            'transport_allowance' => 'nullable|numeric',
            'meal_allowance' => 'nullable|numeric',
            'staff_allowance' => 'nullable|numeric',
            'other_allowance' => 'nullable|numeric',

            'billing_period' => 'required|integer',
            'start_month' => 'required|integer',
            'start_year' => 'required|integer',

            'components_id' => 'array',
            'component_value' => 'array',
            'deductions_id' => 'array',
            'deduction_value' => 'array',
            'deduction_type' => 'array',
        ]);

        try {
            DB::beginTransaction();

            // === 1️⃣ Update payroll setting master ===
            Log::info('Validated update data:', $validated);
            if ($request->teaching_hours_total !== null) {
                $validated['teaching_hours_total'] = $request->teaching_hours_total;
                $validated['teaching_hours'] = null;
            } elseif ($request->teaching_hours !== null) {
                $validated['teaching_hours_total'] = $request->teaching_hours;
                $validated['teaching_hours'] = null;
            } else {
                $validated['teaching_hours_total'] = null;
            }
            // dd([
            //    'total' => $request->teaching_hours_total,
            //    'mgg' => $request->teaching_hours,
            // ]);
            $payrollSetting = PayrollSetting::findOrFail($id);
            $payrollSetting->update($validated);

            // === 2️⃣ Sync Komponen ===
            $payrollSetting->components()->detach();
            $componentsData = [];
            $totalComponentValue = 0;
            if ($request->has('components_id')) {
                foreach ($request->components_id as $i => $compId) {
                    $value = $request->component_value[$i] ?? 0;
                    $totalComponentValue += $value;
                    $componentsData[$compId] = ['value' => $value];
                }
                $payrollSetting->components()->sync($componentsData);
            }

            // === 3️⃣ Sync Potongan ===
            $payrollSetting->deductions()->detach();
            $deductionsData = [];
            $totalDeductions = 0;
            if ($request->has('deductions_id')) {
                foreach ($request->deductions_id as $i => $dedId) {
                    $value = $request->deduction_value[$i] ?? 0;
                    $type = $request->deduction_type[$i] ?? '';
                    $totalDeductions += $value;
                    $deductionsData[$dedId] = ['value' => $value, 'type' => $type];
                }
                $payrollSetting->deductions()->sync($deductionsData);
            }

            // $allowanceTotal =
            //     ($payrollSetting->transport_allowance ?? 0) +
            //     ($payrollSetting->meal_allowance ?? 0) +
            //     ($payrollSetting->other_allowance ?? 0);

            // === 4️⃣ Hitung gaji SEKALI SAJA di luar loop ===

            $basicSalary = ($request->teaching_hours ?? $request->teaching_hours_total) * ($request->salary ?? 0);

            // $deductions = $payrollSetting->deductions;
            $totalDeductions1 = 0;

            // if ($deductions->isEmpty()) {
            //     $totalDeductions1 = 0;
            // } else {
            //     foreach ($deductions as $deduction) {
            //         $type = (! empty($deduction->pivot->type))
            //             ? $deduction->pivot->type
            //             : $deduction->type;

            //         $value = $deduction->pivot->value;

            //         // dump([$type, $value, $deduction->type]);

            //         if ($type === 'nominal') {
            //             $totalDeductions1 += $value;
            //             // dump(['total nominal' => $value]);
            //         } elseif ($type === 'persen') {
            //             $nominalPotongan = ($value / 100) * $basicSalary;
            //             $totalDeductions1 += $nominalPotongan;
            //             // dump(['total persen' => $nominalPotongan, 'basic salary' => $basicSalary]);
            //         }
            //     }
            // }
            // dd($totalDeductions);
            $totalEarnings = $basicSalary + $totalComponentValue;
            $netPayment = $totalEarnings - $totalDeductions1;

            // === 5️⃣ Tentukan periode yang diinginkan ===
            $billingPeriod = (int) $validated['billing_period'];
            $startMonth = (int) $validated['start_month'];
            $startYear = (int) $validated['start_year'];

            // Simpan periode yang akan diupdate/dibuat
            $targetPeriods = [];
            $backup = $payrollSetting->load(['components', 'deductions'])->toArray();
            $backup['total_deductions'] = $totalDeductions;

            for ($i = 0; $i < $billingPeriod; $i++) {
                $month = $startMonth + $i;
                $year = $startYear;

                // Handle rollover tahun dengan benar
                if ($month > 12) {
                    $year += floor(($month - 1) / 12);
                    $month = ($month - 1) % 12 + 1;
                }

                $targetPeriods[] = ['month' => $month, 'year' => $year];
            }

            // === 6️⃣ Hapus payment yang TIDAK termasuk dalam periode baru ===
            PayrollPayment::where('payroll_setting_id', $payrollSetting->id)
                ->where('status', 'pending')
                ->whereNotIn('payment_month', array_column($targetPeriods, 'month'))
                ->whereNotIn('payment_year', array_column($targetPeriods, 'year'))
                ->delete();

            // === 7️⃣ Update atau buat payment untuk setiap periode ===
            foreach ($targetPeriods as $period) {
                $payment = PayrollPayment::firstOrNew([
                    'payroll_setting_id' => $payrollSetting->id,
                    'payment_month' => $period['month'],
                    'payment_year' => $period['year'],
                ]);

                if ($payment && $payment->status === 'paid') {
                    continue;
                }

                $payment->fill([
                    'unit_id' => $validated['units_id'],
                    'officer_id' => $validated['officers_id'],
                    'type' => 'gaji',

                    'teaching_hour_week' => $request->teaching_hours ?? 0,
                    'teaching_hour_month' => $request->teaching_hours_total ?? 0,

                    'total_earnings' => $totalEarnings,
                    'total_deductions' => $totalDeductions1,
                    'net_payment' => $netPayment,
                    'details' => $backup,

                    'notes' => null,
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);

                if (! $payment->exists) {
                    $payment->created_at = now();
                }

                $payment->save();
            }

            DB::commit();

            // Response JSON untuk request AJAX/API
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Berhasil Disimpan.',
                    'data' => $payrollSetting,
                ], 200);
            }

            return redirect()
                ->route('payroll_settings.index')
                ->with('success', 'Data Berhasil Disimpan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('PAYROLL UPDATE VALIDATION ERROR: ' . $e->getMessage());

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('PAYROLL UPDATE MODEL NOT FOUND: ' . $e->getMessage());

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'error' => $e->getMessage(),
                ], 404);
            }

            return back()->with('error', 'Data tidak ditemukan');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PAYROLL UPDATE ERROR: ' . $e->getMessage());

            // Response JSON untuk error umum
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem',
                    'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
                ], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan detail satu payroll setting.
     */
    public function show($id)
    {
        // Filter units berdasarkan user access
        if (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->get();
            $officers = Officer::with(['user:id,name', 'position'])
                ->where('unit_id', Auth::user()->unit_id)
                ->get();
        } else {
            $units = Unit::all();
            $officers = Officer::with(['user:id,name', 'position'])->get();
        }

        $setting = PayrollSetting::with([
            'unit',
            'officer.user',
            'officer.position',
            'components',
            'deductions',
        ])->findOrFail($id);

        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view(
            'pages.penggajian.payroll_setting.payroll_setting',
            compact('setting', 'officers', 'units', 'components', 'deductions'),
        )->with('show', true);
    }

    /**
     * Halaman edit payroll setting.
     */
    public function edit($id)
    {
        $setting = PayrollSetting::with([
            'officer.position',
            'components',
            'deductions',
        ])->findOrFail($id);

        // Filter units berdasarkan user access
        if (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->get();
            $officers = Officer::with(['user:id,name', 'position'])
                ->where('unit_id', Auth::user()->unit_id)
                ->get();
        } else {
            $units = Unit::all();
            $officers = Officer::with(['user:id,name', 'position'])->get();
        }

        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view(
            'pages.penggajian.payroll_setting.payroll_setting',
            compact('setting', 'units', 'components', 'deductions', 'officers'),
        );
    }

    /**
     * Hapus payroll setting.
     */
    public function destroy($id)
    {
        try {
            // Cari data payroll_setting berdasarkan id
            $setting = PayrollSetting::findOrFail($id);

            // Hapus hanya payroll payments dengan status 'pending'
            $setting->payments() // pastikan relasi di model PayrollSetting ada: hasMany(PayrollPayment)
                ->where('status', 'pending')
                ->delete();

            // Hapus setting
            $setting->delete();

            return redirect()
                ->route('payroll_settings.index')
                ->with('success', 'Data payroll berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Payroll Setting Destroy Error: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Ambil data payroll berdasarkan officer (guru/staff).
     */
    public function fetch($officerId)
    {
        $officer = Officer::with(['position'])->find($officerId);

        if (! $officer) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Data guru/staff tidak ditemukan.',
                ],
                404,
            );
        }

        $setting = PayrollSetting::with(['components', 'deductions'])
            ->where('officers_id', $officerId)
            ->first();

        $positionName =
            $officer->position?->positions_name ?? 'Tidak ada jabatan';

        $data = [
            'status' => $setting ? 'found' : 'default',
            'position_id' => $officer->position_id,
            'positions_name' => $positionName,
        ];

        if ($setting) {
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
                    'type' => $d->type,
                ];
            });
        } else {
            $data['components'] = PayrollComponents::select(
                'id',
                'name',
                'price as value',
            )->get();
            $data['deductions'] = PayrollDeductions::select(
                'id',
                'name',
                'price as value',
                'type'
            )->get();
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
            ->with(['user:id,name', 'position'])
            ->get(['id', 'user_id']);

        return response()->json($officers);
    }

    public function getOfficerDetail($officerId)
    {
        $officer = Officer::with([
            'position:id,positions_name',
            'unit:id,nama_unit',
        ])->find($officerId);

        return response()->json([
            'officer_name' => $officer->name ?? '-',
            'officer_unit' => $officer->unit?->nama_unit ?? '-',
            'officer_nip' => $officer->nip ?? '-',
            'officer_no_hp' => $officer->no_hp ?? '-',
            'officer_foto' => $officer->image ?? '',
            'officer_position' => $officer->position?->positions_name ?? 'Tidak ada Jabatan',
            'officer_bank' => $officer->bank ?? '-',
            'officer_norek' => $officer->no_rekening ?? '-',
            'officer_va' => $officer->va_guru ?? '-',
        ]);
    }

    public function getByOfficer($officerId)
    {
        $settings = PayrollSetting::where('officers_id', $officerId)
            ->with(['components.component'])->get();
        $components = $settings
            ->flatMap(fn ($s) => $s->components)
            ->pluck('component')
            ->unique('id')
            ->values()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]);
        $periodes = $settings->pluck('billing_period')->unique()->values();
        $years = $settings->pluck('start_year')->unique()->values();

        return response()->json([
            'components' => $components,
            'periodes' => $periodes,
            'years' => $years,
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\PayrollSetting;
use App\Models\PayrollComponents;
use App\Models\PayrollDeductions;
use App\Models\PayrollPayment;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PayrollSettingController extends Controller
{
    /**
     * Halaman utama setting payroll.
     */
    public function index()
    {
        $headers = [
            "No",
            "Unit",
            "Nama Guru & Staff",
            "Jabatan",
            "Aksi",
        ];

        // Filter settings berdasarkan user access
        $query = PayrollSetting::with([
            "unit",
            "officer.user",
            "officer.position",
        ]);

        if (Auth::user()->unit_id) {
            $query->where('units_id', Auth::user()->unit_id);
        }

        $settings = $query->orderBy("created_at", "desc")->get();

        return view(
            "pages.penggajian.payroll_setting.index",
            compact("settings", "headers"),
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
            $officers = Officer::with(["user:id,name", "position"])
                ->where('unit_id', Auth::user()->unit_id)
                ->get();
        } else {
            $units = Unit::all();
            $officers = Officer::with(["user:id,name", "position"])->get();
        }

        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view(
            "pages.penggajian.payroll_setting.payroll_setting",
            compact("units", "components", "deductions", "officers"),
        );
    }

    /**
     * Simpan atau update data payroll.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            "units_id" => "required|exists:units,id",
            "officers_id" => "required|exists:officers,id",
            "teaching_hours" => "nullable|numeric",
            "teaching_hours_total" => "nullable|numeric",
            "salary" => "nullable|numeric",

            "transport_allowance" => "nullable|numeric",
            "meal_allowance" => "nullable|numeric",
            "staff_allowance" => "nullable|numeric",
            "other_allowance" => "nullable|numeric",

            "billing_period" => "required|integer",
            "start_month" => "required|integer",
            "start_year" => "required|integer",

            "components_id" => "array",
            "component_value" => "array",
            "deductions_id" => "array",
            "deduction_value" => "array",
        ]);


        try {

            Log::info("PAYROLL DEBUG START", $validated);

            // === Simpan payroll_settings ===
            $payrollSetting = PayrollSetting::updateOrCreate(
                [
                    "units_id" => $validated["units_id"],
                    "officers_id" => $validated["officers_id"],
                    "type" => "gaji",
                ],
                $validated
            );

            // === Sync Komponen ===
            $componentsData = [];
            $totalComponentValue = 0;

            if ($request->filled("components_id")) {
                foreach ($request->components_id as $i => $compId) {
                    $value = $request->component_value[$i] ?? 0;
                    $totalComponentValue += $value;
                    $componentsData[$compId] = ["value" => $value];
                }
                $payrollSetting->components()->sync($componentsData);
            }

            // === Sync Potongan ===
            $deductionsData = [];
            $totalDeductions = 0;

            if ($request->filled("deductions_id")) {
                foreach ($request->deductions_id as $i => $deductId) {
                    $value = $request->deduction_value[$i] ?? 0;
                    $totalDeductions += $value;
                    $deductionsData[$deductId] = ["value" => $value];
                }
                $payrollSetting->deductions()->sync($deductionsData);
            }


            // === Hitung allowance & deduction ===
            $allowanceTotal =
                ($payrollSetting->transport_allowance ?? 0) +
                ($payrollSetting->meal_allowance ?? 0) +
                ($payrollSetting->other_allowance ?? 0);

            $deductionsTotal = $payrollSetting->deductions->sum('pivot.value');

            // === Variabel inti ===
            $billingPeriod = (int) $validated["billing_period"];
            $startMonth     = (int) $validated["start_month"];
            $startYear      = (int) $validated["start_year"];
            $rows           = [];

            // === Loop komponen → bulan ===




            for ($i = 0; $i < $billingPeriod; $i++) {

                // Hitung bulan berjalan
                $month = $startMonth + $i;
                $year  = $startYear;

                if ($month > 12) {
                    $month -= 12;
                    $year++;
                }

                // Format bulan
                $paymentMonth = sprintf("%02d-%04d", $month, $year);

                // Gaji pokok (per komponen dihitung sama)
                $basicSalary = ($request->teaching_hours ?? 0) * ($request->salary ?? 0);

                // Total pendapatan per komponen
                $totalEarnings = $basicSalary + $totalComponentValue;

                // Total bersih
                $netPayment = $totalEarnings - $totalDeductions;

                $rows[] = [
                    "unit_id"            => $validated["units_id"],
                    "officer_id"         => $validated["officers_id"],
                    "payroll_setting_id" => $payrollSetting->id,
                    "type"       => 'gaji',

                    "teaching_hour_week"  => $request->teaching_hours ?? 0,
                    "teaching_hour_month" => $request->teaching_hours_total ?? 0,

                    "total_earnings"     => $totalEarnings,
                    "total_deductions"   => $deductionsTotal,
                    "net_payment"        => $netPayment,

                    "payment_month"      => $month,
                    "payment_year"       => $year,
                    "notes"              => null,
                    "status"             => "pending",

                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            }


            // Insert sekali (super cepat)
            PayrollPayment::insert($rows);

            return redirect()
                ->route("payroll_settings.index")
                ->with("success", "Data Berhasil Disimpan");

        } catch (\Throwable $e) {
            Log::error("PAYROLL ERROR: " . $e->getMessage());
            return back()->with("error", $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            "units_id" => "required|exists:units,id",
            "officers_id" => "required|exists:officers,id",
            "teaching_hours" => "nullable|numeric",
            "teaching_hours_total" => "nullable|numeric",
            "salary" => "nullable|numeric",

            "transport_allowance" => "nullable|numeric",
            "meal_allowance" => "nullable|numeric",
            "staff_allowance" => "nullable|numeric",
            "other_allowance" => "nullable|numeric",

            "billing_period" => "required|integer",
            "start_month" => "required|integer",
            "start_year" => "required|integer",

            "components_id" => "array",
            "component_value" => "array",
            "deductions_id" => "array",
            "deduction_value" => "array",
        ]);

        try {
            DB::beginTransaction();

            // === 1️⃣ Update payroll setting master ===
            $payrollSetting = PayrollSetting::findOrFail($id);
            $payrollSetting->update($validated);

            // === 2️⃣ Sync Komponen ===
            $payrollSetting->components()->detach();
            $componentsData = [];
            $totalComponentValue = 0;
            if ($request->has("components_id")) {
                foreach ($request->components_id as $i => $compId) {
                    $value = $request->component_value[$i] ?? 0;
                    $totalComponentValue += $value;
                    $componentsData[$compId] = ["value" => $value];
                }
                $payrollSetting->components()->sync($componentsData);
            }

            // === 3️⃣ Sync Potongan ===
            $payrollSetting->deductions()->detach();
            $deductionsData = [];
            $totalDeductions = 0;
            if ($request->has("deductions_id")) {
                foreach ($request->deductions_id as $i => $dedId) {
                    $value = $request->deduction_value[$i] ?? 0;
                    $totalDeductions += $value;
                    $deductionsData[$dedId] = ["value" => $value];
                }
                $payrollSetting->deductions()->sync($deductionsData);
            }

            $deductionsTotal = $payrollSetting->deductions->sum('pivot.value');

            // === 4️⃣ Hitung gaji SEKALI SAJA di luar loop ===
            $basicSalary = ($request->teaching_hours ?? 0) * ($request->salary ?? 0);
            $totalEarnings = $basicSalary + $totalComponentValue;
            $netPayment = $totalEarnings - $deductionsTotal;

            // === 5️⃣ Tentukan periode yang diinginkan ===
            $billingPeriod = (int) $validated["billing_period"];
            $startMonth = (int) $validated["start_month"];
            $startYear = (int) $validated["start_year"];

            // Simpan periode yang akan diupdate/dibuat
            $targetPeriods = [];

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
                    'status' => 'pending',
                ]);

                $payment->fill([
                    "unit_id" => $validated["units_id"],
                    "officer_id" => $validated["officers_id"],
                    "type" => 'gaji',

                    "teaching_hour_week" => $request->teaching_hours ?? 0,
                    "teaching_hour_month" => $request->teaching_hours_total ?? 0,

                    "total_earnings" => $totalEarnings,
                    "total_deductions" => $deductionsTotal,
                    "net_payment" => $netPayment,

                    "notes" => null,
                    "status" => "pending",
                    "updated_at" => now(),
                ]);

                if (!$payment->exists) {
                    $payment->created_at = now();
                }

                $payment->save();
            }

            DB::commit();

            return redirect()
                ->route("payroll_settings.index")
                ->with("success", "Data Berhasil Disimpan.");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("PAYROLL UPDATE ERROR: " . $e->getMessage());
            return back()->with("error", $e->getMessage());
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
            $officers = Officer::with(["user:id,name", "position"])
                ->where('unit_id', Auth::user()->unit_id)
                ->get();
        } else {
            $units = Unit::all();
            $officers = Officer::with(["user:id,name", "position"])->get();
        }

        $setting = PayrollSetting::with([
            "unit",
            "officer.user",
            "officer.position",
            "components",
            "deductions",
        ])->findOrFail($id);

        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view(
            "pages.penggajian.payroll_setting.payroll_setting",
            compact("setting", "officers", "units", "components", "deductions"),
        )->with("show", true);
    }

    /**
     * Halaman edit payroll setting.
     */
    public function edit($id)
    {
        $setting = PayrollSetting::with([
            "officer.position",
            "components",
            "deductions",
        ])->findOrFail($id);

        // Filter units berdasarkan user access
        if (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->get();
            $officers = Officer::with(["user:id,name", "position"])
                ->where('unit_id', Auth::user()->unit_id)
                ->get();
        } else {
            $units = Unit::all();
            $officers = Officer::with(["user:id,name", "position"])->get();
        }

        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        return view(
            "pages.penggajian.payroll_setting.payroll_setting",
            compact("setting", "units", "components", "deductions", "officers"),
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

            // Hapus data
            $setting->delete();

            // Kembali ke halaman index dengan pesan sukses
            return redirect()
                ->route("payroll_settings.index")
                ->with("success", "Data payroll berhasil dihapus.");
        } catch (\Throwable $e) {
            // Jika terjadi error (misalnya data tidak ditemukan)
            Log::error("Payroll Setting Destroy Error: " . $e->getMessage());
            return redirect()
                ->back()
                ->with("error", "Gagal menghapus data: " . $e->getMessage());
        }
    }

    /**
     * Ambil data payroll berdasarkan officer (guru/staff).
     */
    public function fetch($officerId)
    {
        // Logika ini sudah benar karena memuat 'position'
        $officer = Officer::with(["position"])->find($officerId);

        if (!$officer) {
            return response()->json(
                [
                    "status" => "error",
                    "message" => "Data guru/staff tidak ditemukan.",
                ],
                404,
            );
        }

        $setting = PayrollSetting::with(["components", "deductions"])
            ->where("officers_id", $officerId)
            ->first();

        // Menggunakan optional chaining (?->) untuk akses data yang aman
        $positionName =
            $officer->position?->positions_name ?? "Tidak ada jabatan";

        $data = [
            "status" => $setting ? "found" : "default",
            "position_id" => $officer->position_id,
            "positions_name" => $positionName,
        ];

        if ($setting) {
            // Jika setting ditemukan, ambil komponen dan potongan dari relasi many-to-many
            $data["components"] = $setting->components->map(function ($c) {
                return [
                    "id" => $c->id,
                    "name" => $c->name,
                    "value" => $c->pivot->value,
                ];
            });

            $data["deductions"] = $setting->deductions->map(function ($d) {
                return [
                    "id" => $d->id,
                    "name" => $d->name,
                    "value" => $d->pivot->value,
                ];
            });
        } else {
            // Jika setting tidak ditemukan, ambil data default
            $data["components"] = PayrollComponents::select(
                "id",
                "name",
                "price as value",
            )->get();
            $data["deductions"] = PayrollDeductions::select(
                "id",
                "name",
                "price as value",
            )->get();
        }

        return response()->json($data);
    }

    /**
     * Ambil officer berdasarkan unit.
     */
    public function getByUnit($unit_id)
    {
        $officers = Officer::whereHas("unit", function ($query) use ($unit_id) {
            $query->where("id", $unit_id);
        })
            // PERBAIKAN: Tambahkan 'position' untuk form
            ->with(["user:id,name", "position"])
            ->get(["id", "user_id"]);

        return response()->json($officers);
    }


    public function getOfficerDetail($officerId)
    {
        $officer = Officer::with([
            'position:id,positions_name',
            'unit:id,nama_unit'
        ])->find($officerId);

        return response()->json([
            'officer_name' => $officer->name ?? "-",
            'officer_unit' => $officer->unit?->nama_unit ?? "-",
            'officer_nip' => $officer->nip ?? "-",
            'officer_no_hp' => $officer->no_hp ?? "-",
            'officer_foto' => $officer-> image ?? "",
            'officer_position' => $officer->position?->positions_name ?? "Tidak ada Jabatan"
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
        $components = PayrollComponents::select("id", "name", "price")->get();
        return response()->json($components);
    }
}

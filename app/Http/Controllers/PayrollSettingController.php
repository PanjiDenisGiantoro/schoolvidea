<?php

namespace App\Http\Controllers;

use App\Models\Officer;
use App\Models\PayrollSetting;
use App\Models\PayrollComponents;
use App\Models\PayrollDeductions;
use App\Models\PayrollPayment;
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
            "No",
            "Unit",
            "Nama Guru & Staff",
            "Jabatan", // Tambahkan kolom Jabatan di headers
            "Aksi",
        ];

        // PERBAIKAN: Tambahkan 'officer.position'
        $settings = PayrollSetting::with([
            "unit",
            "officer.user",
            "officer.position",
        ])
            ->orderBy("created_at", "desc")
            ->get();

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
        $units = Unit::all();
        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        // PERBAIKAN: Tambahkan 'position' pada eager load Officer
        $officers = Officer::with(["user:id,name", "position"])->get();

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
            "communication_allowance" => "nullable|numeric",
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
                ],
                $validated
            );

            // === Sync Komponen ===
            $totalComponentValue = 0;
            if ($request->filled("components_id")) {
                $syncComponents = [];

                foreach ($request->components_id as $i => $compId) {
                    $value = $request->component_value[$i] ?? 0;
                    $totalComponentValue += $value;

                    $syncComponents[$compId] = ["value" => $value];
                }

                $payrollSetting->components()->sync($syncComponents);
            }

            // === Sync Potongan ===
            $totalDeductions = 0;
            if ($request->filled("deductions_id")) {
                $syncDeductions = [];

                foreach ($request->deductions_id as $i => $deductId) {
                    $value = $request->deduction_value[$i] ?? 0;
                    $totalDeductions += $value;

                    $syncDeductions[$deductId] = ["value" => $value];
                }

                $payrollSetting->deductions()->sync($syncDeductions);
            }


            // === Hitung allowance & deduction ===
            $allowanceTotal =
                ($payrollSetting->transport_allowance ?? 0) +
                ($payrollSetting->meal_allowance ?? 0) +
                ($payrollSetting->communication_allowance ?? 0) +
                ($payrollSetting->other_allowance ?? 0);

            $deductionsTotal = $payrollSetting->deductions->sum('pivot.value');

            // === Variabel inti ===
            $billingPeriod = (int) $validated["billing_period"];
            $startMonth     = (int) $validated["start_month"];
            $startYear      = (int) $validated["start_year"];
            $rows           = [];

            // === Loop komponen → bulan ===
            foreach ($request->components_id as $index => $compId) {

                $value = (int) ($request->component_value[$index] ?? 0);

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
                    $totalEarnings = $basicSalary + $value;

                    // Total bersih
                    $netPayment = $totalEarnings - $deductionsTotal;

                    $rows[] = [
                        "unit_id"            => $validated["units_id"],
                        "officer_id"         => $validated["officers_id"],
                        "payroll_setting_id" => $payrollSetting->id,
                        "component_id"       => $compId,

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
            }

            // Insert sekali (super cepat)
            PayrollPayment::insert($rows);

            return redirect()
                ->route("payroll_payment.index")
                ->with("success", "Data payroll berhasil disimpan.");

        } catch (\Throwable $e) {
            Log::error("PAYROLL ERROR: " . $e->getMessage());
            return back()->with("error", $e->getMessage());
        }
    }


    /**
     * Update payroll setting (gunakan logika store).
     */
public function update(Request $request, $id)
{
    $validated = $request->validate([
        // ... validasi sama
    ]);

    try {
        DB::beginTransaction();

        $payrollSetting = PayrollSetting::findOrFail($id);
        $payrollSetting->update($validated);

        // Sync komponen dan deductions (sama seperti sebelumnya)

        $deductionsTotal = $payrollSetting->deductions->sum('pivot.value');

        // Update payroll payments yang sudah ada
        $existingPayments = PayrollPayment::where('payroll_setting_id', $payrollSetting->id)->get();

        foreach ($existingPayments as $payment) {
            // Hitung ulang values
            $basicSalary = ($request->teaching_hours ?? 0) * ($request->salary ?? 0);
            $componentValue = // dapatkan value komponen yang sesuai
            $totalEarnings = $basicSalary + $componentValue;
            $netPayment = $totalEarnings - $deductionsTotal;

            $payment->update([
                'teaching_hour_week' => $request->teaching_hours ?? 0,
                'teaching_hour_month' => $request->teaching_hours_total ?? 0,
                'total_earnings' => $totalEarnings,
                'total_deductions' => $deductionsTotal,
                'net_payment' => $netPayment,
                'updated_at' => now(),
            ]);
        }

        DB::commit();

        return redirect()
            ->route("payroll_payment.index")
            ->with("success", "Data payroll berhasil diupdate.");

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
        $units = Unit::all();
        // PERBAIKAN: Tambahkan 'officer.position'
        $setting = PayrollSetting::with([
            "unit",
            "officer.user",
            "officer.position",
            "components",
            "deductions",
        ])->findOrFail($id);

        // PERBAIKAN: Tambahkan 'position' pada eager load Officer
        $officers = Officer::with(["user:id,name", "position"])->get();
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
        // PERBAIKAN: Tambahkan 'officer.position'
        $setting = PayrollSetting::with([
            "officer.position",
            "components",
            "deductions",
        ])->findOrFail($id);
        $units = Unit::all();
        $components = PayrollComponents::all();
        $deductions = PayrollDeductions::all();

        // PERBAIKAN: Tambahkan 'position' pada eager load Officer
        $officers = Officer::with(["user:id,name", "position"])->get();

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

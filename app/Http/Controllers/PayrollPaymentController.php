<?php

namespace App\Http\Controllers;

use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\PayrollComponents;
use App\Models\Officer;
use App\Models\Unit;
use App\Models\AttendanceSync;
use App\Helpers\VideaclassApiHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollPaymentController extends Controller
{
    public function index()
    {
        if (Auth::user()->yayasan_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $officerList = Officer::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $tagihanList = PayrollComponents::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
            $akunList = PayrollSetting::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
            $officerList = Officer::where('unit_id', Auth::user()->unit_id)->get();
            $tagihanList = PayrollComponents::where('unit_id', Auth::user()->unit_id)->get();
            $akunList = PayrollSetting::where('unit_id', Auth::user()->unit_id)->get();
        } else {
            $units = Unit::where('status', '1')->get();
            $officerList = Officer::all();
            $tagihanList = PayrollComponents::all();
            $akunList = PayrollSetting::all();
        }

        return view('pages.penggajian.payroll_payment.payroll_payment', compact('units', 'tagihanList', 'officerList', 'akunList'));
    }

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




    public function getPayment(Request $request)
    {
        $officerId = $request->officer_id;
        $paymentId = $request->component_id;
        $periode   = $request->period;
        $year      = $request->year;
        $status    = $request->status ?? 'draft';

        $query = PayrollPayment::with('component', 'officer.user');

        if ($officerId && $officerId !== 'all') {
            $query->where('officer_id', $officerId);
        }

        if ($paymentId && $paymentId !== 'all') {
            $query->where('component_id', $paymentId);
        }

        if ($periode && $periode !== 'all') {
            $query->where('payment_month', $periode);
        }

        if ($year) {
            $query->where('payment_year', $year);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payments = $query->get();
        $settings = PayrollSetting::where('officers_id', $officerId)->first();
        $allowances = [
            'salary' => $settings->salary ?? 0,
            'transport_allowance' => $settings->transport_allowance ?? 0,
            'meal_allowance' => $settings->meal_allowance ?? 0,
            'communication_allowance' => $settings->communication_allowance ?? 0,
            'other_allowance' => $settings->other_allowance ?? 0,
        ];
        $totalAllowance = array_sum($allowances);

        return response()->json([
            "belum_lunas"  => $payments->whereIn('status', ['draft', 'pending'])->values(),
            "sudah_lunas"  => $payments->where('status', 'paid')->values(),
            "query_sql"    => $query->toSql(),
            "bindings"     => $query->getBindings(),
            "request_debug" => $request->all(),
            "allowances" => $allowances,
            "total_allowance" => $totalAllowance
        ]);
    }



    public function getPaymentList(Request $request, $officerId)
    {
        try {
            $settings = PayrollSetting::where('officers_id', $officerId)
                ->with([
                    'components.component:id,name',
                    'deductions.deduction:id,name',
                    'officer'
                ])
                ->get();

            return response()->json([
                'settings' => $settings,
            ]);

        } catch (\Exception $e) {
            Log::error("GetPaymentList Error: " . $e->getMessage());
            return response()->json([
                'settings' => [],
            ], 500);
        }
    }

    public function getPaymentData(Request $request)
    {
        $officerId = $request->officer_id;
        $componentId = $request->payment_id; // kamu pakai setting_id tetapi isinya component_id
        $periode = $request->period;
        $year = $request->year;

        try {

            $query = PayrollPayment::with([
                'officer.user',
                'component',                 // relasi ke komponen
                'component.componentType',   // kalau nama komponen ada di tabel lain
            ])
                ->where('officer_id', $officerId)
                ->where('component_id', $componentId);

            // Filter bulan
            if ($periode !== 'all' && !empty($periode)) {
                $query->where('payment_month', $periode);
            }

            // Filter tahun
            if (!empty($year)) {
                $query->where('payment_year', $year);
            }

            $data = $query->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error("Payroll Table Error: " . $e->getMessage());
            return response()->json(['success' => false, 'data' => []]);
        }
    }

    /**
     * Ambil data presensi yang sudah disinkronisasi
     */
    public function getAttendanceData(Request $request)
    {
        try {
            $officerId = $request->officer_id;
            $unitId = $request->unit_id;

            if (!$officerId || !$unitId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Officer ID dan Unit ID diperlukan'
                ], 400);
            }

            $attendance = AttendanceSync::where('officer_id', $officerId)
                ->where('unit_id', $unitId)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data presensi belum disinkronisasi',
                    'data' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $attendance->id,
                    'presence_count' => $attendance->presence_count,
                    'absence_count' => $attendance->absence_count,
                    'is_active' => $attendance->is_active,
                    'synced_at' => $attendance->synced_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Get Attendance Data Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sinkronisasi data presensi dari Videaclass API
     */
    public function syncAttendance(Request $request)
    {
        try {
            $unitId = $request->unit_id;
            $officerId = $request->officer_id;
            $search = $request->search ?? null;

            if (!$unitId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit ID diperlukan'
                ], 400);
            }

            $videaclassApi = new VideaclassApiHelper();
            $apiResponse = $videaclassApi->syncAttendanceData($unitId, $search);

            if (!$apiResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data dari API Videaclass'
                ], 500);
            }

            // Process dan simpan data presensi ke database
            $rows = $apiResponse['rows'] ?? [];
            $syncedCount = 0;
            $errorCount = 0;
            $syncedRecords = [];

            foreach ($rows as $attendanceRecord) {
                try {
                    // Cari officer berdasarkan registered_number dari Videaclass
                    $officer = Officer::where('registered_number', $attendanceRecord['registered_number'])
                        ->where('unit_id', $unitId)
                        ->first();

                    if (!$officer) {
                        $errorCount++;
                        continue;
                    }

                    // Update atau buat record AttendanceSync
                    $sync = AttendanceSync::updateOrCreate(
                        [
                            'unit_id' => $unitId,
                            'officer_id' => $officer->id,
                            'videaclass_id' => $attendanceRecord['id'],
                        ],
                        [
                            'registered_number' => $attendanceRecord['registered_number'],
                            'fullname' => $attendanceRecord['fullname'],
                            'presence_count' => $attendanceRecord['presence_count'],
                            'absence_count' => $attendanceRecord['absence_count'],
                            'is_active' => $attendanceRecord['is_active'],
                            'synced_at' => now(),
                        ]
                    );

                    // Jika ada officer_id filter, hanya kembalikan data untuk officer itu
                    if (!$officerId || $officerId == $officer->id) {
                        $syncedRecords[] = [
                            'id' => $sync->id,
                            'officer_id' => $officer->id,
                            'officer_name' => $officer->user->name ?? $officer->name,
                            'registered_number' => $sync->registered_number,
                            'fullname' => $sync->fullname,
                            'presence_count' => $sync->presence_count,
                            'absence_count' => $sync->absence_count,
                            'is_active' => $sync->is_active,
                        ];
                    }

                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error("Error processing attendance record: " . $e->getMessage(), [
                        'record' => $attendanceRecord,
                        'unit_id' => $unitId,
                    ]);
                    $errorCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Data presensi berhasil disinkronisasi. Synced: $syncedCount, Error: $errorCount",
                'synced_count' => $syncedCount,
                'error_count' => $errorCount,
                'data' => $syncedRecords
            ]);

        } catch (\Exception $e) {
            Log::error("Attendance Sync Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

}

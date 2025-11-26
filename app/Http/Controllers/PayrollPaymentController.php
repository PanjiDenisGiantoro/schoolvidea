<?php

namespace App\Http\Controllers;

use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\PayrollComponents;
use App\Models\Officer;
use App\Models\Unit;
use App\Models\AttendanceSync;
use App\Models\DataRekening;
use App\Models\Keuangan_transaksi;
use App\Models\setting_akun;
use App\Helpers\VideaclassApiHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Jurnals;
use Carbon\Carbon;
use SebastianBergmann\Environment\Console;

use function Laravel\Prompts\error;

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
            //$tagihanList = PayrollComponents::where('unit_id', Auth::user()->unit_id)->get();
            $akunList = PayrollSetting::where('units_id', Auth::user()->unit_id)->get();
        } else {
            $units = Unit::where('status', '1')->get();
            $officerList = Officer::all();
            //$tagihanList = PayrollComponents::all();
            $akunList = PayrollSetting::all();
        }

        return view('pages.penggajian.payroll_payment.payroll_payment', compact('units', 'officerList', 'akunList'));
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
        $paymentType = PayrollSetting::where('officers_id', $officerId)
            ->pluck('type');

        return response()->json($paymentType);
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
        $type = 'gaji';
        $periode   = $request->period;
        $year      = $request->year;
        $status    = $request->status ?? 'draft';

        $query = PayrollPayment::with('component', 'officer.user');

        if ($officerId && $officerId !== 'all') {
            $query->where('officer_id', $officerId);
        }

        if ($type &&  $type !== 'all') {
            $query->where('type', $type);
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
            'transport_allowance' => $settings->transport_allowance ?? 0,
            'meal_allowance' => $settings->meal_allowance ?? 0,
            'other_allowance' => $settings->other_allowance ?? 0,
        ];
        $staff = $settings->staff_allowance ?? 0;

        $totalAllowance = array_sum($allowances);

        // Ambil data attendance yang sudah disinkronisasi untuk officer ini
        $attendance = null;
        if ($officerId && $officerId !== 'all') {
            $officer = Officer::find($officerId);
            if ($officer) {
                $attendance = AttendanceSync::where('officer_id', $officerId)
                    ->where('unit_id', $officer->unit_id)
                    ->first();
            }
        }

        return response()->json([
            "belum_lunas"  => $payments->whereIn('status', ['draft', 'pending'])->values(),
            "sudah_lunas"  => $payments->where('status', 'paid')->values(),
            "query_sql"    => $query->toSql(),
            "bindings"     => $query->getBindings(),
            "request_debug" => $request->all(),
            "allowances" => $allowances,
            "total_allowance" => $totalAllowance,
            "staff" => $staff,
            "attendance" => $attendance ? [
                'presence_count' => $attendance->presence_count,
                'absence_count' => $attendance->absence_count,
                'is_active' => $attendance->is_active,
                'synced_at' => $attendance->synced_at,
            ] : null
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

    /**
     * Ambil data presensi yang sudah disinkronisasi
     */
    public function getAttendanceData(Request $request)
    {
        try {
            $officerId = $request->officer_id;
            $unitCode = Unit::where('id', $request->unit_id)->value('code');
            $unitId = $unitCode;
            Log::info('unit code'. $unitCode);


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
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'expired' => true
                ], 401);
            }

            $unitId = $request->unit_id;
            $officerId = $request->officer_id;
            $search = $request->search ?? null;

            if (!$unitId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit ID diperlukan'
                ], 400);
            }

            // Get Unit and check if it has code (videaclass_id)
            $unit = Unit::find($unitId);
            if (!$unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit tidak ditemukan'
                ], 404);
            }

            // Check if unit has code configured (digunakan sebagai videaclass_id)
            if (empty($unit->code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit belum memiliki kode. Silakan isi kode unit di pengaturan unit.',
                    'details' => [
                        'unit_id' => $unitId,
                        'unit_name' => $unit->nama_unit,
                        'help' => 'Hubungi administrator untuk mengisi kode unit di master data unit.'
                    ]
                ], 400);
            }

            $videaclassApi = new VideaclassApiHelper();
            $apiResponse = $videaclassApi->syncAttendanceData($unit->code, $search);

            // Check if API returned error
            if (!$apiResponse || isset($apiResponse['error'])) {
                $errorMsg = 'Gagal mengambil data dari API Videaclass';

                if (isset($apiResponse['message'])) {
                    if ($apiResponse['message'] === 'Tenant not found') {
                        $errorMsg = "Unit ID '{$unitId}' tidak ditemukan di Videaclass. Pastikan Unit ID sudah terdaftar di sistem Videaclass.";
                    } else {
                        $errorMsg = $apiResponse['message'];
                    }
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'details' => [
                        'unit_id' => $unitId,
                        'api_status' => $apiResponse['status'] ?? 'unknown',
                    ]
                ], 500);
            }

            // Process dan simpan data presensi ke database
            $rows = $apiResponse['rows'] ?? [];
            $syncedCount = 0;
            $errorCount = 0;
            $syncedRecords = [];

            foreach ($rows as $attendanceRecord) {
                try {
                    // Cari officer berdasarkan nip yang matching dengan registered_number dari Videaclass
                    $officer = Officer::where('nip', $attendanceRecord['registered_number'])
                        ->where('unit_id', $unitId)
                        ->first();

                    if (!$officer) {
                        Log::warning('Officer tidak ditemukan', [
                            'nip' => $attendanceRecord['registered_number'],
                            'unit_id' => $unitId
                        ]);
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
                'data' => $syncedRecords,
                
            ]);

        } catch (\Exception $e) {
            Log::error("Attendance Sync Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function payment(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'earning' => 'required|numeric|min:1',
                'deduction' => 'required|numeric|min:1',
                'notes' => 'nullable|string|max:200'
            ]);

            $pembayaran = PayrollPayment::where('id', $id)
                ->where('status', 'pending')
                ->first();

            if (!$pembayaran) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pembayaran sudah dilakukan sebelumnya'
                ], 400);
            }
            if ($request->amount < $pembayaran->net_payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah pembayaran kurang'
                ], 400);
            }

            $jumlahBayar = $request->amount ?? $pembayaran->net_payment;
            $jumlahEarning  = $request->earning ?? $pembayaran->total_earnings;
            $jumlahDeduction = $request->deduction ?? $pembayaran->total_deductions;
            $notes = $request->notes ?? null;
            $officer = Officer::find($pembayaran->officer_id);
            $keterangan = "Pembayaran gaji bulan {$pembayaran->payment_month}/{$pembayaran->payment_year} untuk " . $officer->user->name;

            // TRANSAKSI KEUANGAN
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'PG' . date('YmdHis') . rand(1000, 9999),
                'penerima_id' => $officer->id,
                'penerima_tipe' => Officer::class,
                'jenis_transaksi' => 'tagihan-keluar',
                'jumlah' => $jumlahBayar,
                'metode' => $request->metode ?? 'CASH',
                'referensi_tagihan_id' => $pembayaran->id,
                'tanggal_transaksi' => now(),
                'keterangan' => $keterangan,
                'created_by' => Auth::id(),
                'status_approval' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'status_verifikasi' => 'approved',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
            ]);

            $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                ->where(function ($q) {
                    $q->where('allotment', 'Semua Pembayaran')
                      ->orWhere('allotment', 'Pembayaran Tagihan');
                })
                ->first();

            if (!$datarekening) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rekening tabungan tidak ditemukan'
                ]);
            }


            $settings = setting_akun::where('kategori', 'tagihan-keluar');

            if (Auth::user()->unit_id) {
                $settings->where('unit_id', Auth::user()->unit_id);
            }

            $settings = $settings->where('status', '1')->first();

            if (!$settings) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data setting tidak ditemukan'
                ]);
            }

            $akun_id = $settings->akun_id;
            if (!$akun_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data akun tidak ditemukan'
                ]);
            }
            $position = $settings->debit;

            // JURNAL
            if ($position == 1) {
                // kredit pendapatan, debit kas
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'        => 0,
                    'kredit'       => $jumlahBayar,
                    'keterangan'   => $keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'kredit'       => 0,
                    'debit'        => $jumlahBayar,
                    'keterangan'   => $keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);
            } else {
                // kebalikan posisi
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $akun_id,
                    'debit'        => $jumlahBayar,
                    'kredit'       => 0,
                    'keterangan'   => $keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id'      => $datarekening->akun_id,
                    'kredit'       => $jumlahBayar,
                    'debit'        => 0,
                    'keterangan'   => $keterangan,
                    'unit_id'      => Auth::user()->unit_id
                ]);
            }


            $pembayaran->update([
                'status' => 'paid',
                'total_earnings' => $jumlahEarning,
                'total_deductions' => $jumlahDeduction,
                'net_payment' => $jumlahBayar,
                'notes' => $notes
            ]);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran berhasil'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

}

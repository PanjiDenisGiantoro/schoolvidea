<?php

namespace App\Http\Controllers;

use App\Helpers\VideaclassApiHelper;
use App\Models\AttendanceSync;
use App\Models\DataRekening;
use App\Models\Jurnals;
use App\Models\Keuangan_transaksi;
use App\Models\Officer;
use App\Models\PayrollComponents;
use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\setting_akun;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;

use function Laravel\Prompts\error;
use function Symfony\Component\Clock\now;

class PayrollPaymentController extends Controller
{
    public function index()
    {
        if (Auth::user()->yayasan_id) {
            $units = Unit::where('yayasan_id', Auth::user()->yayasan_id)->where('status', '1')->get();
            $officerList = Officer::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->orderBy('created_at', 'desc')->get();
            $tagihanList = PayrollComponents::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->orderBy('created_at', 'desc')->get();
            $akunList = PayrollSetting::whereHas('unit', function ($q) {
                $q->where('yayasan_id', Auth::user()->yayasan_id);
            })->orderBy('created_at', 'desc')->get();
        } elseif (Auth::user()->unit_id) {
            $units = Unit::where('id', Auth::user()->unit_id)->where('status', '1')->get();
            $officerList = Officer::where('unit_id', Auth::user()->unit_id)->orderBy('created_at', 'desc')->get();
            // $tagihanList = PayrollComponents::where('unit_id', Auth::user()->unit_id)->get();
            $akunList = PayrollSetting::where('units_id', Auth::user()->unit_id)->orderBy('created_at', 'desc')->get();
        } else {
            $units = Unit::where('status', '1')->get();
            $officerList = Officer::all();
            // $tagihanList = PayrollComponents::all();
            $akunList = PayrollSetting::all();
        }

        return view('pages.penggajian.payroll_payment.payroll_payment', compact('units', 'officerList', 'akunList'));
    }

    public function getByUnit($unit_id)
    {
        $officers = Officer::whereHas('unit', function ($query) use ($unit_id) {
            $query->where('id', $unit_id);
        })
            // PERBAIKAN: Tambahkan 'position' untuk form
            ->with(['user:id,name', 'position'])
            ->orderBy('created_at', 'desc')
            ->get(['id', 'user_id']);

        return response()->json($officers);
    }

    public function getByOfficer($officerId)
    {
        if ($officerId === 'all') {
            // Kalau all, maka tampilkan semua tipe yang tersedia
            $paymentType = PayrollSetting::pluck('type')->unique()->values();
        } else {
            $paymentType = PayrollSetting::where('officers_id', $officerId)
                ->pluck('type');
        }

        return response()->json($paymentType);
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

    public function getPayment(Request $request)
    {
        $officerId = $request->officer_id;
        $type = $request->type ?? 'gaji';
        $periode = $request->period;
        $year = $request->year;
        $status = $request->status ?? 'draft';

        $query = PayrollPayment::with('component', 'officer.user');

        // Filter officer
        if ($officerId && $officerId !== 'all') {
            $query->where('officer_id', $officerId);
        }

        // Filter type
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        // Filter periode
        if ($periode && $periode !== 'all') {
            $query->where('payment_month', $periode);
        }

        // Filter tahun
        if ($year) {
            $query->where('payment_year', $year);
        }

        // Filter status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Eksekusi query
        $payments = $query->orderBy('created_at', 'desc')->get();

        // Karena "all" = tidak filter, PayrollSetting hanya berlaku jika officerId != all
        $settings = ($officerId && $officerId !== 'all')
            ? PayrollSetting::where('officers_id', $officerId)->first()
            : null;

        $allowances = [
            'transport_allowance' => $settings->transport_allowance ?? 0,
            'meal_allowance' => $settings->meal_allowance ?? 0,
            'other_allowance' => $settings->other_allowance ?? 0,
        ];

        $staff = $settings->staff_allowance ?? 0;
        $totalAllowance = array_sum($allowances);

        // Ambil data attendance per bulan sesuai payment
        $attendance = null;

        if ($officerId && $officerId !== 'all') {
            $officer = Officer::find($officerId);

            if ($officer) {
                // Ambil attendance untuk bulan & tahun yang sesuai payment
                $attendance = AttendanceSync::where('officer_id', $officerId)
                    ->where('unit_id', $officer->unit_id)
                    ->where('month', $periode ?? Carbon::now()->month)
                    ->where('year', $year ?? Carbon::now()->year)
                    ->first();
            }
        }

        $attendanceMap = [];

        if ($officerId === 'all') {
            $officerIds = $payments->pluck('officer_id')->unique();

            $attendances = AttendanceSync::whereIn('officer_id', $officerIds)
                ->where('month', $periode ?? Carbon::now()->month)
                ->where('year', $year ?? Carbon::now()->year)
                ->get();

            foreach ($attendances as $att) {
                $attendanceMap[$att->officer_id] = [
                    'presence_count' => $att->presence_count,
                    'absence_count' => $att->absence_count,
                    'is_active' => $att->is_active,
                    'synced_at' => $att->synced_at,
                ];
            }
        }

        return response()->json([
            'belum_lunas' => $payments->whereIn('status', ['draft', 'pending'])->values(),
            'sudah_lunas' => $payments->where('status', 'paid')->values(),
            'query_sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'request_debug' => $request->all(),
            'allowances' => $allowances,
            'total_allowance' => $totalAllowance,
            'staff' => $staff,
            'attendance' => $attendance ? [
                'presence_count' => $attendance->presence_count,
                'absence_count' => $attendance->absence_count,
                'is_active' => $attendance->is_active,
                'synced_at' => $attendance->synced_at,
                'month' => $attendance->month,
                'year' => $attendance->year,
            ] : null,
            'attedanceMap' => $attendanceMap,
        ]);
    }

    public function getPaymentList(Request $request, $officerId)
    {
        try {
            $settings = PayrollSetting::where('officers_id', $officerId)
                ->with([
                    'components.component:id,name',
                    'deductions.deduction:id,name',
                    'officer',
                ])
                ->get();

            return response()->json([
                'settings' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('GetPaymentList Error: ' . $e->getMessage());

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
            Log::info('unit code' . $unitCode);

            if (! $officerId || ! $unitId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Officer ID dan Unit ID diperlukan',
                ], 400);
            }

            $attendance = AttendanceSync::where('officer_id', $officerId)
                ->where('unit_id', $unitId)
                ->first();

            if (! $attendance) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data presensi belum disinkronisasi',
                    'data' => null,
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
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get Attendance Data Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
            if (! Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'expired' => true,
                ], 401);
            }

            $unitId = $request->unit_id;
            $officerId = $request->officer_id;
            $search = $request->search ?? null;
            $month = Carbon::now()->month;
            $year = Carbon::now()->year;

            if (! $unitId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit ID diperlukan',
                ], 400);
            }

            // Get Unit and check if it has code (videaclass_id)
            $unit = Unit::find($unitId);
            if (! $unit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unit tidak ditemukan',
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
                        'help' => 'Hubungi administrator untuk mengisi kode unit di master data unit.',
                    ],
                ], 400);
            }

            $period = $request->start_period || Carbon::now()->startOfMonth();
            $endPeriod = $request->end_period || Carbon::now()->endOfMonth();

            $videaclassApi = new VideaclassApiHelper();
            $apiResponse = $videaclassApi->syncAttendanceData($unit->code, $search, $period, $endPeriod);

            // Check if API returned error
            if (! $apiResponse || isset($apiResponse['error'])) {
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
                    ],
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

                    if (! $officer) {
                        Log::warning('Officer tidak ditemukan', [
                            'nip' => $attendanceRecord['registered_number'],
                            'unit_id' => $unitId,
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
                            'presence' => $attendanceRecord['presence'],
                            'absence_count' => $attendanceRecord['absence_count'],
                            'is_active' => $attendanceRecord['is_active'],
                            'synced_at' => now(),
                            'month' => $month,
                            'year' => $year,
                        ]
                    );

                    // Jika ada officer_id filter, hanya kembalikan data untuk officer itu
                    if (! $officerId || $officerId == $officer->id) {
                        $syncedRecords[] = [
                            'id' => $sync->id,
                            'officer_id' => $officer->id,
                            'officer_name' => $officer->user->name ?? $officer->name,
                            'registered_number' => $sync->registered_number,
                            'fullname' => $sync->fullname,
                            'presence' => $sync->presence,
                            'presence_count' => $sync->presence_count,
                            'absence_count' => $sync->absence_count,
                            'is_active' => $sync->is_active,
                        ];
                    }

                    $syncedCount++;
                } catch (\Exception $e) {
                    Log::error('Error processing attendance record: ' . $e->getMessage(), [
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
            Log::error('Attendance Sync Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
                'deduction' => 'nullable|numeric|',
                'notes' => 'nullable|string|max:200',
                'salarynote' => 'nullable|numeric',
            ]);

            $pembayaran = PayrollPayment::where('id', $id)
                ->where('status', 'pending')
                ->first();

            if (! $pembayaran) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pembayaran sudah dilakukan sebelumnya',
                ], 400);
            }
            if ($request->amount < $pembayaran->net_payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jumlah pembayaran kurang',
                ], 400);
            }

            $jumlahBayar = $request->amount ?? $pembayaran->net_payment;
            $jumlahEarning = $request->earning ?? $pembayaran->total_earnings;
            $jumlahDeduction = $request->deduction ?? $pembayaran->total_deductions;
            $notes = $request->notes ?? null;
            $salaryNote = $request->salarynote ?? 0;
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

            if (! $datarekening) {
                return response()->json([
                    'status' => false,
                    'message' => '(Rekening) Akun Anda Tidak Memiliki Akses',
                ]);
            }

            $settings = setting_akun::where('kategori', 'tagihan-keluar');

            if (Auth::user()->unit_id) {
                $settings->where('unit_id', Auth::user()->unit_id);
            }

            $settings = $settings->where('status', '1')->first();

            if (! $settings) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data setting tidak ditemukan',
                ]);
            }

            $akun_id = $settings->akun_id;
            if (! $akun_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data akun tidak ditemukan',
                ]);
            }
            $position = $settings->debit;

            // JURNAL
            if ($position == 1) {
                // kredit pendapatan, debit kas
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akun_id,
                    'debit' => 0,
                    'kredit' => $jumlahBayar,
                    'keterangan' => $keterangan,
                    'unit_id' => Auth::user()->unit_id,
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $datarekening->akun_id,
                    'kredit' => 0,
                    'debit' => $jumlahBayar,
                    'keterangan' => $keterangan,
                    'unit_id' => Auth::user()->unit_id,
                ]);
            } else {
                // kebalikan posisi
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akun_id,
                    'debit' => $jumlahBayar,
                    'kredit' => 0,
                    'keterangan' => $keterangan,
                    'unit_id' => Auth::user()->unit_id,
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $datarekening->akun_id,
                    'kredit' => $jumlahBayar,
                    'debit' => 0,
                    'keterangan' => $keterangan,
                    'unit_id' => Auth::user()->unit_id,
                ]);
            }

            $pembayaran->update([
                'status' => 'paid',
                'total_earnings' => $jumlahEarning,
                'total_deductions' => $jumlahDeduction,
                'net_payment' => $jumlahBayar,
                'notes' => $notes,
                'salary_note' => $salaryNote,
            ]);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pembayaran berhasil',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function paymentAll(Request $request)
    {
        DB::begintransaction();
        try {
            // validasi
            $request->validate([
                'items' => 'required|array',
                'items. * .id' => 'required|integer|exists:payroll_payment, id',
                'items. * .net_payment' => 'required|numeric|min:1',
                'items. * .earning' => 'required|numeric|min:1',
                'items. * .deduction' => 'required|numeric|min:1',
                'items. * .text_note' => 'required|string|max:200',
                'totalTagihan' => 'required|numeric|min:1',
            ]);

            $ids = collect($request->items)->pluck('id')->toArray();

            // ambil semua pembayaran pending
            $pembayaran = PayrollPayment::wherein('id', $ids)
                ->where('status', 'pending')
                ->get();

            if ($pembayaran->count() === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'semua pembayaran sudah diproses sebelumnya',
                ], 400);
            }

            // hitung total nominal seluruh payment
            $totalpembayaran = $request->totalTagihan || 0;

            // catat transaksi masal
            $transaksi = Keuangan_transaksi::create([
                'code_pembayaran' => 'pg' . date('ymdhis') . rand(1000, 9999),
                'penerima_id' => null,
                'penerima_tipe' => 'mass',
                'jenis_transaksi' => 'tagihan-keluar',
                'jumlah' => $totalpembayaran,
                'metode' => $request->metode ?? 'cash',
                'referensi_tagihan_id' => null,
                'tanggal_transaksi' => now(),
                'keterangan' => 'pembayaran gaji masal',
                'created_by' => Auth::id(),
                'status_approval' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'status_verifikasi' => 'approved',
                'verified_at' => now(),
                'verified_by' => Auth::id(),
            ]);

            // ambil akun & posisi jurnal
            $datarekening = DataRekening::where('unit_id', Auth::user()->unit_id)
                ->where(function ($q) {
                    $q->where('allotment', 'Semua Pembayaran')
                        ->orWhere('allotment', 'Pembayaran Tagihan');
                })
                ->first();

            $settings = setting_akun::where('kategori', 'tagihan-keluar')
                ->where('unit_id', Auth::user()->unit_id)
                ->where('status', 1)
                ->firstorfail();

            // jurnal
            $akun_id = $settings->akun_id;
            $position = $settings->debit;

            if ($position == 1) {
                // kredit pendapatan, debit kas
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akun_id,
                    'debit' => 0,
                    'kredit' => $totalpembayaran,
                    'keterangan' => 'pembayaran gaji masal',
                    'unit_id' => auth::user()->unit_id,
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $datarekening->akun_id,
                    'debit' => $totalpembayaran,
                    'kredit' => 0,
                    'keterangan' => 'pembayaran gaji masal',
                    'unit_id' => auth::user()->unit_id,
                ]);
            } else {
                // kebalikan posisi
                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $akun_id,
                    'debit' => $totalpembayaran,
                    'kredit' => 0,
                    'keterangan' => 'pembayaran gaji masal',
                    'unit_id' => auth::user()->unit_id,
                ]);

                Jurnals::create([
                    'transaksi_id' => $transaksi->id,
                    'akun_id' => $datarekening->akun_id,
                    'kredit' => $totalpembayaran,
                    'debit' => 0,
                    'keterangan' => 'pembayaran gaji masal',
                    'unit_id' => auth::user()->unit_id,
                ]);
            }

            foreach ($request->items as $item) {
                PayrollPayment::where('id', $item['id'])
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'paid',
                        'notes' => $item['text_note'],
                        'total_earnings' => $item['earning'],
                        'total_deductions' => $item['deduction'],
                        'net_payment' => $item['net_payment'],
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'pembayaran masal berhasil',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function slip($id)
    {
        $payment = PayrollPayment::with(['officer'])->findOrFail($id);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [210, 148], // A5
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 5,
            'margin_bottom' => 5,
            // 'orientation' => 'L',
        ]);

        $html = view('pages.penggajian.payroll_payment.slip', compact('payment'))->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output("slip-{$payment->id}.pdf", 'I');
    }
}

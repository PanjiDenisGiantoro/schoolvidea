<?php

namespace App\Http\Controllers;

use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\PayrollComponents;
use App\Models\Officer;
use App\Models\Unit;
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

}

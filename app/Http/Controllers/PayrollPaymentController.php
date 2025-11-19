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

            $components = $settings
                ->flatMap->components
                ->pluck('component')
                ->whereNotNull()
                ->unique('id')
                ->values();

            $deductions = $settings
                ->flatMap->deductions
                ->pluck('deduction')
                ->whereNotNull()
                ->unique('id')
                ->values();

            $periodes = $settings->pluck('billing_period')->filter()->unique()->values();
            $years = $settings->pluck('start_year')->filter()->unique()->values();

            return response()->json([
                'settings' => $settings,
                'components' => $components,
                'deductions' => $deductions,
                'periodes' => $periodes,
                'years' => $years,
            ]);

        } catch (\Exception $e) {
            Log::error("GetPaymentList Error: " . $e->getMessage());
            return response()->json([
                'settings' => [],
                'components' => [],
                'deductions' => [],
                'periodes' => [],
                'years' => [],
            ], 500);
        }
    }

public function getPaymentData(Request $request)
{
    $officerId = $request->officer_id;
    $componentId = $request->setting_id; // kamu pakai setting_id tetapi isinya component_id
    $periode = $request->periode;
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
            $query->where('payment_month', 'like', sprintf('%02d-', $periode) . '%');
        }

        // Filter tahun
        if (!empty($year)) {
            $query->where('payment_month', 'like', '%-' . $year);
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

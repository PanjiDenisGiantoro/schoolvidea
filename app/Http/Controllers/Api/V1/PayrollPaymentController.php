<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PayrollPayment;
use App\Models\PayrollSetting;

class PayrollPaymentController extends Controller
{
    public function show($id)
    {
        $payment = PayrollPayment::with(['officer', 'transaksi'])->find($id);

        if (! $payment) {
            return response()->json([
                'status' => false,
                'message' => 'Data penggajian tidak ditemukan',
            ], 404);
        }

        $gaji = $payment->details['salary'] ?? 0 ;
        $hour = ($payment->teaching_hour_month ?? $payment->presence_count) ?? ($payment->teaching_hour_week ?? $payment->presence_count);
        $staff_hour = $payment->presence;
        $salary = $gaji * $hour;

        $components = $payment->details['components'];
        $deductions = $payment->details['deductions'];

        foreach ($components as $component) {
            $component_name = $component['name'];
            $component_value = $component['pivot']['value'] ?? 0; 
        }

        foreach ($deductions as $deduction) {
            $deduction_name = $deduction['name'];
            $deduction_value = $deduction['pivot']['value'] ?? 0;
        }

        $meal = (int) ($payment->details['meal_allowance'] ?? 0);
        $staff = (int) ($payment->details['staff_allowance'] ?? 0);
        $transport = (int) ($payment->details['transport_allowance'] ?? 0);
        $other = (int) ($payment->details['other_allowance'] ?? 0);

        return response()->json([
            'status' => true,
            'payment' => $payment,
            'transaction' => $payment->transaksi,
            'details' => [
                'salary' => $salary,
                'hour' => $hour,
                'staff_hour' => $staff_hour,
                'components' => [
                    'name' => $component_name,
                    'value' => $component_value
                ],
                'deductions' => [
                    'name' => $deduction_name,
                    'value' => $deduction_value
                ],
            'meal_allowance' => $meal * $hour,
            'staff_allowance' => $staff * $staff_hour,
            'transport_allowance' => $transport * $hour,
            'other_allowance' => $other * $hour, 
            ]
        ], 200);
    }

public function index()
{
    $payments = PayrollPayment::all();
    

    if ($payments->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Data penggajian tidak ditemukan',
        ], 404);
    }

    $result = [];

    foreach ($payments as $payment) {
        $transactions = PayrollPayment::with(['officer', 'transaksi'])->find($payment->id);

        // base salary
        $gaji = (float) ($payment->details['salary'] ?? 0);

        // jam kerja
        $hourPresence = (int) ( $payment->presence_count) ?? ($payment->presence_count);
        $hour = (int) ($payment->teaching_hour_month ?? $payment->teaching_hour_week); 

        $staffHour = (int) ($payment->presence ?? 0);
        $salary = $gaji * $hour;

        // components
        $componentData = [];
        $totalComponent = 0;

        foreach ($payment->details['components'] ?? [] as $component) {
            $value = (float) ($component['pivot']['value'] ?? 0);

            $componentData[] = [
                'name' => $component['name'],
                'value' => $value,
            ];

            $totalComponent += $value;
        }

        // deductions
        $deductionData = [];
        $totalDeduction = 0;

        foreach ($payment->details['deductions'] ?? [] as $deduction) {
            $value = (float) ($deduction['pivot']['value'] ?? 0);

            $deductionData[] = [
                'name' => $deduction['name'],
                'value' => $value,
            ];

            $totalDeduction += $value;
        }

        // allowances
        $meal      = (float) ($payment->details['meal_allowance'] ?? 0);
        $staff     = (float) ($payment->details['staff_allowance'] ?? 0);
        $transport = (float) ($payment->details['transport_allowance'] ?? 0);
        $other     = (float) ($payment->details['other_allowance'] ?? 0);

        $totalEarning = $payment->total_earnings;

        $year = $payment->payment_year;
        $month = $payment->payment_month;
        $transaction = $transactions->transaksi;

        $result[] = [
            'payment_id' => $payment->id,

            'year' => $year,
            'month' => $month,
            'created_at' => $payment->created_at,
            
            'notes' => $payment->notes,
            'salary_note' => $payment->salary_note,

            'salary_base' => $gaji,
            'hour' => $hour,
            'hour_presence' => $hourPresence,
            'staff_hour' => $staffHour,
            'salary_total' => $salary,

            'components' => $componentData,
            'deductions' => $deductionData,

            'allowances' => [
                'meal' => $meal * $hourPresence,
                'staff' => $staff * $staffHour,
                'transport' => $transport * $hourPresence,
                'other' => $other * $hourPresence,
            ],

            'summary' => [
                'total_component' => $totalComponent,
                'total_deduction' => $totalDeduction,
                'total_earning' => $totalEarning,
                'net_salary' => $payment->net_payment,
            ],
            'transaction' => $transaction
        ];
    }

    return response()->json([
        'status' => true,
        'data' => $result,
    ]);
}

}

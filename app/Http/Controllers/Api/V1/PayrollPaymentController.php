<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PayrollPayment;

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

        return response()->json([
            'status' => true,
            'payment' => $payment,
            'transaction' => $payment->transaksi,
        ], 200);
    }

    public function index()
    {
        $payment = PayrollPayment::all();

        if (! $payment) {
            return response()->json([
                'status' => false,
                'message' => 'Data penggajian tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'payment' => $payment,
        ], 200);
    }
}

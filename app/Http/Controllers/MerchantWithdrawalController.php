<?php

namespace App\Http\Controllers;

use App\Models\MerchantWithdrawal;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class MerchantWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::all();

        $baseQuery = MerchantWithdrawal::query();

        if (auth()->user()->unit_id) {
            $baseQuery->whereHas('merchant', function ($q) {
                $q->where('unit_id', auth()->user()->unit_id);
            });
        }

        $summary = [
            'total_nominal' => (clone $baseQuery)->sum('amount'),
            'total_approved' => (clone $baseQuery)->where('status', 'approved')->sum('amount'),
            'total_pending' => (clone $baseQuery)->where('status', 'pending')->sum('amount'),
            'total_rejected' => (clone $baseQuery)->where('status', 'rejected')->sum('amount'),
        ];

        return view(
            'pages.ekantin.withdrawal.index',
            compact('units', 'summary')
        );
    }

    public function datatable(Request $request)
    {
        $query = MerchantWithdrawal::with('merchant');

        // Filter unit
        if ($request->unit_id) {
            $query->whereHas(
                'merchant',
                fn ($q) => $q->where('unit_id', $request->unit_id)
            );
        }

        // Filter status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter tanggal
        if ($request->tanggal) {
            $query->whereDate('requested_at', $request->tanggal);
        }

        // Search (DataTables default)
        if (! empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->whereHas('merchant', function ($q) use ($search) {
                $q->where('kode_merchant', 'like', "%{$search}%")
                    ->orWhere('nama_merchant', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = MerchantWithdrawal::count();

        $rows = $query
            ->orderBy('requested_at', 'desc')
            ->offset($request->start)
            ->limit($request->length)
            ->get();

        $data = [];
        $no = $request->start + 1;

        foreach ($rows as $wd) {
            $data[] = [
                'no' => $no++,
                'kode_merchant' => $wd->merchant->kode_merchant ?? '-',
                'nama_merchant' => $wd->merchant->nama_merchant ?? '-',
                'no_telp' => $wd->merchant->no_hp ?? '-',
                'jml' => 'Rp ' . number_format($wd->amount, 0, ',', '.'),
                'metode' => 'Debit',
                'status' => match ($wd->status) {
                    'pending' => '<span class="badge bg-warning text-dark text-center">Pending</span>',
                    'approved' => '<span class="badge bg-success text-center">Approved</span>',
                    'rejected' => '<span class="badge bg-danger text-center">Rejected</span>',
                    default => '<span class="badge bg-secondary text-center">' . ucfirst($wd->status) . '</span>',
                },
                'waktu_penarikan' => $wd->requested_at->format('d-m-Y H:i'),

                // ✅ ACTION PALING BENAR
                'action' => view(
                    'pages.ekantin.withdrawal.action',
                    compact('wd')
                )->render(),
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function show($id)
    {
        $withdrawal = MerchantWithdrawal::with(['merchant', 'processedBy'])
            ->findOrFail($id);

        return view(
            'pages.ekantin.withdrawal.show',
            compact('withdrawal')
        );
    }

    public function print($id)
    {
        $withdrawal = MerchantWithdrawal::with('merchant')->findOrFail($id);

        // Optional: hanya boleh print jika approved
        if ($withdrawal->status !== 'approved') {
            return back()->with('error', 'Struk hanya bisa dicetak jika status approved');
        }

        $html = view(
            'pages.ekantin.withdrawal.print',
            compact('withdrawal')
        )->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output(
            'struk-withdrawal-' . $withdrawal->reference_id . '.pdf',
            'I' // I = inline (preview di browser)
        ))->header('Content-Type', 'application/pdf');
    }

    public function reject($id)
    {
        $admin = auth()->user();

        DB::transaction(function () use ($id, $admin) {
            $withdrawal = MerchantWithdrawal::lockForUpdate()->findOrFail($id);

            if ($withdrawal->status !== 'pending') {
                abort(400, 'Penarikan sudah diproses');
            }

            $withdrawal->update([
                'status' => 'rejected',
                'processed_at' => now(),
                'processed_by' => $admin->id,
            ]);
        });

        return back()->with('success', 'Penarikan berhasil di-reject');
    }

    public function withdraw(Request $request)
    {
        $admin = auth()->user();

        $request->validate([
            'withdrawal_id' => 'required|exists:merchant_withdrawals,id',
        ]);

        DB::beginTransaction();

        try {
            $withdrawal = MerchantWithdrawal::with('merchant')
                ->lockForUpdate()
                ->where('id', $request->withdrawal_id)
                ->where('status', 'pending')
                ->firstOrFail();

            $merchant = $withdrawal->merchant;
            $amount = $withdrawal->amount;

            if ($merchant->saldo_aktif < $amount) {
                DB::rollBack();

                return back()->with('error', 'Saldo merchant tidak mencukupi');
            }

            // Kurangi saldo merchant
            $merchant->update([
                'saldo_aktif' => $merchant->saldo_aktif - $amount,
            ]);

            // Update withdrawal (INI YANG BENAR)
            $withdrawal->update([
                'status' => 'approved',
                'processed_at' => Carbon::now(),
                'processed_by' => $admin->id,
            ]);

            DB::commit();

            return back()->with('success', 'Penarikan saldo berhasil disetujui');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error($e);

            return back()->with(
                'error',
                'Terjadi masalah, silakan hubungi admin'
            );
        }
    }

    // Dashboard Merchant
    public function balance()
    {
        $merchantId = auth('merchant')->id();
        $today = Carbon::today();
        $query = MerchantWithdrawal::where('merchant_id', $merchantId);
        $balance = $query->latest()->get();

        // dd(
        //     MerchantWithdrawal::where('merchant_id', $merchantId)->count(),
        //     MerchantWithdrawal::all()->pluck('merchant_id')
        // );

        $totalWithdraw = MerchantWithdrawal::where('merchant_id', $merchantId)->where('status', 'approved')->sum('amount');
        $pendingToday = MerchantWithdrawal::where('merchant_id', $merchantId)->where('status', 'pending')->whereDate('created_at', $today)->sum('amount');
        $successToday = MerchantWithdrawal::where('merchant_id', $merchantId)->where('status', 'approved')->whereDate('created_at', $today)->sum('amount');
        $rejectedToday = MerchantWithdrawal::where('merchant_id', $merchantId)->where('status', 'rejected')->whereDate('created_at', $today)->sum('amount');

        return view('pages.ekantin.dashboard_merchant.balance.index', compact('balance', 'totalWithdraw', 'pendingToday', 'successToday', 'rejectedToday'));
    }

    public function requestWithdraw(Request $request)
    {
        $merchant = auth('merchant')->user();

        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ], [
            'amount.required' => 'Jumlah penarikan wajib disi',
            'amount.min' => 'Minimal penarikan Rp. 10.000',
        ]);

        $amount = $request->amount;
        $balance = $merchant->saldo_aktif;
        $bankName = $merchant->bank_name;
        $accountNumber = $merchant->account_number;
        $accountName = $merchant->account_name;
        $requestedAt = Carbon::now();
        $referenceId = 'WD-' . Carbon::now()->format('YmdHis');

        if ($balance < $amount) {
            return back()->with('error', 'Saldo tidak mencukupi');
        }

        DB::beginTransaction();

        try {
            MerchantWithdrawal::create([
                'merchant_id' => $merchant->id,
                'amount' => $amount,
                'status' => 'pending',
                'bank_name' => $bankName,
                'account_name' => $accountName,
                'account_number' => $accountNumber,
                'requested_at' => $requestedAt,
                'reference_id' => $referenceId,
            ]);

            DB::commit();

            return back()->with('success', 'Penarikan saldo berhasil diajukan');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);

            return back()->with('error', 'Terjadi masalah, silahkan hubungi admin' . $e->getMessage());
        }
    }

    public function showBalance($id)
    {
        $withdrawal = MerchantWithdrawal::with(['merchant', 'processedBy'])
            ->findOrFail($id);

        return view(
            'pages.ekantin.dashboard_merchant.balance.show',
            compact('withdrawal')
        );
    }

    public function printBalance($id)
    {
        $withdrawal = MerchantWithdrawal::with('merchant')->findOrFail($id);

        // Optional: hanya boleh print jika approved
        if ($withdrawal->status !== 'approved') {
            return back()->with('error', 'Struk hanya bisa dicetak jika status approved');
        }

        $html = view(
            'pages.ekantin.dashboard_merchant.balance.print',
            compact('withdrawal')
        )->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response($mpdf->Output(
            'struk-withdrawal-' . $withdrawal->reference_id . '.pdf',
            'I' // I = inline (preview di browser)
        ))->header('Content-Type', 'application/pdf');
    }

    public function datatableBalance(Request $request)
    {
        $merchantId = auth('merchant')->user()->id;
        $query = MerchantWithdrawal::where('merchant_id', $merchantId);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->tanggal) {
            $query->whereDate('requested_at', $request->tanggal);
        }

        if (! empty($request->search['value'])) {
            $search = $request->search['value'];

            $query->where(function ($q) use ($search) {
                $q->where('reference_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = MerchantWithdrawal::where('merchant_id', $merchantId)->count();

        $rows = $query
            ->orderBy('requested_at', 'desc')
            ->offset($request->start)
            ->limit($request->length)
            ->get();

        $data = [];
        $no = $request->start + 1;

        foreach ($rows as $wd) {
            $data[] = [
                'no' => $no++,
                'kode_withdrawal' => $wd->reference_id ?? '-',
                'jml' => 'Rp ' . number_format($wd->amount, 0, ',', '.'),
                'metode' => 'Debit',
                'status' => match ($wd->status) {
                    'pending' => '<span class="badge bg-warning text-dark text-center">Pending</span>',
                    'approved' => '<span class="badge bg-success text-center">Approved</span>',
                    'rejected' => '<span class="badge bg-danger text-center">Rejected</span>',
                    default => '<span class="badge bg-secondary text-center">' . ucfirst($wd->status) . '</span>',
                },
                'waktu_penarikan' => $wd->requested_at->format('d-m-Y H:i'),
                'action' => '
    <a href="' . route('merchant.balance.show', $wd->id) . '"
       class="btn btn-sm btn-primary rounded-pill">
       <i class="ri-eye-line"></i>
    </a>

    ' . ($wd->status === 'approved'
                        ? '<a href="' . route('merchant.balance.print', $wd->id) . '"
             target="_blank"
             class="btn btn-sm btn-warning ms-1 rounded-pill">
             <i class="ri-printer-line"></i>
           </a>'
                        : '') . '
',

            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}

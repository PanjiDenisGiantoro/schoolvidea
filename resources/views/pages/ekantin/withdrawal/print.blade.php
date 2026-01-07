<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Penarikan</title>
    <style>
        body {
            font-family: DejaVu Sans, monospace;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .receipt {
            width: 58mm;
            padding: 6px;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center bold">
            STRUK PENARIKAN
        </div>
        <div class="center">
            {{ config('app.name') }}
        </div>

        <div class="line"></div>

        <div class="row">
            <span>Ref</span>
            <span>{{ $withdrawal->reference_id }}</span>
        </div>
        <div class="row">
            <span>Tgl</span>
            <span>{{ $withdrawal->requested_at->format('d/m/Y H:i') }}</span>
        </div>

        <div class="line"></div>

        <div class="row">
            <span>Merchant</span>
            <span>{{ $withdrawal->merchant->kode_merchant }}</span>
        </div>
        <div>
            {{ $withdrawal->merchant->nama_merchant }}
        </div>

        <div class="line"></div>

        <div class="row bold">
            <span>Penarikan</span>
            <span>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</span>
        </div>

        <div class="row">
            <span>Bank</span>
            <span>{{ $withdrawal->bank_name }}</span>
        </div>
        <div class="row">
            <span>No Rek</span>
            <span>{{ $withdrawal->account_number }}</span>
        </div>
        <div class="row">
            <span>Nama</span>
            <span>{{ $withdrawal->account_name }}</span>
        </div>

        <div class="line"></div>

        <div class="row bold">
            <span>Status</span>
            <span>{{ strtoupper($withdrawal->status) }}</span>
        </div>

        <div class="line"></div>

        <div class="footer">
            *** TERIMA KASIH *** <br>
            Dicetak oleh sistem
        </div>
    </div>
</body>
</html>

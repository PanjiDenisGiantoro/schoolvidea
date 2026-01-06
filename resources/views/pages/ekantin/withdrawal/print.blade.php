<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Penarikan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .container {
            width: 100%;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .row {
            margin-bottom: 6px;
        }
        .label {
            display: inline-block;
            width: 140px;
        }
        hr {
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">STRUK PENARIKAN DANA</div>
        <hr>

        <div class="row">
            <span class="label">Reference ID</span> :
            {{ $withdrawal->reference_id }}
        </div>
        <div class="row">
            <span class="label">Tanggal</span> :
            {{ $withdrawal->requested_at->format('d-m-Y H:i') }}
        </div>

        <hr>

        <div class="row">
            <span class="label">Kode Merchant</span> :
            {{ $withdrawal->merchant->kode_merchant }}
        </div>
        <div class="row">
            <span class="label">Nama Merchant</span> :
            {{ $withdrawal->merchant->nama_merchant }}
        </div>

        <hr>

        <div class="row">
            <span class="label">Jumlah Penarikan</span> :
            <strong>
                Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}
            </strong>
        </div>
        <div class="row">
            <span class="label">Bank</span> :
            {{ $withdrawal->bank_name }}
        </div>
        <div class="row">
            <span class="label">No Rekening</span> :
            {{ $withdrawal->account_number }}
        </div>
        <div class="row">
            <span class="label">Atas Nama</span> :
            {{ $withdrawal->account_name }}
        </div>

        <hr>

        <div class="row">
            <span class="label">Status</span> :
            <strong>{{ strtoupper($withdrawal->status) }}</strong>
        </div>

        <div class="footer">
            Dokumen ini dicetak secara otomatis oleh sistem
        </div>
    </div>
</body>
</html>

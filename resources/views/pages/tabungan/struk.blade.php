<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk Transaksi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            width: 80mm;
            background: #fff;
        }

        .container {
            padding: 5px;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 1px;
        }

        .header-subtitle {
            font-size: 9px;
            margin-bottom: 2px;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
            width: 45%;
        }

        .info-value {
            width: 55%;
            text-align: right;
            word-break: break-word;
        }

        .detail-box {
            border: 1px solid #000;
            padding: 6px;
            margin: 8px 0;
            background-color: #f9f9f9;
        }

        .transaction-type {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            padding: 6px 0;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .transaction-type.setoran {
            color: #28a745;
            border: 2px solid #28a745;
        }

        .transaction-type.penarikan {
            color: #dc3545;
            border: 2px solid #dc3545;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 12px;
            font-weight: bold;
        }

        .amount-label {
            width: 50%;
        }

        .amount-value {
            width: 50%;
            text-align: right;
        }

        .balance-section {
            margin: 8px 0;
            padding: 6px;
            background-color: #f0f0f0;
            border: 1px solid #999;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 10px;
        }

        .balance-label {
            font-weight: bold;
        }

        .balance-value {
            text-align: right;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #000;
            font-size: 9px;
        }

        .footer-text {
            margin: 3px 0;
        }

        .qr-note {
            text-align: center;
            margin-top: 8px;
            font-size: 9px;
            padding: 4px;
            border: 1px dashed #999;
        }

        .center {
            text-align: center;
        }

        .text-green {
            color: #28a745;
        }

        .text-red {
            color: #dc3545;
        }

        .text-blue {
            color: #1bb394;
        }

        .note {
            font-size: 9px;
            color: #666;
            margin-top: 6px;
            padding: 4px;
            border: 1px solid #ddd;
            background: #fafafa;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div class="header-title">STRUK TRANSAKSI</div>
            <div class="header-subtitle">TABUNGAN SISWA</div>
            <div class="header-subtitle" style="font-size: 8px; margin-top: 3px;">School Videa Indonesia</div>
        </div>

        {{-- Student Info --}}
        <div class="info-row">
            <span class="info-label">Nama</span>
            <span class="info-value">{{ $siswa->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NISN</span>
            <span class="info-value">{{ $siswa->nisn }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">{{ $siswa->kelas->nama_kelas }}</span>
        </div>

        <div class="divider"></div>

        {{-- Transaction Type --}}
        <div class="transaction-type @if($transaksi->jenis_transaksi == 'setoran_tabungan') setoran @else penarikan @endif">
            @if($transaksi->jenis_transaksi == 'setoran_tabungan')
                ✓ SETORAN TABUNGAN ✓
            @else
                ✗ PENARIKAN TABUNGAN ✗
            @endif
        </div>

        {{-- Transaction Details --}}
        <div class="detail-box">
            <div class="info-row">
                <span class="info-label">No. Transaksi</span>
                <span class="info-value">{{ $transaksi->code_pembayaran }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Waktu</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('H:i:s') }}</span>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Amount Section --}}
        <div class="amount-row">
            <span class="amount-label">Jumlah</span>
            <span class="amount-value @if($transaksi->jenis_transaksi == 'setoran_tabungan') text-green @else text-red @endif">
                @if($transaksi->jenis_transaksi == 'setoran_tabungan')
                    + Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                @else
                    - Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                @endif
            </span>
        </div>

        @if($transaksi->keterangan)
            <div class="info-row">
                <span class="info-label">Ket.</span>
                <span class="info-value">{{ substr($transaksi->keterangan, 0, 20) }}{{ strlen($transaksi->keterangan) > 20 ? '...' : '' }}</span>
            </div>
        @endif

        <div class="divider"></div>

        {{-- Balance Section --}}
        <div class="balance-section">
            <div class="balance-row">
                <span class="balance-label">Saldo Awal</span>
                <span class="balance-value">Rp {{ number_format($current_transaction->saldo_sebelum, 0, ',', '.') }}</span>
            </div>
            <div class="balance-row">
                <span class="balance-label">Jumlah</span>
                <span class="balance-value @if($transaksi->jenis_transaksi == 'setoran_tabungan') text-green @else text-red @endif">
                    @if($transaksi->jenis_transaksi == 'setoran_tabungan')
                        + Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                    @else
                        - Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}
                    @endif
                </span>
            </div>
            <div class="balance-row" style="font-size: 11px; font-weight: bold; border-top: 1px solid #999; padding-top: 4px;">
                <span class="balance-label">Saldo Akhir</span>
                <span class="balance-value text-blue">Rp {{ number_format($current_transaction->saldo_sesudah, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Status --}}
        <div class="info-row" style="margin-top: 6px;">
            <span class="info-label">Status</span>
            <span class="info-value">
                @php
                    $statusApproval = $transaksi->jenis_transaksi == 'penarikan_tabungan'
                        ? $transaksi->status_approval
                        : 'approved';
                @endphp

                @if($statusApproval == 'approved')
                    <strong class="text-green">✓ APPROVED</strong>
                @elseif($statusApproval == 'rejected')
                    <strong class="text-red">✗ REJECTED</strong>
                @elseif($statusApproval == 'pending')
                    <strong>⏳ PENDING</strong>
                @endif
            </span>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-text">Terima kasih</div>
            <div class="footer-text" style="font-size: 8px;">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</div>
            <div class="qr-note">
                Simpan struk ini sebagai bukti transaksi
            </div>
        </div>
    </div>
</body>
</html>

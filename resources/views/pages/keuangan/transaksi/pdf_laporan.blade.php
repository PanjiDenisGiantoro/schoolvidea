<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Keuangan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .header h2 {
            font-size: 18px;
            color: #7f8c8d;
            font-weight: normal;
            margin-bottom: 10px;
        }

        .header .info {
            font-size: 11px;
            color: #95a5a6;
        }

        /* Periode Info */
        .periode-info {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }

        .periode-info h3 {
            font-size: 14px;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .periode-info p {
            margin-bottom: 5px;
        }

        /* Summary Cards */
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
            page-break-after: always;
            page-break-inside: avoid;
        }

        .summary-card {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            border: 2px solid #dee2e6;
            text-align: center;
            page-break-inside: avoid;
        }

        .summary-card .label {
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
        }

        .summary-card.success .value {
            color: #28a745;
        }

        .summary-card.danger .value {
            color: #dc3545;
        }

        .summary-card.info .value {
            color: #17a2b8;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: #fff;
        }

        table thead {
            background: #2c3e50;
            color: #fff;
        }

        table th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }

        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 11px;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-secondary {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-primary {
            background: #cce5ff;
            color: #004085;
        }

        .text-success {
            color: #28a745;
            font-weight: 600;
        }

        .text-danger {
            color: #dc3545;
            font-weight: 600;
        }

        .text-muted {
            color: #6c757d;
            font-size: 10px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }

        .signature {
            margin-top: 100px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-box .label {
            margin-bottom: 80px;
            font-size: 12px;
        }

        .signature-box .name {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: 600;
        }

        .signature-title {
            text-align: center;
            margin-bottom: 50px;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }

    </style>
</head>
<body>

        <!-- Header -->
        <div class="header">
            <h1>LAPORAN TRANSAKSI KEUANGAN</h1>
            <h2>{{ Auth::user()->unit->nama_unit ?? 'Semua Unit' }}</h2>
            <p class="info">
                Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }} |
                Oleh: {{ Auth::user()->name }}
            </p>
        </div>

        <!-- Periode Info -->
        <div class="periode-info">
            <h3>Informasi Periode</h3>
            <p><strong>Dari Tanggal:</strong> {{ \Carbon\Carbon::parse($dari_tanggal)->format('d F Y') }}</p>
            <p><strong>Sampai Tanggal:</strong> {{ \Carbon\Carbon::parse($sampai_tanggal)->format('d F Y') }}</p>
            <p><strong>Total Transaksi:</strong> {{ $total_transaksi }} transaksi</p>
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-card success">
                <div class="label">Total Pemasukan</div>
                <div class="value">Rp {{ number_format($total_pemasukan, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card danger">
                <div class="label">Total Pengeluaran</div>
                <div class="value">Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card info">
                <div class="label">Saldo Bersih</div>
                <div class="value">Rp {{ number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 10%;">Kode</th>
                    <th style="width: 9%;">Tanggal</th>
                    <th style="width: 15%;">Jenis Transaksi</th>
                    <th style="width: 18%;">Penerima</th>
                    <th style="width: 12%;">Jumlah</th>
                    <th style="width: 10%;">Metode</th>
                    <th style="width: 23%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $transaksi)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transaksi->code_pembayaran }}</td>
                        <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $badgeClass = match($transaksi->jenis_transaksi) {
                                    'setoran_tabungan' => 'badge-success',
                                    'penarikan_tabungan' => 'badge-warning',
                                    'pembayaran', 'tagihan' => 'badge-info',
                                    default => 'badge-secondary',
                                };
                                $jenisText = match($transaksi->jenis_transaksi) {
                                    'setoran_tabungan' => 'Setoran Tabungan',
                                    'penarikan_tabungan' => 'Penarikan Tabungan',
                                    'pembayaran' => 'Pembayaran',
                                    'tagihan' => 'Pembayaran Tagihan',
                                    default => ucwords(str_replace('_', ' ', $transaksi->jenis_transaksi)),
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $jenisText }}</span>
                            @if(in_array($transaksi->jenis_transaksi, ['tagihan', 'pembayaran']) && $transaksi->pembayaranTagihan)
                                <br><small class="text-muted">{{ $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->nama_tagihan ?? '-' }}</small>
                            @endif
                        </td>
                        <td>
                            @if($transaksi->penerima)
                                @if($transaksi->penerima_tipe === 'App\Models\Siswa')
                                    {{ $transaksi->penerima->user->name ?? '-' }}
                                    <br><small class="text-muted">{{ $transaksi->penerima->nisn ?? '-' }}</small>
                                @else
                                    {{ $transaksi->penerima->name ?? '-' }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if(in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'pembayaran', 'tagihan']))
                                <span class="text-success">+ Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                            @else
                                <span class="text-danger">- Rp {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $metodeBadge = match($transaksi->metode) {
                                    'CASH' => 'badge-primary',
                                    'TRANSFER' => 'badge-info',
                                    'SALDO_TABUNGAN' => 'badge-warning',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $metodeBadge }}">{{ $transaksi->metode ?? '-' }}</span>
                        </td>
                        <td>{{ $transaksi->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">Tidak ada data transaksi</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Signature Page -->
        <div style="page-break-before: always;">
            <div class="signature-title">
                LEMBAR PENGESAHAN<br>
                LAPORAN TRANSAKSI KEUANGAN
            </div>

            <div class="signature">
                <div class="signature-box">
                    <div class="label">Mengetahui,<br>Kepala Sekolah</div>
                    <div class="name">(__________________)</div>
                </div>
                <div class="signature-box">
                    <div class="label">{{ \Carbon\Carbon::now()->format('d F Y') }}<br>Bendahara</div>
                    <div class="name">(__________________)</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dokumen ini dicetak secara otomatis dari Sistem Informasi Keuangan</p>
            <p>{{ Auth::user()->unit->nama_unit ?? config('app.name') }}</p>
        </div>
</body>
</html>

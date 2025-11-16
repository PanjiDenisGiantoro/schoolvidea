<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mutasi Tabungan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h2 {
            font-size: 16px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .header p {
            font-size: 9px;
            margin: 2px 0;
        }

        .student-info {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 3px;
            background-color: #f9f9f9;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 9px;
        }

        .info-label {
            font-weight: bold;
            width: 30%;
        }

        .info-value {
            width: 70%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        thead {
            background-color: #1bb394;
            color: white;
        }

        th {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 9px;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f0f0f0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-success {
            color: #28a745;
            font-weight: bold;
        }

        .text-danger {
            color: #dc3545;
            font-weight: bold;
        }

        .text-info {
            color: #1bb394;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 8px;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 3px;
        }

        .summary-item {
            text-align: center;
            flex: 1;
            border-right: 1px solid #ddd;
            padding-right: 10px;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #1bb394;
        }

        .page-number {
            text-align: center;
            margin-top: 15px;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h2>MUTASI TABUNGAN SISWA</h2>
        <p style="font-weight: bold; color: #1bb394;">PT. SCHOOL VIDEA INDONESIA</p>
    </div>

    {{-- Student Info --}}
    <div class="student-info">
        <div class="info-row">
            <span class="info-label">Nama Siswa</span>
            <span class="info-value">: {{ $siswa->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">NISN</span>
            <span class="info-value">: {{ $siswa->nisn }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Kelas</span>
            <span class="info-value">: {{ $siswa->kelas->nama_kelas }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak</span>
            <span class="info-value">: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Saldo Awal</div>
            <div class="summary-value">Rp {{ number_format($saldo_awal, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Setoran</div>
            <div class="summary-value text-success">Rp {{ number_format($logs->where('jenis_transaksi', 'setoran_tabungan')->sum('jumlah'), 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Penarikan</div>
            <div class="summary-value text-danger">Rp {{ number_format($logs->where('jenis_transaksi', 'penarikan_tabungan')->sum('jumlah'), 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Saldo Akhir</div>
            <div class="summary-value text-info">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Transaction Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 12%;">Tanggal/Waktu</th>
                <th style="width: 10%;">Jenis</th>
                <th style="width: 12%;">Jumlah</th>
                <th style="width: 10%;">Debit/Kredit</th>
                <th style="width: 12%;">Saldo Awal</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;">Saldo Akhir</th>
                <th style="width: 14%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}<br>
                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}
                    </td>
                    <td class="text-center">
                        @if($log->jenis_transaksi == 'setoran_tabungan')
                            <span class="badge badge-success">Setoran</span>
                        @else
                            <span class="badge badge-danger">Penarikan</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @if($log->jenis_transaksi == 'setoran_tabungan')
                            <span class="text-success">+ Rp {{ number_format($log->jumlah, 0, ',', '.') }}</span>
                        @else
                            <span class="text-danger">- Rp {{ number_format($log->jumlah, 0, ',', '.') }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($log->jenis_transaksi == 'setoran_tabungan')
                            Kredit
                        @else
                            Debit
                        @endif
                    </td>
                    <td class="text-right text-info">Rp {{ number_format($log->saldo_sebelum, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @php
                            $statusApproval = $log->jenis_transaksi == 'penarikan_tabungan'
                                ? $log->status_approval
                                : 'approved';
                        @endphp

                        @if($statusApproval == 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($statusApproval == 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @elseif($statusApproval == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td class="text-right text-success">Rp {{ number_format($log->saldo_sesudah, 0, ',', '.') }}</td>
                    <td>{{ $log->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <div style="margin-bottom: 10px;">
            Dokumen ini adalah mutasi tabungan resmi dari School Videa Indonesia.<br>
            Untuk pertanyaan lebih lanjut, silakan hubungi bagian keuangan.
        </div>
        <div class="page-number">
            Halaman {{ $currentPage }} dari {{ $totalPages }}
        </div>
    </div>
</body>
</html>

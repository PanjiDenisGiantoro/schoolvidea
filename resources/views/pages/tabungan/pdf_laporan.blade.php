<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Tabungan Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        .summary {
            margin-bottom: 15px;
            width: 100%;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary td strong {
            display: block;
            font-size: 9px;
            margin-bottom: 5px;
            color: #666;
        }
        .summary td span {
            font-size: 12px;
            font-weight: bold;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data th {
            background-color: #4CAF50;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 10px;
            border: 1px solid #ddd;
        }
        table.data td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9px;
            text-align: center;
        }
        table.data tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN TABUNGAN SISWA</h2>
        <p>
            @if($dari_tanggal && $sampai_tanggal)
                Periode: {{ date('d/m/Y', strtotime($dari_tanggal)) }} - {{ date('d/m/Y', strtotime($sampai_tanggal)) }}
            @else
                Semua Periode
            @endif
        </p>
        <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <table>
            <tr>
                <td>
                    <strong>Total Setoran</strong>
                    <span>Rp {{ number_format($total_setoran ?? 0, 0, ',', '.') }}</span>
                </td>
                <td>
                    <strong>Total Penarikan</strong>
                    <span>Rp {{ number_format($total_penarikan ?? 0, 0, ',', '.') }}</span>
                </td>
                <td>
                    <strong>Saldo Tabungan</strong>
                    <span>Rp {{ number_format($total_setoran - $total_penarikan ?? 0, 0, ',', '.') }}</span>
                </td>
                <td>
                    <strong>Jumlah Transaksi</strong>
                    <span>{{ number_format($jumlah_transaksi ?? 0, 0, ',', '.') }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Total Pending</strong>
                    <span>Rp {{ number_format($total_pending ?? 0, 0, ',', '.') }}</span>
                </td>
                <td>
                    <strong>Total Approved</strong>
                    <span>Rp {{ number_format($total_approved ?? 0, 0, ',', '.') }}</span>
                </td>
                <td colspan="2">
                    <strong>Total Rejected</strong>
                    <span>Rp {{ number_format($total_rejected ?? 0, 0, ',', '.') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Data Table --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Unit</th>
                <th style="width: 10%;">NISN</th>
                <th style="width: 20%;">Nama Siswa</th>
                <th style="width: 12%;">Kelas</th>
                <th style="width: 13%;">Tahun Ajaran</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 15%;">Saldo Aktif</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $siswa)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $siswa->unit->nama_unit ?? '-' }}</td>
                    <td>{{ $siswa->nisn ?? '-' }}</td>
                    <td style="text-align: left;">{{ $siswa->user->name ?? '-' }}</td>
                    <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $siswa->tahun_ajaran->tahun_ajaran ?? '-' }}</td>
                    <td>
                        @php
                            $saldo = $siswa->user->saldo ?? null;
                            $statusBadge = $saldo && $saldo->status == 1 ? 'success' : 'danger';
                            $statusText = $saldo && $saldo->status == 1 ? 'Aktif' : 'Tidak Aktif';
                        @endphp
                        <span class="badge badge-{{ $statusBadge }}">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        Rp {{ number_format($saldo->saldo_akhir ?? 0, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada data siswa</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
    </div>
</body>
</html>

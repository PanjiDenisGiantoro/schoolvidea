<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kelola Tagihan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
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
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary td strong {
            display: block;
            font-size: 10px;
            margin-bottom: 5px;
            color: #666;
        }
        .summary td span {
            font-size: 13px;
            font-weight: bold;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data th {
            background-color: #2196F3;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 9px;
            border: 1px solid #ddd;
        }
        table.data td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 8px;
            text-align: center;
        }
        table.data tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
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
        <h2>LAPORAN KELOLA TAGIHAN</h2>
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
                    <strong>Jumlah Data</strong>
                    <span>{{ $summary['jumlah_data'] ?? 0 }}</span>
                </td>
                <td>
                    <strong>Nominal Tagihan</strong>
                    <span>Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</span>
                </td>
                <td>
                    <strong>Sudah Dibayar</strong>
                    <span>Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</span>
                </td>
                <td>
                    <strong>Belum Dibayar</strong>
                    <span>Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Data Table --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Unit</th>
                <th style="width: 12%;">Nama Tagihan</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 18%;">Kategori</th>
                <th style="width: 10%;">NISN</th>
                <th style="width: 14%;">Nama Siswa</th>
                <th style="width: 10%;">Total Tagihan</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($tagihans as $tagihan)
                @php
                    $total_tagihan = $tagihan->items->sum('nominal');
                    $siswas = $tagihan->tagihanSiswa;
                    $siswa_count = $siswas->count();
                @endphp

                @if($siswa_count > 0)
                    @foreach($siswas as $index => $tagihan_siswa)
                        @php
                            $siswa = $tagihan_siswa->siswa;
                            if(!$siswa) continue;

                            $sudah_bayar = $siswa->pembayaranTagihan->sum('jumlah_bayar');
                            $tunggakan = $total_tagihan - $sudah_bayar;
                        @endphp
                        <tr>
                            @if($index == 0)
                                <td rowspan="{{ $siswa_count }}">{{ $no++ }}</td>
                                <td rowspan="{{ $siswa_count }}" style="text-align: left;">
                                    {{ $tagihan->unit->nama_unit ?? '-' }}
                                </td>
                                <td rowspan="{{ $siswa_count }}" style="text-align: left;">
                                    {{ $tagihan->nama_tagihan }}
                                </td>
                                <td rowspan="{{ $siswa_count }}">
                                    {{ $tagihan->kelas->nama_kelas ?? 'Semua Kelas' }}
                                </td>
                                <td rowspan="{{ $siswa_count }}" style="text-align: left;">
                                    @foreach($tagihan->items as $item)
                                        {{ $item->kategori->nama_kategori ?? '-' }}@if(!$loop->last), @endif
                                    @endforeach
                                </td>
                            @endif

                            <td>{{ $siswa->nisn ?? '-' }}</td>
                            <td style="text-align: left;">{{ $siswa->user->name ?? '-' }}</td>
                            <td style="text-align: right;">Rp {{ number_format($total_tagihan, 0, ',', '.') }}</td>
                            <td>
                                @if ($tunggakan <= 0)
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-warning">Belum Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td style="text-align: left;">{{ $tagihan->unit->nama_unit ?? '-' }}</td>
                        <td style="text-align: left;">{{ $tagihan->nama_tagihan }}</td>
                        <td>{{ $tagihan->kelas->nama_kelas ?? 'Semua Kelas' }}</td>
                        <td style="text-align: left;">
                            @foreach($tagihan->items as $item)
                                {{ $item->kategori->nama_kategori ?? '-' }}@if(!$loop->last), @endif
                            @endforeach
                        </td>
                        <td colspan="4">Belum ada siswa terdaftar</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="9">Belum ada data tagihan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->name }}</p>
    </div>
</body>
</html>

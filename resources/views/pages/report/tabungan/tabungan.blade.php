@extends('layouts.app')

@section('title', 'Laporan Tabungan')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Tabungan</h4>
            <span>Periode: {{ \Carbon\Carbon::parse($from)->format('d-m-Y') }}
            s/d {{ \Carbon\Carbon::parse($to)->format('d-m-Y') }}
        </span>
        </div>

        <div class="card-body">
            <p><strong>Saldo Awal:</strong> {{ number_format($saldoAwal, 0, ',', '.') }}</p>
            <p><strong>Saldo Akhir (saldo_keuangan):</strong> {{ number_format($saldoAkhir, 0, ',', '.') }}</p>

            <table class="table table-bordered table-sm mt-3">
                <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis Transaksi</th>
                    <th>Keterangan</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Saldo Berjalan</th>
                </tr>
                </thead>
                <tbody>
                @foreach($riwayat as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['tanggal'])->format('d-m-Y H:i') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $row['jenis'])) }}</td>
                        <td>{{ $row['keterangan'] }}</td>
                        <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

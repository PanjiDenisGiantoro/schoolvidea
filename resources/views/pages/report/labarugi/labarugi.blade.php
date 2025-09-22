@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h4>Laporan Laba Rugi</h4>
            <span>Periode: {{ \Carbon\Carbon::parse($from)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($to)->format('d-m-Y') }}</span>
        </div>

        <div class="card-body">
            <h5>Pendapatan</h5>
            <table class="table table-sm">
                @foreach($pendapatan as $row)
                    <tr>
                        <td>{{ $row['nama'] }}</td>
                        <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold table-success">
                    <td>Total Pendapatan</td>
                    <td class="text-end">{{ number_format($total_pendapatan, 0, ',', '.') }}</td>
                </tr>
            </table>

            <h5>Beban</h5>
            <table class="table table-sm">
                @foreach($beban as $row)
                    <tr>
                        <td>{{ $row['nama'] }}</td>
                        <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold table-danger">
                    <td>Total Beban</td>
                    <td class="text-end">{{ number_format($total_beban, 0, ',', '.') }}</td>
                </tr>
            </table>

            <h4 class="text-end mt-3">
                <span class="fw-bold">Laba Bersih: </span>
                {{ number_format($laba_bersih, 0, ',', '.') }}
            </h4>
        </div>
    </div>
@endsection

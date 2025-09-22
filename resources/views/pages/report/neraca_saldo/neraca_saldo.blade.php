@extends('layouts.app')

@section('title', 'Neraca Saldo')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Neraca Saldo ({{ $from }} s/d {{ $to }})</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Kode Akun</th>
                    <th>Nama Akun</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Kredit</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $row)
                    <tr>
                        <td>{{ $row['kode'] }}</td>
                        <td>{{ $row['nama'] }}</td>
                        <td class="text-end">{{ number_format($row['debit'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row['kredit'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="fw-bold">
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td class="text-end">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($totalKredit, 0, ',', '.') }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

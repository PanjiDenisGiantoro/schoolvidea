@extends('layouts.app')

@section('title', 'Jurnal Umum')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Jurnal Umum</h4>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Akun</th>
                    <th>Keterangan</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Kredit</th>
                </tr>
                </thead>
                <tbody>
                @foreach($jurnals as $jurnal)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $jurnal->akun->nama_akun ?? '-' }}</td>
                        <td>{{ $jurnal->keterangan }}</td>
                        <td class="text-end">{{ number_format($jurnal->debit, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($jurnal->kredit, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="3" class="text-center">Total</th>
                    <th class="text-end">
                        {{ number_format($jurnals->sum('debit'), 0, ',', '.') }}
                    </th>
                    <th class="text-end">
                        {{ number_format($jurnals->sum('kredit'), 0, ',', '.') }}
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

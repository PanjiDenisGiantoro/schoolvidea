@extends('layouts.app')

@section('title', 'Buku Besar')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Buku Besar ({{ $from }} s/d {{ $to }})</h4>
        </div>
        <div class="card-body">
            @foreach($akuns as $akun)
                <h5 class="mt-4">{{ $akun->kode_akun }} - {{ $akun->nama_akun }}</h5>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Kredit</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $saldo = 0; @endphp
                    @foreach($akun->jurnals as $jurnal)
                        @php
                            $saldo += $jurnal->debit - $jurnal->kredit;
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $jurnal->keterangan }}</td>
                            <td class="text-end">{{ number_format($jurnal->debit, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($jurnal->kredit, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($saldo, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
@endsection

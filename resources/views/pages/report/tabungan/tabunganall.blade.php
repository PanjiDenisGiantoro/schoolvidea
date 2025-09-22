@extends('layouts.app')
@section('title', 'Laporan Rekap Tabungan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Laporan Rekap Tabungan',
        'subTitle' => "Periode: $from s/d $to"
    ])

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>NISN</th>
                    <th class="text-end">Total Setoran</th>
                    <th class="text-end">Total Penarikan</th>
                    <th class="text-end">Saldo Akhir</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rekap as $row)
                    <tr>
                        <td>{{ $row['nama'] }}</td>
                        <td class="text-end">{{ number_format($row['setoran'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row['penarikan'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row['saldo_akhir'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

        </div>
    </div>
@endsection

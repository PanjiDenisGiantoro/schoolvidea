@extends('layouts.app')

@section('title', 'Laporan Neraca')

@section('content')
    <div class="card">
        <div class="card-header">
            <h4>Laporan Neraca per {{ \Carbon\Carbon::parse($to)->format('d-m-Y') }}</h4>
        </div>
        <div class="card-body row">

            <!-- Aktiva -->
            <div class="col-md-6">
                <h5>Aktiva (Aset)</h5>
                <table class="table table-sm">
                    @foreach($data['aset'] as $row)
                        <tr>
                            <td>{{ $row['nama'] }}</td>
                            <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold table-primary">
                        <td>Total Aset</td>
                        <td class="text-end">{{ number_format($total_aset, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Pasiva -->
            <div class="col-md-6">
                <h5>Kewajiban & Ekuitas</h5>
                <table class="table table-sm">
                    <tr><th colspan="2">Liabilitas</th></tr>
                    @foreach($data['liabilitas'] as $row)
                        <tr>
                            <td>{{ $row['nama'] }}</td>
                            <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold">
                        <td>Total Liabilitas</td>
                        <td class="text-end">{{ number_format($total_liabilitas, 0, ',', '.') }}</td>
                    </tr>

                    <tr><th colspan="2">Ekuitas</th></tr>
                    @foreach($data['ekuitas'] as $row)
                        <tr>
                            <td>{{ $row['nama'] }}</td>
                            <td class="text-end">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="fw-bold">
                        <td>Total Ekuitas</td>
                        <td class="text-end">{{ number_format($total_ekuitas, 0, ',', '.') }}</td>
                    </tr>

                    <tr class="fw-bold table-primary">
                        <td>Total Liabilitas + Ekuitas</td>
                        <td class="text-end">{{ number_format($total_passiva, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection

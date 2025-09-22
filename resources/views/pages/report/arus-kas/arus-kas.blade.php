@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    @include('partials.page-title', [
        'title' => 'Dashboard',
        'subTitle' => 'Data arus kas'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">List arus kas</h5>
                </div>

                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        @foreach($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($akuns as $akun)
                        {{-- Parent akun --}}
                        <tr>
                            <td colspan="{{ count($headers) }}"><strong>{{ $akun->nama_akun }}</strong></td>
                        </tr>

                        {{-- Jurnal parent --}}
                        @foreach($akun->jurnals as $jurnal)
                            <tr>
                                <td>{{ $jurnal->unit_id ?? '-' }}</td>
                                <td>{{ optional($jurnal->transaksi)->tanggal_transaksi ?
                            \Carbon\Carbon::parse($jurnal->transaksi->tanggal_transaksi)->format('d-m-Y H:i') : '-' }}</td>
                                <td>&nbsp;&nbsp;&nbsp;{{ $akun->nama_akun }}</td>
                                <td class="text-end">{{ number_format($jurnal->debit, 2) }}</td>
                                <td class="text-end">{{ number_format($jurnal->kredit, 2) }}</td>
                                <td class="text-end">{{ number_format($jurnal->debit > 0 ? $jurnal->debit : $jurnal->kredit, 2) }}</td>
                                <td>{{ $jurnal->transaksi->created_by ?? '-' }}</td>
                            </tr>
                        @endforeach

                        {{-- Anak akun --}}
                        @foreach($akun->children as $child)
                            <tr>
                                <td colspan="{{ count($headers) }}">
                                    &nbsp;&nbsp;&nbsp;└─ <strong>{{ $child->nama_akun }}</strong>
                                </td>
                            </tr>

                            @foreach($child->jurnals as $jurnal)
                                <tr>
                                    <td>{{ $jurnal->unit_id ?? '-' }}</td>
                                    <td>{{ optional($jurnal->transaksi)->tanggal_transaksi ?
                                \Carbon\Carbon::parse($jurnal->transaksi->tanggal_transaksi)->format('d-m-Y H:i') : '-' }}</td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $child->nama_akun }}</td>
                                    <td class="text-end">{{ number_format($jurnal->debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($jurnal->kredit, 2) }}</td>
                                    <td class="text-end">{{ number_format($jurnal->debit > 0 ? $jurnal->debit : $jurnal->kredit, 2) }}</td>
                                    <td>{{ $jurnal->transaksi->created_by ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

@endsection

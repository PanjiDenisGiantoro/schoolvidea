@extends('layouts.app')

@section('title', 'Laporan Tagihan')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/extra-libs/datatables.net-bs4/css/responsive.dataTables.min.css') }}">
@endpush

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 d-flex no-block align-items-center">
                <h4 class="page-title">Laporan Tagihan</h4>
                <div class="ms-auto text-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Laporan Tagihan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Filter Laporan</h5>
                <form action="{{ route('report.tagihan') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Unit</label>
                                <select name="unit_id" class="form-select">
                                    <option value="">Semua Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Kelas</label>
                                <select name="kelas_id" class="form-select">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                    <option value="belum_lunas" {{ request('status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="{{ route('report.tagihan') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success">Total Tagihan</h5>
                                <h3>Rp {{ number_format($summary['nominal_tagihan'], 0, ',', '.') }}</h3>
                                <p class="mb-0">{{ $summary['jumlah_data'] }} Data</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Total Dibayar</h5>
                                <h3>Rp {{ number_format($summary['sudah_dibayar'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning">Total Tunggakan</h5>
                                <h3>Rp {{ number_format($summary['belum_dibayar'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info">
                            <div class="card-body">
                                <h5 class="card-title text-info">Persentase Pembayaran</h5>
                                @php
                                    $persentase = $summary['nominal_tagihan'] > 0
                                        ? ($summary['sudah_dibayar'] / $summary['nominal_tagihan']) * 100
                                        : 0;
                                @endphp
                                <h3>{{ number_format($persentase, 2) }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tagihanTable" class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Nomor Induk</th>
                            <th>Nama Lengkap</th>
                            <th>Tagihan Unit</th>
                            <th>Tagihan Kelas</th>
                            <th>Item Tagihan</th>
                            <th>Jumlah Tagihan</th>
                            <th>Jumlah Dibayar</th>
                            <th>Tunggakan</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($tagihans as $tagihan)
                            @php
                                $ts = $tagihan->tagihanSiswa->first(); // ambil siswa pertama saja
                                $siswa = $ts->siswa ?? null;

                                $total_tagihan = $tagihan->items->sum('nominal') * ($tagihan->periode ?? 1);

                                $jumlah_dibayar = $tagihan->tagihanSiswa
                                    ->where('siswa_id', $siswa?->id)
                                    ->where('status', 1)
                                    ->count() * $tagihan->items->sum('nominal');

                                // jumlah tunggakan
                                $tunggakan = max($total_tagihan - $jumlah_dibayar, 0);
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa?->nisn ?? '-' }}</td>
                                <td>{{ $siswa?->user->name ?? '-' }}</td>
                                <td>{{ $tagihan->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $tagihan->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    @foreach($tagihan->items as $item)
                                        {{ $item->kategori->nama_kategori ?? '-' }}<br>
                                    @endforeach
                                </td>
                                <td>{{ $tagihan->jenis_tagihan ?? '-' }}</td>
                                <td>Rp {{ number_format($total_tagihan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($jumlah_dibayar, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($tunggakan, 0, ',', '.') }}</td>
                                <td>
                                    @if($tunggakan <= 0)
                                        <span class="badge bg-success rounded-pill">Lunas</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill">Belum Lunas</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('tagihan.show', [$tagihan->id, $siswa?->id]) }}" class="btn btn-sm btn-primary rounded-pill">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/extra-libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/extra-libs/datatables.net-bs4/js/dataTables.responsive.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#tagihanTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json'
                }
            });
        });
    </script>
@endpush

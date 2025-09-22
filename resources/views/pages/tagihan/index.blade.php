@extends('layouts.app')

@section('title', 'Kelola Tagihan')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">KELOLA TAGIHAN</h3>

        {{-- Action Buttons --}}
        <div class="d-flex mb-3">
            <a href="{{ url('tagihan/create') }}" class="btn btn-primary me-2 rounded-pill shadow-sm"><i class="fa fa-plus"></i> Tambah</a>
            <a href="#" class="btn btn-primary me-2 rounded-pill shadow-sm"><i class="fa fa-upload"></i> Impor</a>
            <a href="#" class="btn btn-primary me-2 rounded-pill shadow-sm"><i class="fa fa-download"></i> Ekspor</a>
            <a href="#" class="btn btn-primary rounded-pill shadow-sm"><i class="fa fa-sync"></i> Sinkron</a>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow-sm rounded-3 border-0">
                    <div class="card-body text-primary">
                        <h6>Jumlah Data</h6>
                        <h4>{{ $summary['jumlah_data'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm rounded-3 border-0">
                    <div class="card-body text-primary">
                        <h6>Nominal Tagihan</h6>
                        <h4>Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm rounded-3 border-0">
                    <div class="card-body text-primary">
                        <h6>Sudah Dibayar</h6>
                        <h4>Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow-sm rounded-3 border-0">
                    <div class="card-body text-primary">
                        <h6>Belum Dibayar</h6>
                        <h4>Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert jika ada error --}}
        @if(session('error'))
            <div class="alert alert-danger rounded-3 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="card mb-3 shadow-sm rounded-3 border-0">
            <div class="card-body">
                <form method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Filter Data</label>
                            <select name="filter" class="form-control rounded-pill">
                                <option value="all">All</option>
                                <option value="lunas">Lunas</option>
                                <option value="belum">Belum Lunas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control rounded-pill" value="{{ request('tanggal') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card shadow-sm rounded-3 border-0">
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Nomor Induk</th>
                        <th>Nama Lengkap</th>
                        <th>Tagihan Unit</th>
                        <th>Tagihan Kelas</th>
                        <th>Jml. Tagihan</th>
                        <th>Jml. Dibayar</th>
                        <th>Jml. Tunggakan</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    {{-- contoh dummy --}}
                    <tr>
                        <td>1</td>
                        <td>12345</td>
                        <td>Andi</td>
                        <td>SMP</td>
                        <td>9A</td>
                        <td>Rp 1.000.000</td>
                        <td>Rp 500.000</td>
                        <td>Rp 500.000</td>
                        <td><span class="badge bg-warning text-dark rounded-pill">Belum Lunas</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

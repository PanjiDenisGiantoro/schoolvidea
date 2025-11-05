@extends('layouts.app')

@section('title', 'Kelola Tagihan')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">KELOLA TAGIHAN</h3>

        {{-- Action Buttons --}}
        <div class="d-flex mb-3">
            <a href="{{ url('tagihan/create') }}" class="btn btn-primary rounded-pill me-2 shadow-sm">
                <i class="fa fa-plus"></i> Tambah
            </a>
            <a href="#" class="btn btn-primary rounded-pill me-2 shadow-sm">
                <i class="fa fa-upload"></i> Impor
            </a>
            <a href="#" class="btn btn-primary rounded-pill me-2 shadow-sm">
                <i class="fa fa-download"></i> Ekspor
            </a>
            <a href="#" class="btn btn-primary rounded-pill shadow-sm">
                <i class="fa fa-sync"></i> Sinkron
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm">
                    <div class="card-body text-primary">
                        <h6>Jumlah Data</h6>
                        <h4>{{ $summary['jumlah_data'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm">
                    <div class="card-body text-primary">
                        <h6>Nominal Tagihan</h6>
                        <h4>Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm">
                    <div class="card-body text-primary">
                        <h6>Sudah Dibayar</h6>
                        <h4>Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm">
                    <div class="card-body text-primary">
                        <h6>Belum Dibayar</h6>
                        <h4>Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert jika ada error --}}
        @if (session('error'))
            <div class="alert alert-danger rounded-3 shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="card rounded-3 mb-3 border-0 shadow-sm">
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
                            <input type="date" name="tanggal" class="form-control rounded-pill"
                                value="{{ request('tanggal') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card rounded-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table-bordered table-striped table text-center align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Nomor Induk</th>
                                <th>Nama Lengkap</th>
                                <th>Tagihan Unit</th>
                                <th>Tagihan Kelas</th>
                                <th>Item Tagihan</th>
                                <th>Type Tagihan</th>
                                <th>Periode</th>
                                <th>Jml. Tagihan</th>
                                <th>Jml. Dibayar</th>
                                <th>Jml. Tunggakan</th>
                                <th>Status</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tagihans as $tagihan)
                                @php
                                    $siswa = $tagihan->tagihanSiswa->first()?->siswa;

                                    $total_tagihan = $tagihan->items->sum('nominal') * ($tagihan->periode ?? 1);

                                    $jumlah_dibayar = $siswa
                                        ? $tagihan->tagihanSiswa
                                                ->where('siswa_id', $siswa->id)
                                                ->where('status', 1)
                                                ->count() * $tagihan->items->sum('nominal')
                                        : 0;

                                    $tunggakan = max($total_tagihan - $jumlah_dibayar, 0);
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $siswa?->nisn ?? '-' }}</td>
                                    <td>{{ $siswa?->user->name ?? '-' }}</td>
                                    <td>{{ $tagihan->unit->nama_unit ?? '-' }}</td>
                                    <td>{{ $tagihan->kelas->nama_kelas ?? '-' }}</td>
                                    <td>
                                        @foreach ($tagihan->items as $item)
                                            {{ $item->kategori->nama_kategori ?? '-' }}<br>
                                        @endforeach
                                    </td>
                                    <td>{{ $tagihan->jenis_tagihan ?? '-' }}</td>
                                    <td>{{ $tagihan->periode ?? '-' }} Bulan</td>

                                    <td>Rp {{ number_format($total_tagihan * ($tagihan->periode ?? 1), 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($jumlah_dibayar, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($tunggakan, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($tunggakan <= 0)
                                            <span class="badge bg-success rounded-pill">Lunas</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill">Belum Lunas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($siswa)
                                            <a href="{{ route('tagihan.show', [$tagihan->id, $siswa->id]) }}"
                                                class="btn btn-primary rounded-pill">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        @endif
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

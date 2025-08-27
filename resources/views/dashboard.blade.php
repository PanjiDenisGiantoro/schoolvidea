@extends('layouts.app')

@section('content')
    <!-- Start Container Fluid -->
    <div class="page-content">

        <div class="row">
            <!-- Card Petugas -->
            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2">Total Petugas</p>
                            <h4 class="fw-bold text-primary">25</h4>
                            <a href="{{ url('petugas') }}" class="text-decoration-underline">Lihat Detail</a>
                        </div>
                        <i class="ri-user-star-line fs-32 text-muted"></i>
                    </div>
                </div>
            </div>

            <!-- Card Unit -->
            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2">Unit</p>
                            <h4 class="fw-bold text-primary">12</h4>
                            <a href="{{ url('unit') }}" class="text-decoration-underline">Lihat Detail</a>
                        </div>
                        <i class="ri-building-2-line fs-32 text-muted"></i>
                    </div>
                </div>
            </div>

            <!-- Card Kelas -->
            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2">Jumlah Kelas</p>
                            <h4 class="fw-bold text-primary">36</h4>
                            <a href="{{ url('kelas') }}" class="text-decoration-underline">Lihat Detail</a>
                        </div>
                        <i class="ri-community-line fs-32 text-muted"></i>
                    </div>
                </div>
            </div>

            <!-- Card Siswa -->
            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2">Jumlah Siswa</p>
                            <h4 class="fw-bold text-primary">1280</h4>
                            <a href="{{ url('siswa') }}" class="text-decoration-underline">Lihat Detail</a>
                        </div>
                        <i class="ri-team-line fs-32 text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart / Ringkasan -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Statistik Siswa</h4>
                        <span class="text-muted">Per Tahun Ajaran</span>
                    </div>
                    <div class="card-body">
                        <div id="chartSiswa" class="apex-charts" data-colors="#604ae3,#0dcaf0,#198754"></div>
                    </div>
                </div>
            </div>

            <!-- Info Tabungan -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tabungan Siswa</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">Saldo Total</p>
                        <h3 class="fw-bold text-success">Rp 75.500.000</h3>
                        <p class="mt-3">Jumlah transaksi tabungan bulan ini: <b>560</b></p>
                        <a href="{{ url('tabungan') }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table contoh daftar terbaru -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Daftar Siswa Baru</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Tahun</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>2025001</td>
                                    <td>Andi Setiawan</td>
                                    <td>X IPA 1</td>
                                    <td>2025/2026</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                </tr>
                                <tr>
                                    <td>2025002</td>
                                    <td>Budi Hartono</td>
                                    <td>X IPS 2</td>
                                    <td>2025/2026</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                </tr>
                                <tr>
                                    <td>2025003</td>
                                    <td>Citra Dewi</td>
                                    <td>X IPA 2</td>
                                    <td>2025/2026</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @include('partials.vendor-scripts')
@endsection

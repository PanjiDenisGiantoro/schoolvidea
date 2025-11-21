@extends('layouts.app')
@section('title', 'Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tabungan.css') }}">
@endpush
@section('content')
    <!-- Start Container Fluid -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Total Petugas</p>
                            <h3 class="fw-bold text-success mb-0 text-absolute" >{{ $totalPetugas }}</h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="ri-user-star-line text-success" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="bx bx-star text-success"></i> <a href="{{ url('officer') }}" class="text-decoration-underline">Lihat Detail</a></small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-purple shadow-sm h-100 transition-all">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Total Petugas</p>
                            <h3 class="fw-bold text-info mb-0 text-absolute" >{{ $totalUnit }}</h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="ri-building-2-line text-info" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="bx bx-star text-success"></i> <a href="{{ url('unit') }}" class="text-decoration-underline">Lihat Detail</a></small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-yellow shadow-sm h-100 transition-all">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Jumlah Kelas</p>
                            <h3 class="fw-bold text-warning mb-0 text-absolute" >{{ $totalKelas }}</h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3">
                            <i class="ri-community-line text-warning" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="bx bx-star text-warning"></i> <a href="{{ url('kelas') }}" class="text-decoration-underline">Lihat Detail</a></small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-red shadow-sm h-100 transition-all">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Jumlah Siswa</p>
                            <h3 class="fw-bold text-danger mb-0 text-absolute" >{{ $totalSiswa }}</h3>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="ri-team-line text-danger" style="font-size: 24px;"></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="bx bx-up-arrow-alt text-success"></i> <a href="{{ url('siswa') }}" class="text-decoration-underline">Lihat Detail</a></small>
                </div>
            </div>
        </div>



        </div>

        <!-- Chart / Ringkasan -->
        <div class="row ">
            <div class="col-lg-8 mb-4">
                <div class="card stat-card-purple stat-card">
                    <div class="card-header d-flex justify-content-between mb-4">
                        <h4 class="card-title">Statistik Tagihan Pembayaran</h4>
                        <span class="text-muted">Per Tahun Ajaran</span>
                    </div>
                    <div class="card-body mb-3">
                        <div id="chartTagihan" class="apex-charts" data-colors="#604ae3,#0dcaf0,#198754"></div>
                    </div>
                </div>
            </div>
            <!-- Info Tabungan -->
            <div class="col-lg-4">
                <div class="card stat-card stat-card-green">
                    <div class="card-header">
                        <h4 class="card-title">Tabungan</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">Saldo Aktif</p>
                        <h3 class="fw-bold text-success">{{  number_format($totalSaldo, 0, ',', '.')  }}</h3>
                        <p class="mt-3">Jumlah transaksi tabungan bulan ini: <b>{{ number_format($jumlahTransaksi, 0, ',', '.') }}</b></p>
                        <a href="{{ url('tabungan') }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                    </div>
                </div>
                <div class="card stat-card-yellow stat-card">
                    <div class="card-header">
                        <h4 class="card-title">Tagihan</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">Saldo Total</p>
                        <h3 class="fw-bold text-success">{{ number_format($totalSaldo, 0, ',', '.') }}</h3>
                        <p class="mt-3">Jumlah transaksi tabungan bulan ini: <b>{{ number_format($jumlahTransaksi, 0, ',', '.') }}</b></p>
                        <a href="{{ url('tabungan') }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                    </div>
                </div>
            </div>
        </div>
    <!-- Table contoh daftar terbaru -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card stat-card-green stat-card">
                <div class="card-header">
                    <h4 class="card-title">Daftar Pembayaran Tagihan Terbaru</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                <tr>
                                    <th>NISN</th>
                                    <th>Nama Lengkap</th>
                                    <th>Tagihan Unit</th>
                                    <th>Tagihan Kelas</th>
                                    <th>Item Tagihan</th>
                                    <th>Tipe Tagihan</th>
                                    <th>Jml. Tagihan</th>
                                    <th>Jml. Dibayar</th>
                                    <th>Jml. Tunggakan</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($data as $row)
                                    <tr>
                                        <td>{{ $row['nomor_induk'] }}</td>
                                        <td>{{ $row['nama_lengkap'] }}</td>
                                        <td>{{ $row['tagihan_unit'] }}</td>
                                        <td>{{ $row['tagihan_kelas'] }}</td>
                                        <td>{{ $row['item_tagihan'] }}</td>
                                        <td>{{ $row['type_tagihan'] }}</td>
                                        <td>Rp {{ number_format($row['jml_tagihan'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($row['jml_dibayar'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($row['jml_tunggakan'], 0, ',', '.') }}</td>
                                        <td>
                                            @if($row['status'] === 'Lunas')
                                                <span class="badge bg-success">Lunas</span>
                                            @else
                                                <span class="badge bg-danger">Belum Lunas</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Tidak ada data tagihan baru</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>






@endsection
@push('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chartData = @json($datas);

            // Mapping bulan ke nama bulan
            const bulanLabels = chartData.map(item => {
                const bulan = item.bulan;
                const namaBulan = new Date(2000, bulan - 1, 1).toLocaleString('id-ID', { month: 'long' });
                return namaBulan;
            });

            // Series untuk chart
            const options = {
                chart: {
                    type: "bar",
                    height: 350,
                    stacked: false,
                    padding: {
                        top:0,
                        bottom:0,
                        left: 2,
                        right:2,
                    }
                },
                plotOptions: {
                    bar: {
                        columnWidth: '70%',

                    }
                },
                colors: ["#604ae3", "#0dcaf0", "#198754"],
                series: [
                    {
                        name: "Total Tagihan",
                        data: chartData.map(item => item.jml_tagihan)
                    },
                    {
                        name: "Total Dibayar",
                        data: chartData.map(item => item.jml_dibayar)
                    },
                    {
                        name: "Total Tunggakan",
                        data: chartData.map(item => item.jml_tunggakan)
                    }
                ],
                xaxis: {
                    categories: bulanLabels
                },
                yaxis: {
                    labels: {
                        formatter: val => "Rp " + val.toLocaleString("id-ID")
                    }
                },
                tooltip: {
                    y: {
                        formatter: val => "Rp " + val.toLocaleString("id-ID")
                    }
                }

            };

            const chart = new ApexCharts(document.querySelector("#chartTagihan"), options);
            chart.render();
        });
    </script>

@endpush

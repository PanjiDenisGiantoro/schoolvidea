@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    <!-- Start Container Fluid -->
    <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2">Total Petugas</p>
                            <h4 class="fw-bold text-primary">{{ $totalPetugas }}</h4>
                            <a href="{{ url('officer') }}" class="text-decoration-underline">Lihat Detail</a>
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
                            <h4 class="fw-bold text-primary">{{ $totalUnit }}</h4>
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
                            <h4 class="fw-bold text-primary">{{ $totalKelas }}</h4>
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
                            <h4 class="fw-bold text-primary">{{ $totalSiswa }}</h4>
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
                        <h4 class="card-title">Statistik Tagihan Pembayaran</h4>
                        <span class="text-muted">Per Tahun Ajaran</span>
                    </div>
                    <div class="card-body">
                        <div id="chartTagihan" class="apex-charts" data-colors="#604ae3,#0dcaf0,#198754"></div>
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
                        <h3 class="fw-bold text-success">{{ $totalSaldo }}</h3>
                        <p class="mt-3">Jumlah transaksi tabungan bulan ini: <b>{{ $jumlahTransaksi }}</b></p>
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
                        <h4 class="card-title">Daftar Pembayaran Tagihan Terbaru</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th>Nomor Induk</th>
                                        <th>Nama Lengkap</th>
                                        <th>Tagihan Unit</th>
                                        <th>Tagihan Kelas</th>
                                        <th>Item Tagihan</th>
                                        <th>Type Tagihan</th>
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
                    stacked: false
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


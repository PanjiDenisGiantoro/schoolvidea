di @extends('layouts.app')

@section('title', 'Kelola Tagihan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">KELOLA TAGIHAN</h3>

        {{-- Main Summary Cards --}}
        <div class="row g-3 mb-4">
            {{-- Total Data Card --}}
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-blue shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Jumlah Data</p>
                                <h3 class="fw-bold text-primary mb-0">{{ $summary['jumlah_data'] ?? 0 }}</h3>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="ri-file-list-3-line text-primary" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="ri-list-check text-primary"></i> Total tagihan</small>
                    </div>
                </div>
            </div>

            {{-- Nominal Tagihan Card --}}
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Nominal Tagihan</p>
                                <h3 class="fw-bold text-success mb-0">Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="ri-money-dollar-circle-line text-success" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="ri-arrow-up-line text-success"></i> Total masuk</small>
                    </div>
                </div>
            </div>

            {{-- Sudah Dibayar Card --}}
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-cyan shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Sudah Dibayar</p>
                                <h3 class="fw-bold text-info mb-0">Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="ri-checkbox-circle-line text-info" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="ri-check-double-line text-info"></i> Pembayaran</small>
                    </div>
                </div>
            </div>

            {{-- Belum Dibayar Card --}}
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-orange shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Belum Dibayar</p>
                                <h3 class="fw-bold text-warning mb-0">Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="ri-alert-line text-warning" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="ri-time-line text-warning"></i> Tunggakan</small>
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

        {{-- Tabel --}}
        <div class="card rounded-3 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold text-dark">Daftar Tagihan</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ url('tagihan/create') }}" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-plus"></i> Tambah
                        </a>
                        <a href="#" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-upload"></i> Impor
                        </a>
                        <a href="#" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-download"></i> Ekspor
                        </a>
                        <a href="{{ route('tagihan.print_laporan', request()->all()) }}" target="_blank" class="btn btn-primary rounded-pill shadow-sm">
                            <i class="fa fa-print"></i> Cetak
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tagihanTable" class="table table-striped table-hover table-bordered align-middle table-sm">
                        <thead class="table-primary text-center align-middle">
                            <tr>
                                <th class="text-center" style="width: 4%">#</th>
                                <th style="width: 8%">NISN</th>
                                <th style="width: 14%">Nama Siswa</th>
                                <th style="width: 8%">Unit</th>
                                <th style="width: 8%">Kelas</th>
                                <th style="width: 14%">Nama Tagihan</th>
                                <th class="text-center" style="width: 10%">Jml. Tagihan</th>
                                <th class="text-center" style="width: 10%">Jml. Dibayar</th>
                                <th class="text-end" style="width: 10%">Jml. Tunggakan</th>
                                <th class="text-center" style="width: 8%">Tgl. Buat</th>
                                <th class="text-center" style="width: 6%">Status</th>
                                <th class="text-center" style="width: 6%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <style>
        /* Stat Cards Styling */
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 1rem !important;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            opacity: 0.5;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12) !important;
        }

        .stat-card-green::before {
            background: linear-gradient(90deg, transparent, #28a745, transparent);
        }

        .stat-card-blue::before {
            background: linear-gradient(90deg, transparent, #0d6efd, transparent);
        }

        .stat-card-cyan::before {
            background: linear-gradient(90deg, transparent, #0dcaf0, transparent);
        }

        .stat-card-orange::before {
            background: linear-gradient(90deg, transparent, #fd7e14, transparent);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.15) rotate(-5deg);
        }

        .transition-all {
            transition: all 0.3s ease;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 0.5rem;
            }

            .stat-card h3 {
                font-size: 1.5rem !important;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
            }

            .stat-icon i {
                font-size: 20px !important;
            }
        }

        @media (max-width: 576px) {
            .stat-card:hover {
                transform: translateY(-4px) scale(1.01);
            }

            .stat-card h3 {
                font-size: 1.25rem !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        let tagihanTable;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            tagihanTable = $('#tagihanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                ajax: {
                    url: '{{ route("tagihan.datatable") }}',
                    type: 'GET',
                    data: function(d) {
                        d.unit_id = document.getElementById('unit_id')?.value || '';
                        d.kelas_id = document.getElementById('kelas_id')?.value || '';
                        d.tagihan_status = document.getElementById('tagihan_status')?.value || '';
                        d.jenis_tagihan = document.getElementById('jenis_tagihan')?.value || '';
                        d.search = d.search.value;
                    },
                    error: function(xhr, status, error) {
                        console.error('DataTable AJAX Error:', error, xhr.responseText);
                    }
                },
                columns: [
                    { data: 'no', name: 'no', className: 'text-center', render: function(data) { return data; } },
                    { data: 'nisn', name: 'nisn', render: function(data) { return data || '-'; } },
                    { data: 'nama_siswa', name: 'nama_siswa', render: function(data) { return data || '-'; } },
                    { data: 'unit', name: 'unit', render: function(data) { return data || '-'; } },
                    { data: 'kelas', name: 'kelas', render: function(data) { return data || '-'; } },
                    { data: 'nama_tagihan', name: 'nama_tagihan', render: function(data) { return data || '-'; } },
                    { data: 'jml_tagihan', name: 'jml_tagihan', className: 'text-center', render: function(data) { return data || '0x'; } },
                    { data: 'jml_dibayar', name: 'jml_dibayar', className: 'text-center', render: function(data) { return data || '0 bulan'; } },
                    { data: 'jml_tunggakan', name: 'jml_tunggakan', className: 'text-end', render: function(data) { return data || 'Rp 0'; } },
                    { data: 'created_at', name: 'created_at', className: 'text-center', render: function(data) { return data || '-'; } },
                    { data: 'status', name: 'status', className: 'text-center', orderable: false, render: function(data) { return data || '-'; } },
                    { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false, render: function(data) { return data || '-'; } }
                ],
                columnDefs: [
                    { orderable: false, targets: [0, 10, 11] }
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@endpush

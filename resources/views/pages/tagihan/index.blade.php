di @extends('layouts.app')

@section('title', 'Kelola Tagihan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">KELOLA TAGIHAN</h3>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-muted">Jumlah Data</p>
                            <h4 class="fw-bold text-primary">{{ $summary['jumlah_data'] ?? 0 }}</h4>
                        </div>
                        <i class="ri-file-list-3-line fs-32 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-muted">Nominal Tagihan</p>
                            <h4 class="fw-bold text-success">Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</h4>
                        </div>
                        <i class="ri-money-dollar-circle-line fs-32 text-success opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-muted">Sudah Dibayar</p>
                            <h4 class="fw-bold text-info">Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                        </div>
                        <i class="ri-checkbox-circle-line fs-32 text-info opacity-50"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card mini-stats">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-2 text-muted">Belum Dibayar</p>
                            <h4 class="fw-bold text-warning">Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                        </div>
                        <i class="ri-alert-line fs-32 text-warning opacity-50"></i>
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
                                <th class="text-end" style="width: 10%">Jml. Tagihan</th>
                                <th class="text-end" style="width: 10%">Jml. Dibayar</th>
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
                    { data: 'jml_tagihan', name: 'jml_tagihan', className: 'text-end', render: function(data) { return data || 'Rp 0'; } },
                    { data: 'jml_dibayar', name: 'jml_dibayar', className: 'text-end', render: function(data) { return data || 'Rp 0'; } },
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

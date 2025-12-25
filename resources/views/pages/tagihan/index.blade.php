@extends('layouts.app')

@section('title', 'Kelola Tagihan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/tabungan.css') }}">
    @endpush
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">KELOLA TAGIHAN</h3>

        {{-- Main Summary Cards --}}



        {{-- Summary Cards --}}
        <div class="row mb-4">

            {{-- Total Data Card --}}
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-purple shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Jumlah Data</p>
                                <h3 class="fw-bold text-info mb-0 text-absolute">{{ $summary['jumlah_data'] ?? 0 }}</h3>
                            </div>
                            <div class="stat-icon bg-info bg-opacity-10 rounded-3 p-3">
                                <i class="ri-file-list-3-line text-info" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="ri-list-check text-info"></i> Total tagihan</small>
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
                                <h3 class="fw-bold text-success mb-0 text-absolute">Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-success bg-opacity-10 rounded-3 p-3">
                                <i class="ri-money-dollar-circle-line text-success" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="ri-arrow-up-line text-success"></i> Total masuk</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-blue shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Sudah Dibayar</p>
                                <h3 class="fw-bold text-primary mb-0 text-absolute">Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-check-circle text-primary" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bx bx-check-circle text-primary"></i> Nominal Tagihan Yang Sudah Dibayar</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="card border-0 rounded-4 overflow-hidden stat-card stat-card-yellow shadow-sm h-100 transition-all">
                    <div class="card-body position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted fw-500 mb-1 text-uppercase" style="font-size: 12px; letter-spacing: 0.5px;">Belum Dibayar</p>
                                <h3 class="fw-bold text-warning mb-0 text-absolute">Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</h3>
                            </div>
                            <div class="stat-icon bg-warning bg-opacity-10 rounded-3 p-3">
                                <i class="bx bx-time text-warning" style="font-size: 24px;"></i>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bx bx-time text-warning"></i> Nominal Tagihan Yang Belum Dibayar</small>
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

        {{-- Filter Card --}}
        <div class="card rounded-3 mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="justify-content-between align-items-center d-flex gap-3"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse"
                    style="cursor: pointer;">
                    <h5 class="fw-bold text-primary mt-3">
                        <span><i class="bx bx-filter"></i> Filter Tagihan</span>
                    </h5>
                    <h5 class="fw-bold text-primary" style="font-size: 26px;"><i class="bx bx-chevron-down"></i></h5>
                </div>

                <form action="{{ route('tagihan.index') }}" method="GET">
                    <div class="row g-3 collapse" id="filterCollapse">
                        {{-- Filter Unit --}}
                        @if(auth()->user()->yayasan_id && !auth()->user()->unit_id || !auth()->user()->yayasan_id && !auth()->user()->unit_id)
                        <div class="col-md-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select">
                                <option value="">Pilih Unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Filter Jenis Tagihan --}}
                        <div class="col-md-3">
                            <label for="jenis_tagihan" class="form-label">Jenis Tagihan</label>
                            <select name="jenis_tagihan" id="jenis_tagihan" class="form-select">
                                <option value="">Semua Jenis</option>
                                <option value="bulanan" {{ request('jenis_tagihan') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                <option value="sekali_bayar" {{ request('jenis_tagihan') == 'sekali_bayar' ? 'selected' : '' }}>Sekali Bayar</option>
                            </select>
                        </div>

                        {{-- Filter Status --}}
                        <div class="col-md-3">
                            <label for="status_tagihan" class="form-label">Status Tagihan</label>
                            <select name="status_tagihan" id="status_tagihan" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="lunas" {{ request('status_tagihan') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="belum_lunas" {{ request('status_tagihan') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="cicilan" {{ request('status_tagihan') == 'cicilan' ? 'selected' : '' }}>Cicilan</option>
                            </select>
                        </div>

                        {{-- Filter Kelas --}}
                        <div class="col-md-3">
                            <label for="kelas_id" class="form-label">Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-select">
                                <option value="">Semua Kelas</option>
                                @if(isset($kelas))
                                    @foreach ($kelas as $k)
                                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        {{-- Filter Nama Siswa --}}
                        <div class="col-md-3">
                            <label for="nama_siswa" class="form-label">Nama/NISN Siswa</label>
                            <input type="text" name="nama_siswa" id="nama_siswa" class="form-control p-3"
                                placeholder="Cari nama atau NISN" value="{{ request('nama_siswa') }}">
                        </div>

                        {{-- Filter Tanggal Dari --}}
                        <div class="col-md-3">
                            <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                            <input type="text" name="dari_tanggal" id="dari_tanggal" class="form-control datepicker p-3"
                                placeholder="DD/MM/YYYY"
                                value="{{ request('dari_tanggal') }}">
                        </div>

                        {{-- Filter Tanggal Sampai --}}
                        <div class="col-md-3">
                            <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                            <input type="text" name="sampai_tanggal" id="sampai_tanggal" class="form-control datepicker p-3"
                                placeholder="DD/MM/YYYY"
                                value="{{ request('sampai_tanggal') }}">
                        </div>

                        {{-- Tombol Filter --}}
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('tagihan.index') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
                    <table id="tagihanTable" class="table table-striped table-hover table-bordered align-middle table-sm
                            text-nowrap">
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
    <style>
        /* Horizontal scroll untuk tabel */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Firefox scrollbar styling */
        .table-responsive {
            scrollbar-color: #888 #f1f1f1;
            scrollbar-width: thin;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        let tagihanTable;
        let filterTimeout;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTable
            tagihanTable = $('#tagihanTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: false,
                "scrollX": true,
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
                        d.status_tagihan = document.getElementById('status_tagihan')?.value || '';
                        d.jenis_tagihan = document.getElementById('jenis_tagihan')?.value || '';
                        d.nama_siswa = document.getElementById('nama_siswa')?.value || '';
                        d.dari_tanggal = document.getElementById('dari_tanggal')?.value || '';
                        d.sampai_tanggal = document.getElementById('sampai_tanggal')?.value || '';
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

            // Initialize datepicker dengan format DD/MM/YYYY
            flatpickr('.datepicker', {
                dateFormat: 'd/m/Y',
                allowInput: true
            });

            // Debounce function untuk filter dengan delay 5 detik
            function debounceFilter() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    tagihanTable.ajax.reload();
                }, 5000); // 5 detik delay
            }

            // Dynamic kelas loading based on unit selection
            const unitSelect = document.getElementById('unit_id');
            const kelasSelect = document.getElementById('kelas_id');

            if (unitSelect) {
                unitSelect.addEventListener('change', function() {
                    const unitId = this.value;

                    // Load kelas berdasarkan unit
                    if (unitId) {
                        fetch(`/api/kelas-by-unit/${unitId}`)
                            .then(response => response.json())
                            .then(data => {
                                kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
                                data.forEach(kelas => {
                                    kelasSelect.innerHTML += `<option value="${kelas.id}">${kelas.nama_kelas}</option>`;
                                });
                            })
                            .catch(error => {
                                console.error('Error loading kelas:', error);
                            });
                    } else {
                        kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
                    }

                    // Reload table langsung tanpa delay untuk unit
                    tagihanTable.ajax.reload();
                });
            }

            // Event listener untuk filter kelas dengan debounce 5 detik
            if (kelasSelect) {
                kelasSelect.addEventListener('change', function() {
                    debounceFilter();
                });
            }

            // Event listener untuk filter nama siswa dengan debounce 5 detik
            const namaSiswaInput = document.getElementById('nama_siswa');
            if (namaSiswaInput) {
                namaSiswaInput.addEventListener('input', function() {
                    debounceFilter();
                });
            }
        });
    </script>
@endpush
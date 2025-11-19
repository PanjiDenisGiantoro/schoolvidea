di @extends('layouts.app')

@section('title', 'Kelola Tagihan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">KELOLA TAGIHAN</h3>

        {{-- Action Buttons & Pagination Control --}}


        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center text-white bg-info shadow-sm">
                    <div class="card-body">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Jumlah Data</h6>
                        <h4 class="text-white">{{ $summary['jumlah_data'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center text-white bg-success shadow-sm">
                    <div class="card-body">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Nominal Tagihan</h6>
                        <h4 class="text-white">Rp {{ number_format($summary['nominal_tagihan'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm text-white bg-warning">
                    <div class="card-body">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Sudah Dibayar</h6>
                        <h4 class="text-white">Rp {{ number_format($summary['sudah_dibayar'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-3 border-0 text-center shadow-sm bg-danger">
                    <div class="card-body text-primary">
                        <h6 class="text-white fw-bold" style="font-size: 14px">Belum Dibayar</h6>
                        <h4 class="text-white">Rp {{ number_format($summary['belum_dibayar'] ?? 0, 0, ',', '.') }}</h4>
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
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bx bx-filter"></i> Filter Tagihan
                </h5>
                <form method="GET" action="{{ route('tagihan.index') }}">
                    <div class="row g-3">
                        {{-- Filter Search Nama/Tagihan --}}
                        <div class="col-md-2">
                            <label for="search" class="form-label fw-semibold">Cari Nama/Tagihan</label>
                            <input type="text" name="search" id="search"
                                   class="form-control" placeholder="Nama siswa, tagihan..."
                                   value="{{ request('search') }}">
                        </div>

                        {{-- Filter Unit --}}
                        @if(auth()->user()->yayasan_id && !auth()->user()->unit_id || !auth()->user()->yayasan_id && !auth()->user()->unit_id)
                        <div class="col-md-2">
                            <label for="unit_id" class="form-label fw-semibold">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select">
                                <option value="">Semua Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Filter Kelas --}}
                        <div class="col-md-2">
                            <label for="kelas_id" class="form-label fw-semibold">Kelas</label>
                            <select name="kelas_id" id="kelas_id" class="form-select">
                                <option value="">Semua Kelas</option>
                            </select>
                        </div>

                        {{-- Filter Tagihan Status --}}
                        <div class="col-md-2">
                            <label for="tagihan_status" class="form-label fw-semibold">Status Pembayaran</label>
                            <select name="tagihan_status" id="tagihan_status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="lunas" {{ request('tagihan_status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="belum_lunas" {{ request('tagihan_status') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            </select>
                        </div>

                        {{-- Filter Jenis Tagihan --}}
                        <div class="col-md-2">
                            <label for="jenis_tagihan" class="form-label fw-semibold">Jenis Tagihan</label>
                            <select name="jenis_tagihan" id="jenis_tagihan" class="form-select">
                                <option value="">Semua Jenis</option>
                                <option value="bulanan" {{ request('jenis_tagihan') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                <option value="bebas" {{ request('jenis_tagihan') == 'bebas' ? 'selected' : '' }}>Bebas</option>
                            </select>
                        </div>

                        {{-- Tombol Filter --}}
                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('tagihan.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <i class="bx bx-refresh"></i>
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
                    <table id="tagihanTable" class="table table-striped table-hover table-bordered align-middle">
                        <thead class="table-primary text-center text-nowrap align-middle">
                            <tr>
                                <th class="text-center">#</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Unit</th>
                                <th>Kelas</th>
                                <th>Nama Tagihan</th>
                                <th>Jenis</th>
                                <th>Periode</th>
                                <th class="text-end">Jml. Tagihan</th>
                                <th class="text-end">Jml. Dibayar</th>
                                <th class="text-end">Jml. Tunggakan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
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
                    }
                },
                columns: [
                    { data: 'no', name: 'no', className: 'text-center', width: '5%' },
                    { data: 'nisn', name: 'nisn', width: '10%' },
                    { data: 'nama_siswa', name: 'nama_siswa', width: '15%' },
                    { data: 'unit', name: 'unit', width: '10%' },
                    { data: 'kelas', name: 'kelas', width: '10%' },
                    { data: 'nama_tagihan', name: 'nama_tagihan', width: '15%' },
                    { data: 'jenis_tagihan', name: 'jenis_tagihan', width: '8%' },
                    { data: 'periode', name: 'periode', width: '10%' },
                    { data: 'jml_tagihan', name: 'jml_tagihan', className: 'text-end', width: '12%' },
                    { data: 'jml_dibayar', name: 'jml_dibayar', className: 'text-end', width: '12%' },
                    { data: 'jml_tunggakan', name: 'jml_tunggakan', className: 'text-end', width: '12%' },
                    { data: 'status', name: 'status', className: 'text-center', orderable: false, width: '10%' },
                    { data: 'action', name: 'action', className: 'text-center', orderable: false, searchable: false, width: '8%' }
                ],
                columnDefs: [
                    { orderable: false, targets: [0, 11, 12] }
                ],
                order: [[2, 'asc']],
                initComplete: function() {
                    setupFilterListeners();
                }
            });

            // Setup filter listeners
            function setupFilterListeners() {
                const filterElements = ['search', 'unit_id', 'kelas_id', 'tagihan_status', 'jenis_tagihan'];

                filterElements.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.addEventListener('change', function() {
                            tagihanTable.ajax.reload();
                        });
                        // For search input, use keyup event
                        if (id === 'search') {
                            element.addEventListener('keyup', function() {
                                tagihanTable.search(this.value).draw();
                            });
                        }
                    }
                });
            }

            // Load kelas berdasarkan unit yang dipilih
            document.getElementById('unit_id')?.addEventListener('change', function() {
                const unitId = this.value;
                const kelasSelect = document.getElementById('kelas_id');

                if (!unitId) {
                    kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
                    return;
                }

                fetch(`/api/kelas-by-unit/${unitId}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '<option value="">Semua Kelas</option>';
                        if (data.kelas && Array.isArray(data.kelas)) {
                            data.kelas.forEach(kelas => {
                                html += `<option value="${kelas.id}">${kelas.nama_kelas}</option>`;
                            });
                        }
                        kelasSelect.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error loading kelas:', error);
                        kelasSelect.innerHTML = '<option value="">Semua Kelas</option>';
                    });
            });

            // Trigger change event ketika halaman pertama kali load jika ada unit_id
            const unitSelect = document.getElementById('unit_id');
            const selectedUnitId = '{{ request("unit_id") }}';

            if (unitSelect && selectedUnitId) {
                unitSelect.value = selectedUnitId;
                unitSelect.dispatchEvent(new Event('change'));

                // Set kelas yang dipilih setelah kelas di-load
                setTimeout(() => {
                    const kelasSelect = document.getElementById('kelas_id');
                    if (kelasSelect) {
                        kelasSelect.value = '{{ request("kelas_id") }}';
                    }
                }, 300);
            }
        });
    </script>
@endpush

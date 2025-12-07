@extends('layouts.app')

@section('title', 'Migrasi Data')

@section('content')
    @include('partials.page-title', [
        'title' => 'Migrasi Data',
        'subTitle' => 'Import & Export Data'
    ])

    {{-- Summary Statistics --}}
    <div class="row mb-4">
        @foreach($totals as $total)
        <div class="col-12 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bx bx-buildings me-2 text-primary"></i>{{ $total['unit'] }}
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <div class="p-3 bg-primary-subtle rounded text-center">
                                <i class="bx bx-user fs-2 text-primary"></i>
                                <h4 class="mt-2 mb-0">{{ $total['siswa'] }}</h4>
                                <p class="text-muted mb-0 small">Siswa</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-success-subtle rounded text-center">
                                <i class="bx bx-chalkboard fs-2 text-success"></i>
                                <h4 class="mt-2 mb-0">{{ $total['kelas'] }}</h4>
                                <p class="text-muted mb-0 small">Kelas</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-info-subtle rounded text-center">
                                <i class="bx bx-id-card fs-2 text-info"></i>
                                <h4 class="mt-2 mb-0">{{ $total['officer'] }}</h4>
                                <p class="text-muted mb-0 small">Officer</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-warning-subtle rounded text-center">
                                <i class="bx bx-briefcase fs-2 text-warning"></i>
                                <h4 class="mt-2 mb-0">{{ $total['jurusan'] }}</h4>
                                <p class="text-muted mb-0 small">Jurusan</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-danger-subtle rounded text-center">
                                <i class="bx bx-shield fs-2 text-danger"></i>
                                <h4 class="mt-2 mb-0">{{ $total['role'] }}</h4>
                                <p class="text-muted mb-0 small">Roles</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="p-3 bg-secondary-subtle rounded text-center">
                                <i class="bx bx-briefcase-alt fs-2 text-secondary"></i>
                                <h4 class="mt-2 mb-0">{{ $total['position'] }}</h4>
                                <p class="text-muted mb-0 small">Positions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Import & Export Tabs --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-tabs card-header-tabs" id="migrasiTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="import-tab" data-bs-toggle="tab" data-bs-target="#import" type="button" role="tab">
                        <i class="bx bx-import me-2"></i>Import Data
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="export-tab" data-bs-toggle="tab" data-bs-target="#export" type="button" role="tab">
                        <i class="bx bx-export me-2"></i>Export Data
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="migrasiTabContent">
                {{-- Import Tab --}}
                <div class="tab-pane fade show active" id="import" role="tabpanel">
                    <div class="row g-4">
                        {{-- Import Siswa --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm rounded-circle bg-primary-subtle me-3">
                                            <i class="bx bx-user fs-4 text-primary"></i>
                                        </div>
                                        <h5 class="mb-0">Import Siswa</h5>
                                    </div>
                                    <p class="text-muted small">Upload file Excel untuk mengimport data siswa</p>
                                    <form action="{{ route('import.siswa') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Unit</label>
                                            <select name="unit_id" class="form-select" required>
                                                <option value="">Pilih Unit</option>
                                                @foreach($unit_migrasi as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tahun Ajaran</label>
                                            <select name="tahun_ajaran_id" class="form-select" required>
                                                <option value="">Pilih Tahun Ajaran</option>
                                                @foreach($tahun_ajaran as $ta)
                                                    <option value="{{ $ta->id }}">{{ $ta->tahun_ajaran }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">File Excel</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-upload me-1"></i> Import
                                            </button>
                                            <a href="{{ route('export.exportSiswa') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-download me-1"></i> Download Template
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Import Kelas --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm rounded-circle bg-success-subtle me-3">
                                            <i class="bx bx-chalkboard fs-4 text-success"></i>
                                        </div>
                                        <h5 class="mb-0">Import Kelas</h5>
                                    </div>
                                    <p class="text-muted small">Upload file Excel untuk mengimport data kelas</p>
                                    <form action="{{ route('import.kelas') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Unit</label>
                                            <select name="unit_id" class="form-select" required>
                                                <option value="">Pilih Unit</option>
                                                @foreach($unit_migrasi as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tahun Ajaran</label>
                                            <select name="tahun_ajaran_id" class="form-select" required>
                                                <option value="">Pilih Tahun Ajaran</option>
                                                @foreach($tahun_ajaran as $ta)
                                                    <option value="{{ $ta->id }}">{{ $ta->tahun_ajaran }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">File Excel</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-success">
                                                <i class="bx bx-upload me-1"></i> Import
                                            </button>
                                            <a href="{{ route('export.exportkelas') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-download me-1"></i> Download Template
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Import Officer --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm rounded-circle bg-info-subtle me-3">
                                            <i class="bx bx-id-card fs-4 text-info"></i>
                                        </div>
                                        <h5 class="mb-0">Import Officer</h5>
                                    </div>
                                    <p class="text-muted small">Upload file Excel untuk mengimport data officer</p>
                                    <form action="{{ route('import.officer') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Unit</label>
                                            <select name="unit_id" class="form-select" required>
                                                <option value="">Pilih Unit</option>
                                                @foreach($unit_migrasi as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tahun Ajaran</label>
                                            <select name="tahun_ajaran_id" class="form-select" required>
                                                <option value="">Pilih Tahun Ajaran</option>
                                                @foreach($tahun_ajaran as $ta)
                                                    <option value="{{ $ta->id }}">{{ $ta->tahun_ajaran }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">File Excel</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-info">
                                                <i class="bx bx-upload me-1"></i> Import
                                            </button>
                                            <a href="{{ route('export.officerexport') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-download me-1"></i> Download Template
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Import Jurusan --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm rounded-circle bg-warning-subtle me-3">
                                            <i class="bx bx-briefcase fs-4 text-warning"></i>
                                        </div>
                                        <h5 class="mb-0">Import Jurusan</h5>
                                    </div>
                                    <p class="text-muted small">Upload file Excel untuk mengimport data jurusan</p>
                                    <form action="{{ route('import.jurusan') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Unit</label>
                                            <select name="unit_id" class="form-select" required>
                                                <option value="">Pilih Unit</option>
                                                @foreach($unit_migrasi as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tahun Ajaran</label>
                                            <select name="tahun_ajaran_id" class="form-select" required>
                                                <option value="">Pilih Tahun Ajaran</option>
                                                @foreach($tahun_ajaran as $ta)
                                                    <option value="{{ $ta->id }}">{{ $ta->tahun_ajaran }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">File Excel</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-upload me-1"></i> Import
                                            </button>
                                            <a href="{{ route('export.jurusantkelas') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-download me-1"></i> Download Template
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Import Role --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm rounded-circle bg-danger-subtle me-3">
                                            <i class="bx bx-shield fs-4 text-danger"></i>
                                        </div>
                                        <h5 class="mb-0">Import Role</h5>
                                    </div>
                                    <p class="text-muted small">Upload file Excel untuk mengimport data role</p>
                                    <form action="{{ route('import.role') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">File Excel</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bx bx-upload me-1"></i> Import
                                            </button>
                                            <a href="{{ route('export.roleexport') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-download me-1"></i> Download Template
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Import Position --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-sm rounded-circle bg-secondary-subtle me-3">
                                            <i class="bx bx-briefcase-alt fs-4 text-secondary"></i>
                                        </div>
                                        <h5 class="mb-0">Import Position</h5>
                                    </div>
                                    <p class="text-muted small">Upload file Excel untuk mengimport data position</p>
                                    <form action="{{ route('import.position') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">File Excel</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-secondary">
                                                <i class="bx bx-upload me-1"></i> Import
                                            </button>
                                            <a href="{{ route('export.positionexport') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="bx bx-download me-1"></i> Download Template
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Export Tab --}}
                <div class="tab-pane fade" id="export" role="tabpanel">
                    <div class="row g-4">
                        {{-- Export Siswa --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-md rounded-circle bg-primary-subtle mx-auto mb-3">
                                        <i class="bx bx-user fs-1 text-primary"></i>
                                    </div>
                                    <h5 class="mb-2">Export Siswa</h5>
                                    <p class="text-muted small mb-3">Download data siswa dalam format Excel</p>
                                    <a href="{{ route('export.exportSiswa') }}" class="btn btn-primary">
                                        <i class="bx bx-download me-1"></i> Download Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Export Kelas --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-md rounded-circle bg-success-subtle mx-auto mb-3">
                                        <i class="bx bx-chalkboard fs-1 text-success"></i>
                                    </div>
                                    <h5 class="mb-2">Export Kelas</h5>
                                    <p class="text-muted small mb-3">Download data kelas dalam format Excel</p>
                                    <a href="{{ route('export.exportkelas') }}" class="btn btn-success">
                                        <i class="bx bx-download me-1"></i> Download Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Export Officer --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-md rounded-circle bg-info-subtle mx-auto mb-3">
                                        <i class="bx bx-id-card fs-1 text-info"></i>
                                    </div>
                                    <h5 class="mb-2">Export Officer</h5>
                                    <p class="text-muted small mb-3">Download data officer dalam format Excel</p>
                                    <a href="{{ route('export.officerexport') }}" class="btn btn-info">
                                        <i class="bx bx-download me-1"></i> Download Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Export Jurusan --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-md rounded-circle bg-warning-subtle mx-auto mb-3">
                                        <i class="bx bx-briefcase fs-1 text-warning"></i>
                                    </div>
                                    <h5 class="mb-2">Export Jurusan</h5>
                                    <p class="text-muted small mb-3">Download data jurusan dalam format Excel</p>
                                    <a href="{{ route('export.jurusantkelas') }}" class="btn btn-warning">
                                        <i class="bx bx-download me-1"></i> Download Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Export Role --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-md rounded-circle bg-danger-subtle mx-auto mb-3">
                                        <i class="bx bx-shield fs-1 text-danger"></i>
                                    </div>
                                    <h5 class="mb-2">Export Role</h5>
                                    <p class="text-muted small mb-3">Download data role dalam format Excel</p>
                                    <a href="{{ route('export.roleexport') }}" class="btn btn-danger">
                                        <i class="bx bx-download me-1"></i> Download Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Export Position --}}
                        <div class="col-md-6 col-lg-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-md rounded-circle bg-secondary-subtle mx-auto mb-3">
                                        <i class="bx bx-briefcase-alt fs-1 text-secondary"></i>
                                    </div>
                                    <h5 class="mb-2">Export Position</h5>
                                    <p class="text-muted small mb-3">Download data position dalam format Excel</p>
                                    <a href="{{ route('export.positionexport') }}" class="btn btn-secondary">
                                        <i class="bx bx-download me-1"></i> Download Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-md {
        width: 4rem;
        height: 4rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.1);
    }

    .bg-success-subtle {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .bg-info-subtle {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.1);
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
    }

    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        background: transparent;
    }
</style>
@endpush

@push('scripts')
<script>
    // Show success/error messages if any
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#28a745'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#dc3545'
        });
    @endif

    // Confirm before submit
    $('form').on('submit', function(e) {
        if (!$(this).data('confirmed')) {
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin melanjutkan proses ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.data('confirmed', true).submit();
                }
            });
        }
    });
</script>
@endpush

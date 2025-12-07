@extends('layouts.app')

@section('title', 'Laporan Tagihan Siswa')

@section('content')
    @include('partials.page-title', [
        'title' => 'Laporan Tagihan Siswa',
        'subTitle' => 'Laporan / Keuangan'
    ])

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-info-subtle">
                                <span class="avatar-title text-info rounded-circle fs-3">
                                    <i class="bx bx-receipt"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Data</p>
                            <h4 class="mb-0">{{ $summary['jumlah_data'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary-subtle">
                                <span class="avatar-title text-primary rounded-circle fs-3">
                                    <i class="bx bx-dollar-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Tagihan</p>
                            <h4 class="mb-0">Rp {{ number_format($summary['nominal_tagihan'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-success-subtle">
                                <span class="avatar-title text-success rounded-circle fs-3">
                                    <i class="bx bx-check-circle"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Dibayar</p>
                            <h4 class="mb-0">Rp {{ number_format($summary['sudah_dibayar'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-warning-subtle">
                                <span class="avatar-title text-warning rounded-circle fs-3">
                                    <i class="bx bx-time-five"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Tunggakan</p>
                            <h4 class="mb-0">Rp {{ number_format($summary['belum_dibayar'], 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report.tagihan') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" class="form-select">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ $unit_id == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ $kelas_id == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="lunas" {{ $status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum_lunas" {{ $status == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan NISN atau nama siswa..." value="{{ $search }}">
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('report.tagihan') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-reset me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-receipt me-2"></i>Data Tagihan Siswa
                </h5>
                <div class="btn-group">
                    <a href="{{ route('report.tagihan', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-sm btn-success">
                        <i class="bx bx-file me-1"></i> Excel
                    </a>
                    <a href="{{ route('report.tagihan', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-sm btn-danger">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="bx bx-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-bordered" id="tagihanTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 4%;">#</th>
                            <th style="width: 10%;">NISN</th>
                            <th style="width: 18%;">Nama Siswa</th>
                            <th style="width: 10%;">Unit</th>
                            <th style="width: 10%;">Kelas</th>
                            <th style="width: 15%;">Nama Tagihan</th>
                            <th style="width: 10%;" class="text-end">Nominal</th>
                            <th style="width: 10%;" class="text-end">Dibayar</th>
                            <th style="width: 10%;" class="text-end">Tunggakan</th>
                            <th style="width: 8%;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataDetails as $index => $detail)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $detail['siswa']->nisn ?? '-' }}</td>
                                <td>{{ $detail['siswa']->user->name ?? '-' }}</td>
                                <td>{{ $detail['tagihan']->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $detail['tagihan']->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $detail['tagihan_siswa']->tagihanItem->kategori->nama_kategori ?? '-' }}</td>
                                <td class="text-end">
                                    <span class="text-primary fw-bold">
                                        Rp {{ number_format($detail['jumlah_tagihan'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-success fw-bold">
                                        Rp {{ number_format($detail['sudah_dibayar'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-{{ $detail['tunggakan'] > 0 ? 'danger' : 'muted' }} fw-bold">
                                        Rp {{ number_format($detail['tunggakan'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($detail['status_code'] == '1')
                                        <span class="badge bg-success rounded-pill">Lunas</span>
                                    @elseif($detail['status_code'] == '2')
                                        <span class="badge bg-warning rounded-pill">Cicilan</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill">Belum Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle fs-1"></i>
                                        <p class="mt-2">Tidak ada data tagihan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($dataDetails) > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="6" class="text-end">Total:</th>
                            <th class="text-end text-primary">
                                Rp {{ number_format($summary['nominal_tagihan'], 0, ',', '.') }}
                            </th>
                            <th class="text-end text-success">
                                Rp {{ number_format($summary['sudah_dibayar'], 0, ',', '.') }}
                            </th>
                            <th class="text-end text-danger">
                                Rp {{ number_format($summary['belum_dibayar'], 0, ',', '.') }}
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header .btn-group, .page-title, nav, .sidebar {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }

    .avatar-sm {
        width: 3rem;
        height: 3rem;
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#tagihanTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                url: '{{ asset("assets/datatables/id.json") }}'
            }
        });
    });
</script>
@endpush

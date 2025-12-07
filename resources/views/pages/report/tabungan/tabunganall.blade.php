@extends('layouts.app')

@section('title', 'Laporan Rekap Tabungan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Laporan Rekap Tabungan Siswa',
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
                                    <i class="bx bx-user"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Siswa</p>
                            <h4 class="mb-0">{{ $summary['jumlah_siswa'] ?? 0 }}</h4>
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
                                    <i class="bx bx-trending-up"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Setoran</p>
                            <h4 class="mb-0">Rp {{ number_format($summary['total_setoran'] ?? 0, 0, ',', '.') }}</h4>
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
                            <div class="avatar-sm rounded-circle bg-danger-subtle">
                                <span class="avatar-title text-danger rounded-circle fs-3">
                                    <i class="bx bx-trending-down"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Penarikan</p>
                            <h4 class="mb-0">Rp {{ number_format($summary['total_penarikan'] ?? 0, 0, ',', '.') }}</h4>
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
                                    <i class="bx bx-wallet"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Saldo</p>
                            <h4 class="mb-0">Rp {{ number_format($summary['total_saldo'] ?? 0, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('tabungan.report-all') }}" id="filterForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Dari</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Sampai</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Unit</label>
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-filter me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('tabungan.report-all') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-reset"></i>
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
                    <i class="bx bx-book-bookmark me-2"></i>Rekap Tabungan Siswa
                    <small class="text-muted">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</small>
                </h5>
                <div class="btn-group">
                    <a href="{{ route('tabungan.report-all', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-sm btn-success">
                        <i class="bx bx-file me-1"></i> Excel
                    </a>
                    <a href="{{ route('tabungan.report-all', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-sm btn-danger">
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
                <table class="table table-hover align-middle table-bordered" id="tabunganTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 12%;">NISN</th>
                            <th style="width: 25%;">Nama Siswa</th>
                            <th style="width: 12%;">Kelas</th>
                            <th style="width: 12%;">Unit</th>
                            <th style="width: 15%;" class="text-end">Total Setoran</th>
                            <th style="width: 15%;" class="text-end">Total Penarikan</th>
                            <th style="width: 15%;" class="text-end">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap as $index => $row)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $row['nisn'] ?? '-' }}</td>
                                <td>{{ $row['nama'] ?? '-' }}</td>
                                <td>{{ $row['kelas'] ?? '-' }}</td>
                                <td>{{ $row['unit'] ?? '-' }}</td>
                                <td class="text-end">
                                    <span class="text-success fw-bold">
                                        Rp {{ number_format($row['setoran'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-danger fw-bold">
                                        Rp {{ number_format($row['penarikan'] ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @php
                                        $saldoAkhir = $row['saldo_akhir'] ?? 0;
                                        $saldoClass = 'text-primary';
                                        if ($saldoAkhir == 0) {
                                            $saldoClass = 'text-muted';
                                        } elseif ($saldoAkhir < 100000) {
                                            $saldoClass = 'text-warning';
                                        }
                                    @endphp
                                    <span class="{{ $saldoClass }} fw-bold">
                                        Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle fs-1"></i>
                                        <p class="mt-2">Tidak ada data tabungan untuk periode ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($rekap) > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Total:</th>
                            <th class="text-end text-success">
                                Rp {{ number_format($summary['total_setoran'] ?? 0, 0, ',', '.') }}
                            </th>
                            <th class="text-end text-danger">
                                Rp {{ number_format($summary['total_penarikan'] ?? 0, 0, ',', '.') }}
                            </th>
                            <th class="text-end text-primary">
                                Rp {{ number_format($summary['total_saldo'] ?? 0, 0, ',', '.') }}
                            </th>
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

    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-info-subtle {
        background-color: rgba(23, 162, 184, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        @if(count($rekap) > 0)
        $('#tabunganTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                url: '{{ asset("assets/datatables/id.json") }}'
            },
            columnDefs: [
                { orderable: false, targets: 0 }
            ]
        });
        @endif
    });
</script>
@endpush

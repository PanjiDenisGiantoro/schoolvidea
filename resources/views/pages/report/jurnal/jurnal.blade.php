@extends('layouts.app')

@section('title', 'Laporan Jurnal Umum')

@section('content')
    @include('partials.page-title', [
        'title' => 'Laporan Jurnal Umum',
        'subTitle' => 'Laporan / Keuangan'
    ])

    <div class="row">
        {{-- Summary Cards --}}
        <div class="col-md-4 mb-4">
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
                            <p class="text-muted mb-1">Total Debit</p>
                            <h4 class="mb-0">Rp {{ number_format($jurnals->sum('debit'), 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
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
                            <p class="text-muted mb-1">Total Kredit</p>
                            <h4 class="mb-0">Rp {{ number_format($jurnals->sum('kredit'), 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
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
                            <p class="text-muted mb-1">Total Transaksi</p>
                            <h4 class="mb-0">{{ $jurnals->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report.jurnal') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Dari</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Sampai</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Akun</label>
                        <select name="akun_id" class="form-select" data-choices data-choices-sorting-false>
                            <option value="">Semua Akun</option>
                            @foreach($akuns as $akun)
                                <option value="{{ $akun->id }}" {{ $akun_id == $akun->id ? 'selected' : '' }}>
                                    {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                                </option>
                            @endforeach
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
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan keterangan, kode akun, atau nama akun..." value="{{ $search }}">
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('report.jurnal') }}" class="btn btn-secondary w-100">
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
                    <i class="bx bx-book-open me-2"></i>Jurnal Umum
                    <small class="text-muted">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</small>
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-success" onclick="exportTable('excel')">
                        <i class="bx bx-file me-1"></i> Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="exportTable('pdf')">
                        <i class="bx bxs-file-pdf me-1"></i> PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="bx bx-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-bordered" id="jurnalTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 12%;">Tanggal</th>
                            <th style="width: 20%;">Kode Akun</th>
                            <th style="width: 28%;">Nama Akun</th>
                            <th style="width: 20%;">Keterangan</th>
                            <th style="width: 12%;" class="text-end">Debit</th>
                            <th style="width: 12%;" class="text-end">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jurnals as $index => $jurnal)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark">
                                        {{ $jurnal->akun->kode_akun ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $jurnal->akun->nama_akun ?? '-' }}</td>
                                <td>
                                    <small class="text-muted">{{ $jurnal->keterangan }}</small>
                                </td>
                                <td class="text-end">
                                    @if($jurnal->debit > 0)
                                        <span class="text-success fw-bold">
                                            Rp {{ number_format($jurnal->debit, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($jurnal->kredit > 0)
                                        <span class="text-danger fw-bold">
                                            Rp {{ number_format($jurnal->kredit, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle fs-1"></i>
                                        <p class="mt-2">Tidak ada data jurnal untuk periode ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($jurnals->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Total:</th>
                            <th class="text-end text-success">
                                Rp {{ number_format($jurnals->sum('debit'), 0, ',', '.') }}
                            </th>
                            <th class="text-end text-danger">
                                Rp {{ number_format($jurnals->sum('kredit'), 0, ',', '.') }}
                            </th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Selisih:</th>
                            <th colspan="2" class="text-end {{ $jurnals->sum('debit') == $jurnals->sum('kredit') ? 'text-success' : 'text-warning' }}">
                                @php
                                    $selisih = $jurnals->sum('debit') - $jurnals->sum('kredit');
                                @endphp
                                Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                                @if($selisih == 0)
                                    <i class="bx bx-check-circle ms-1"></i>
                                @else
                                    <i class="bx bx-error-circle ms-1"></i>
                                @endif
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

    .bg-success-subtle {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-info-subtle {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#jurnalTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[1, 'desc']], // Sort by tanggal descending
            language: {
                url: '{{ asset("assets/datatables/id.json") }}'
            },
            dom: 'Bfrtip',
            buttons: []
        });
    });

    // Export functions
    function exportTable(type) {
        const params = new URLSearchParams(window.location.search);
        params.append('export', type);

        Swal.fire({
            title: 'Export ' + type.toUpperCase(),
            text: 'Fitur export sedang dalam pengembangan',
            icon: 'info',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }
</script>
@endpush

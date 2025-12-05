@extends('layouts.app')

@section('title', 'Laporan Buku Besar')

@section('content')
    @include('partials.page-title', [
        'title' => 'Laporan Buku Besar',
        'subTitle' => 'Laporan / Keuangan'
    ])

    {{-- Summary Cards --}}
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm rounded-circle bg-primary-subtle">
                                <span class="avatar-title text-primary rounded-circle fs-3">
                                    <i class="bx bx-book-bookmark"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Akun</p>
                            <h4 class="mb-0">{{ $akuns->count() }}</h4>
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
                            <p class="text-muted mb-1">Total Debit</p>
                            @php
                                $totalDebit = $akuns->sum(function($akun) {
                                    return $akun->jurnals->sum('debit');
                                });
                            @endphp
                            <h4 class="mb-0">Rp {{ number_format($totalDebit, 0, ',', '.') }}</h4>
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
                            <p class="text-muted mb-1">Total Kredit</p>
                            @php
                                $totalKredit = $akuns->sum(function($akun) {
                                    return $akun->jurnals->sum('kredit');
                                });
                            @endphp
                            <h4 class="mb-0">Rp {{ number_format($totalKredit, 0, ',', '.') }}</h4>
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
                            <div class="avatar-sm rounded-circle bg-info-subtle">
                                <span class="avatar-title text-info rounded-circle fs-3">
                                    <i class="bx bx-receipt"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Transaksi</p>
                            @php
                                $totalTransaksi = $akuns->sum(function($akun) {
                                    return $akun->jurnals->count();
                                });
                            @endphp
                            <h4 class="mb-0">{{ $totalTransaksi }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('report.buku_besar') }}" id="filterForm">
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
                        <label class="form-label">Tipe Akun</label>
                        <select name="tipe" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="ASET" {{ $tipe == 'ASET' ? 'selected' : '' }}>Aset</option>
                            <option value="LIABILITAS" {{ $tipe == 'LIABILITAS' ? 'selected' : '' }}>Liabilitas</option>
                            <option value="EKUITAS" {{ $tipe == 'EKUITAS' ? 'selected' : '' }}>Ekuitas</option>
                            <option value="PENDAPATAN" {{ $tipe == 'PENDAPATAN' ? 'selected' : '' }}>Pendapatan</option>
                            <option value="BEBAN" {{ $tipe == 'BEBAN' ? 'selected' : '' }}>Beban</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Akun Spesifik</label>
                        <select name="akun_id" class="form-select" data-choices data-choices-sorting-false>
                            <option value="">Semua Akun</option>
                            @foreach($allAkuns as $akun)
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
                        <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan kode akun atau nama akun..." value="{{ $search }}">
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('report.buku_besar') }}" class="btn btn-secondary w-100">
                            <i class="bx bx-reset me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-book-open me-2"></i>Buku Besar
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
            @if($akuns->count() > 0)
                <div class="accordion" id="bukuBesarAccordion">
                    @foreach($akuns as $index => $akun)
                        @php
                            $saldo = 0;
                            $totalDebitAkun = $akun->jurnals->sum('debit');
                            $totalKreditAkun = $akun->jurnals->sum('kredit');
                            $saldoAkhir = $totalDebitAkun - $totalKreditAkun;

                            // Determine badge color based on akun tipe
                            $badgeColor = match($akun->tipe) {
                                'ASET' => 'primary',
                                'LIABILITAS' => 'danger',
                                'EKUITAS' => 'warning',
                                'PENDAPATAN' => 'success',
                                'BEBAN' => 'info',
                                default => 'secondary'
                            };
                        @endphp

                        <div class="accordion-item border mb-2">
                            <h2 class="accordion-header" id="heading{{ $index }}">
                                <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                    <div class="d-flex w-100 justify-content-between align-items-center pe-3">
                                        <div>
                                            <span class="badge bg-{{ $badgeColor }} me-2">{{ $akun->kode_akun }}</span>
                                            <strong>{{ $akun->nama_akun }}</strong>
                                            <small class="text-muted ms-2">({{ $akun->tipe }})</small>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Saldo Akhir:</small>
                                            <strong class="{{ $saldoAkhir >= 0 ? 'text-success' : 'text-danger' }}">
                                                Rp {{ number_format(abs($saldoAkhir), 0, ',', '.') }}
                                            </strong>
                                            <small class="text-muted ms-1">({{ $akun->jurnals->count() }} transaksi)</small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                 aria-labelledby="heading{{ $index }}" data-bs-parent="#bukuBesarAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%;">#</th>
                                                    <th style="width: 15%;">Tanggal</th>
                                                    <th style="width: 35%;">Keterangan</th>
                                                    <th style="width: 15%;" class="text-end">Debit</th>
                                                    <th style="width: 15%;" class="text-end">Kredit</th>
                                                    <th style="width: 15%;" class="text-end">Saldo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $saldo = 0; @endphp
                                                @foreach($akun->jurnals as $jIndex => $jurnal)
                                                    @php
                                                        $saldo += $jurnal->debit - $jurnal->kredit;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $jIndex + 1 }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                                                        <td>
                                                            <small class="text-muted">{{ $jurnal->keterangan }}</small>
                                                        </td>
                                                        <td class="text-end">
                                                            @if($jurnal->debit > 0)
                                                                <span class="text-success">
                                                                    Rp {{ number_format($jurnal->debit, 0, ',', '.') }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            @if($jurnal->kredit > 0)
                                                                <span class="text-danger">
                                                                    Rp {{ number_format($jurnal->kredit, 0, ',', '.') }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            <strong class="{{ $saldo >= 0 ? 'text-success' : 'text-danger' }}">
                                                                Rp {{ number_format(abs($saldo), 0, ',', '.') }}
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <th colspan="3" class="text-end">Total:</th>
                                                    <th class="text-end text-success">
                                                        Rp {{ number_format($totalDebitAkun, 0, ',', '.') }}
                                                    </th>
                                                    <th class="text-end text-danger">
                                                        Rp {{ number_format($totalKreditAkun, 0, ',', '.') }}
                                                    </th>
                                                    <th class="text-end {{ $saldoAkhir >= 0 ? 'text-success' : 'text-danger' }}">
                                                        <strong>Rp {{ number_format(abs($saldoAkhir), 0, ',', '.') }}</strong>
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Quick Summary at Bottom --}}
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Total Debit</h6>
                            <h4 class="text-success mb-0">Rp {{ number_format($totalDebit, 0, ',', '.') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Total Kredit</h6>
                            <h4 class="text-danger mb-0">Rp {{ number_format($totalKredit, 0, ',', '.') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Selisih</h6>
                            <h4 class="mb-0 {{ $totalDebit == $totalKredit ? 'text-success' : 'text-warning' }}">
                                Rp {{ number_format(abs($totalDebit - $totalKredit), 0, ',', '.') }}
                                @if($totalDebit == $totalKredit)
                                    <i class="bx bx-check-circle"></i>
                                @else
                                    <i class="bx bx-error-circle"></i>
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-info-circle fs-1 text-muted"></i>
                    <p class="text-muted mt-3">Tidak ada data buku besar untuk periode dan filter yang dipilih</p>
                    <a href="{{ route('report.buku_besar') }}" class="btn btn-primary">
                        <i class="bx bx-reset me-1"></i> Reset Filter
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
<style>
    @media print {
        .btn, .card-header .btn-group, .page-title, nav, .sidebar, .accordion-button::after {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .accordion-collapse {
            display: block !important;
        }
        .accordion-button {
            background-color: #f8f9fa !important;
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

    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #212529;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }

    .accordion-item {
        border-radius: 0.5rem !important;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script>
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

    // Optional: Expand all accordions for print
    window.addEventListener('beforeprint', function() {
        document.querySelectorAll('.accordion-collapse').forEach(function(el) {
            el.classList.add('show');
        });
    });

    window.addEventListener('afterprint', function() {
        document.querySelectorAll('.accordion-collapse').forEach(function(el, index) {
            if(index > 0) {
                el.classList.remove('show');
            }
        });
    });
</script>
@endpush

@extends('layouts.app')
@section('title', 'Transaksi Keuangan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        /* Summary Cards Container */
        .summary-cards-container {
            margin-bottom: 2rem;
        }

        /* Base Card Styling */
        .summary-card {
            border: none;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: white;
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(10px);
        }

        /* Hover Effects */
        .summary-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        }

        .summary-card:hover .card-icon {
            transform: scale(1.12);
        }

        /* Card Body */
        .summary-card .card-body {
            padding: 2rem 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
        }

        /* Card Icon */
        .summary-card .card-icon {
            font-size: 2.8rem;
            margin-bottom: 1.2rem;
            opacity: 0.95;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
        }

        /* Card Label */
        .summary-card .card-label {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            opacity: 0.98;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }

        /* Card Sublabel */
        .summary-card .card-sublabel {
            font-size: 0.8rem;
            opacity: 0.85;
            margin-bottom: 1.2rem;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* Card Value */
        .summary-card .card-value {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.8px;
            word-break: break-word;
            line-height: 1.2;
        }

        /* Gradient Backgrounds - Modern Minimalist Design */
        .card-pemasukan {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            position: relative;
            overflow: hidden;
        }

        .card-pemasukan::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .card-pemasukan:hover::before {
            opacity: 0.2;
        }

        .card-pengeluaran {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        }

        .card-transaksi {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        }

        .card-harian {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        }

        .card-tunai {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .card-nontunai {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        /* Responsive Typography */
        @media (max-width: 1200px) {
            .summary-card .card-label {
                font-size: 0.95rem;
            }

            .summary-card .card-value {
                font-size: 1.7rem;
            }

            .summary-card .card-icon {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .summary-card .card-body {
                padding: 1.75rem 1.25rem;
            }

            .summary-card .card-label {
                font-size: 0.85rem;
            }

            .summary-card .card-sublabel {
                font-size: 0.75rem;
            }

            .summary-card .card-value {
                font-size: 1.5rem;
            }

            .summary-card .card-icon {
                font-size: 2.2rem;
                margin-bottom: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .summary-card .card-body {
                padding: 1.5rem 1rem;
            }

            .summary-card .card-label {
                font-size: 0.8rem;
            }

            .summary-card .card-sublabel {
                font-size: 0.7rem;
                margin-bottom: 0.8rem;
            }

            .summary-card .card-value {
                font-size: 1.3rem;
            }

            .summary-card .card-icon {
                font-size: 2rem;
                margin-bottom: 0.6rem;
            }

            .summary-card:hover {
                transform: translateY(-4px) scale(1.005);
            }
        }

        /* Mobile Portrait - Extra Small */
        @media (max-width: 360px) {
            .summary-card .card-body {
                padding: 1.25rem 0.75rem;
            }

            .summary-card .card-label {
                font-size: 0.75rem;
            }

            .summary-card .card-value {
                font-size: 1.15rem;
            }
        }
    </style>
@endpush
@section('content')
    @include('partials.page-title', [
        'title' => 'Transaksi Keuangan',
        'subTitle' => 'Kelola semua transaksi keuangan',
    ])

    {{-- Summary Cards --}}
    <div class="summary-cards-container">
        <div class="row g-3 g-lg-4">
            {{-- Total Pemasukan --}}
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="card summary-card card-pemasukan">
                    <div class="card-body">
                        <div class="card-icon">
                            <i class="fa fa-arrow-up"></i>
                        </div>
                        <div class="card-label">Pemasukan</div>
                        <div class="card-sublabel">Masuk</div>
                        <div class="card-value">
                            Rp {{ number_format($summary['total_pemasukan'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Pengeluaran --}}
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="card summary-card card-pengeluaran">
                    <div class="card-body">
                        <div class="card-icon">
                            <i class="fa fa-arrow-down"></i>
                        </div>
                        <div class="card-label">Pengeluaran</div>
                        <div class="card-sublabel">Keluar</div>
                        <div class="card-value">
                            Rp {{ number_format($summary['total_pengeluaran'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Transaksi --}}
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="card summary-card card-transaksi">
                    <div class="card-body">
                        <div class="card-icon">
                            <i class="fa fa-exchange"></i>
                        </div>
                        <div class="card-label">Transaksi</div>
                        <div class="card-sublabel">Jumlah</div>
                        <div class="card-value">
                            {{ number_format($summary['total_transaksi'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Harian --}}
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="card summary-card card-harian">
                    <div class="card-body">
                        <div class="card-icon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <div class="card-label">Hari Ini</div>
                        <div class="card-sublabel">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
                        <div class="card-value">
                            Rp {{ number_format($summary['total_harian'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Tunai --}}
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="card summary-card card-tunai">
                    <div class="card-body">
                        <div class="card-icon">
                            <i class="fa fa-money"></i>
                        </div>
                        <div class="card-label">Tunai</div>
                        <div class="card-sublabel">Kas Langsung</div>
                        <div class="card-value">
                            Rp {{ number_format($summary['total_tunai'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Non-Tunai --}}
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="card summary-card card-nontunai">
                    <div class="card-body">
                        <div class="card-icon">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="card-label">Non-Tunai</div>
                        <div class="card-sublabel">Transfer, E-Wallet</div>
                        <div class="card-value">
                            Rp {{ number_format($summary['total_non_tunai'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card rounded-3 mb-4 border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">
                <i class="bx bx-filter"></i> Filter Transaksi
            </h5>
            <form action="{{ route('keuangan_transaksi.index') }}" method="GET">
                <div class="row g-3">
                    {{-- Filter Unit --}}
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

                    {{-- Filter Jenis Transaksi --}}
                    <div class="col-md-3">
                        <label for="jenis_transaksi" class="form-label">Jenis Transaksi</label>
                        <select name="jenis_transaksi" id="jenis_transaksi" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="setoran_tabungan"
                                {{ request('jenis_transaksi') == 'setoran_tabungan' ? 'selected' : '' }}>
                                Setoran Tabungan
                            </option>
                            <option value="penarikan_tabungan"
                                {{ request('jenis_transaksi') == 'penarikan_tabungan' ? 'selected' : '' }}>
                                Penarikan Tabungan
                            </option>
                            <option value="tagihan" {{ request('jenis_transaksi') == 'tagihan' ? 'selected' : '' }}>
                                Pembayaran Tagihan
                            </option>
                        </select>
                    </div>

                    {{-- Filter Kode Pembayaran --}}
                    <div class="col-md-3">
                        <label for="kode_pembayaran" class="form-label">Kode Pembayaran</label>
                        <input type="text" name="kode_pembayaran" id="kode_pembayaran" class="form-control p-3"
                            placeholder="Cari kode pembayaran" value="{{ request('kode_pembayaran') }}">
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
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search"></i> Filter
                        </button>
                        <a href="{{ route('keuangan_transaksi.index') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Card Utama --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            {{-- Action Buttons & Pagination Control --}}
            <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <label for="per_page" class="mb-0">Tampilkan:</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;"
                        onchange="changePerPage(this.value)">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                    </select>
                    <span class="text-muted">data per halaman</span>
                </div>
                <a href="{{ route('keuangan_transaksi.print_laporan') }}" target="_blank"
                    class="btn btn-outline-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                    <i class="bx bx-printer"></i> Cetak Laporan
                </a>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table-bordered table-hover table overflow-hidden text-nowrap text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>No Transaksi</th>
                            <th>Nama & NISN</th>
                            <th>Jenis Transaksi</th>
                            <th>Tot. Transaksi</th>
                            <th>Metode</th>
                            <th>Wkt. Transaksi</th>
                            <th>Status</th>
                            <th>Petugas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksis as $transaksi)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $transaksi->code_pembayaran }}</span>
                                </td>
                                <td>
                                    @if ($transaksi->penerima)
                                        @if ($transaksi->penerima_tipe === 'App\Models\Siswa')
                                            {{ $transaksi->penerima->user->name ?? '-' }}
                                            <br>
                                            <small class="text-muted">NISN:
                                                {{ $transaksi->penerima->nisn ?? '-' }}</small>
                                        @else
                                            {{ $transaksi->penerima->name ?? '-' }}
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match ($transaksi->jenis_transaksi) {
                                            'setoran_tabungan' => 'success',
                                            'penarikan_tabungan' => 'warning',
                                            'pembayaran' => 'info',
                                            'tagihan' => 'info',
                                            default => 'secondary',
                                        };
                                        $jenisText = match ($transaksi->jenis_transaksi) {
                                            'setoran_tabungan' => 'Setoran Tabungan',
                                            'penarikan_tabungan' => 'Penarikan Tabungan',
                                            'pembayaran' => 'Pembayaran',
                                            'tagihan' => 'Pembayaran Tagihan',
                                            default => ucwords(str_replace('_', ' ', $transaksi->jenis_transaksi)),
                                        };
                                    @endphp
                                    <span class="badge rounded-pill bg-{{ $badgeColor }}">
                                        {{ $jenisText }}
                                    </span>
                                    @if (in_array($transaksi->jenis_transaksi, ['tagihan', 'pembayaran']) && $transaksi->pembayaranTagihan)
                                        <br>
                                        <small class="text-muted">
                                            @if ($transaksi->pembayaranTagihan->tagihanSiswa && $transaksi->pembayaranTagihan->tagihanSiswa->tagihan)
                                                {{ $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->nama_tagihan ?? '-' }}
                                            @endif
                                        </small>
                                        @if (
                                            $transaksi->pembayaranTagihan->tagihanSiswa &&
                                                $transaksi->pembayaranTagihan->tagihanSiswa->tagihan &&
                                                $transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items->count() > 0)
                                            <br>
                                            <small class="text-muted">
                                                @foreach ($transaksi->pembayaranTagihan->tagihanSiswa->tagihan->items as $item)
                                                    <span
                                                        class="badge badge-sm bg-light text-dark">{{ $item->kategori->nama_kategori ?? '-' }}</span>
                                                @endforeach
                                            </small>
                                        @endif
                                    @endif
                                </td>

                                <td>
                                    @if (in_array($transaksi->jenis_transaksi, ['setoran_tabungan', 'pembayaran', 'tagihan']))
                                        <span class="text-success fw-bold">+ Rp
                                            {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger fw-bold">- Rp
                                            {{ number_format($transaksi->jumlah, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $metodeBadge = match ($transaksi->metode) {
                                            'TUNAI' => 'primary',
                                            'CASH' => 'primary',
                                            'TRANSFER' => 'info',
                                            'NONTUNAI' => 'info',
                                            'SALDO_TABUNGAN' => 'warning',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $metodeBadge }}">{{ $transaksi->metode }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($transaksi->tanggal_transaksi)->format('d/m/Y') }}</td>
                                <td>
                                    @if ($transaksi->status_verifikasi == 'approved')
                                        <span class="badge bg-success rounded-pill">
                                            <i class="bx bx-check-circle me-1"></i>Approved
                                        </span>
                                    @elseif($transaksi->status_verifikasi == 'rejected')
                                        <span class="badge bg-danger rounded-pill">
                                            <i class="bx bx-x-circle me-1"></i>Rejected
                                        </span>
                                    @else
                                        <span class="badge bg-warning rounded-pill">
                                            <i class="bx bx-time-five me-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $transaksi->creator->name ?? '-' }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-success rounded-pill btn-detail-trx"
                                            data-id="{{ $transaksi->id }}" title="Lihat Detail">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning rounded-pill btn-detail-trx"
                                            data-id="{{ $transaksi->id }}" title="Cetak">
                                            <i class="bx bx-printer"></i>
                                        </button>
                                        {{--                                        @if ($transaksi->status_verifikasi == 'pending') --}}
                                        {{--                                            <button type="button" class="btn btn-sm btn-success rounded-pill btn-approve-trx" --}}
                                        {{--                                                    data-id="{{ $transaksi->id }}" --}}
                                        {{--                                                    title="Approve"> --}}
                                        {{--                                                <i class="bx bx-check"></i> --}}
                                        {{--                                            </button> --}}
                                        {{--                                            <button type="button" class="btn btn-sm btn-danger rounded-pill btn-reject-trx" --}}
                                        {{--                                                    data-id="{{ $transaksi->id }}" --}}
                                        {{--                                                    title="Reject"> --}}
                                        {{--                                                <i class="bx bx-x"></i> --}}
                                        {{--                                            </button> --}}
                                        {{--                                        @endif --}}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Belum ada data transaksi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $transaksis->firstItem() ?? 0 }} sampai {{ $transaksis->lastItem() ?? 0 }} dari
                    {{ $transaksis->total() }} data
                </div>
                <div>
                    {{ $transaksis->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .animate-btn {
            transition: all 0.3s ease-in-out;
        }

        .animate-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
        }
    </style>
@endpush

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }

        // Handle detail button click
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-detail-trx').forEach(button => {
                button.addEventListener('click', function() {
                    const transaksiId = this.dataset.id;
                    window.location.href = `{{ url('keuangan-transaksi/show') }}/${transaksiId}`;
                });
            });

            // Handle approve button click
            document.querySelectorAll('.btn-approve-trx').forEach(button => {
                button.addEventListener('click', function() {
                    const transaksiId = this.dataset.id;
                    approveTransaksi(transaksiId);
                });
            });

            // Handle reject button click
            document.querySelectorAll('.btn-reject-trx').forEach(button => {
                button.addEventListener('click', function() {
                    const transaksiId = this.dataset.id;
                    rejectTransaksi(transaksiId);
                });
            });
        });

        function approveTransaksi(transaksiId) {
            Swal.fire({
                title: 'Approve Transaksi',
                html: `
                    <div class="text-start">
                        <label for="catatan-approve" class="form-label">Catatan (Opsional)</label>
                        <textarea id="catatan-approve" class="form-control" rows="3" placeholder="Masukkan catatan verifikasi..."></textarea>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#48bb78',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bx bx-check me-1"></i> Approve',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    const catatan = document.getElementById('catatan-approve').value;
                    processApproval(transaksiId, catatan);
                }
            });
        }

        function rejectTransaksi(transaksiId) {
            Swal.fire({
                title: 'Reject Transaksi',
                html: `
                    <div class="text-start">
                        <label for="catatan-reject" class="form-label">Alasan Reject <span class="text-danger">*</span></label>
                        <textarea id="catatan-reject" class="form-control" rows="3" placeholder="Masukkan alasan reject..." required></textarea>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f56565',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bx bx-x me-1"></i> Reject',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const catatan = document.getElementById('catatan-reject').value;
                    if (!catatan) {
                        Swal.showValidationMessage('Alasan reject harus diisi');
                        return false;
                    }
                    return catatan;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    processRejection(transaksiId, result.value);
                }
            });
        }

        function processApproval(transaksiId, catatan) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`{{ url('keuangan-transaksi/approve') }}/${transaksiId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        catatan_verifikasi: catatan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#48bb78'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message,
                            confirmButtonColor: '#f56565'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat approve transaksi',
                        confirmButtonColor: '#f56565'
                    });
                });
        }

        function processRejection(transaksiId, catatan) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`{{ url('keuangan-transaksi/reject') }}/${transaksiId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        catatan_verifikasi: catatan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#48bb78'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message,
                            confirmButtonColor: '#f56565'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat reject transaksi',
                        confirmButtonColor: '#f56565'
                    });
                });
        }

        // Initialize datepicker dengan format DD/MM/YYYY
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.datepicker', {
                dateFormat: 'd/m/Y',
                allowInput: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Convert DD/MM/YYYY to YYYY-MM-DD for backend
                    if (selectedDates.length > 0) {
                        const date = selectedDates[0];
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const formattedDate = `${year}-${month}-${day}`;

                        // Set the actual input value to backend format
                        if (instance.element.id === 'dari_tanggal') {
                            instance.element.setAttribute('data-value', formattedDate);
                        } else if (instance.element.id === 'sampai_tanggal') {
                            instance.element.setAttribute('data-value', formattedDate);
                        }
                    }
                }
            });

            // Convert dates before form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                const dariTanggal = document.getElementById('dari_tanggal');
                const sampaiTanggal = document.getElementById('sampai_tanggal');

                // Convert dari_tanggal
                if (dariTanggal.value) {
                    const dataValue = dariTanggal.getAttribute('data-value');
                    if (dataValue) {
                        dariTanggal.value = dataValue;
                    } else {
                        const parts = dariTanggal.value.split('/');
                        if (parts.length === 3) {
                            dariTanggal.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        }
                    }
                }

                // Convert sampai_tanggal
                if (sampaiTanggal.value) {
                    const dataValue = sampaiTanggal.getAttribute('data-value');
                    if (dataValue) {
                        sampaiTanggal.value = dataValue;
                    } else {
                        const parts = sampaiTanggal.value.split('/');
                        if (parts.length === 3) {
                            sampaiTanggal.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        }
                    }
                }
            });
        });
    </script>
@endpush

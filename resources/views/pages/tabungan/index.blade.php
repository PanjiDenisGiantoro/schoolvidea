@extends('layouts.app')
@section('title', 'Tabungan Siswa')

@section('content')
    @include('partials.page-title', [
        'title' => 'Tabungan Siswa',
        'subTitle' => 'Kelola transaksi tabungan siswa',
    ])

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-success">
                    <h6>Total Setoran</h6>
                    <h4>Rp {{ number_format($total_setoran ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-danger">
                    <h6>Total Penarikan</h6>
                    <h4>Rp {{ number_format($total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-primary">
                    <h6>Saldo Tabungan</h6>
                    <h4>Rp {{ number_format($total_setoran - $total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-info">
                    <h6>Jumlah Transaksi</h6>
                    <h4>{{ number_format($jumlah_transaksi ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Statistics --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-warning">
                    <h6>Total Pending Penarikan</h6>
                    <h4>Rp {{ number_format($total_pending ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-success">
                    <h6>Total Approved Penarikan</h6>
                    <h4>Rp {{ number_format($total_approved ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-danger">
                    <h6>Total Reject Penarikan</h6>
                    <h4>Rp {{ number_format($total_rejected ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    @if(auth()->user()->yayasan_id && !auth()->user()->unit_id || !auth()->user()->yayasan_id && !auth()->user()->unit_id)
    <div class="card rounded-3 border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold text-primary mb-3">
                <i class="bx bx-filter"></i> Filter Tabungan
            </h5>
            <form action="{{ route('tabungan.index') }}" method="GET">
                <div class="row g-3">
                    {{-- Filter Unit --}}
                    <div class="col-md-3">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select name="unit_id" id="unit_id" class="form-select">
                            <option value="">Semua Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Search --}}
                    <div class="col-md-3">
                        <label for="search" class="form-label">Cari Siswa</label>
                        <input type="text" name="search" id="search"
                               class="form-control" placeholder="NISN, Nama, Kelas..."
                               value="{{ request('search') }}">
                    </div>

                    {{-- Filter Tanggal Dari --}}
                    <div class="col-md-2">
                        <label for="dari_tanggal" class="form-label">Dari Tanggal</label>
                        <input type="date" name="dari_tanggal" id="dari_tanggal"
                               class="form-control" value="{{ request('dari_tanggal') }}">
                    </div>

                    {{-- Filter Tanggal Sampai --}}
                    <div class="col-md-2">
                        <label for="sampai_tanggal" class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sampai_tanggal" id="sampai_tanggal"
                               class="form-control" value="{{ request('sampai_tanggal') }}">
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search"></i> Filter
                        </button>
                        <a href="{{ route('tabungan.index') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Card Utama --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            {{-- Action Buttons & Pagination Control --}}
            <div class="d-flex justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <label for="per_page" class="mb-0">Tampilkan:</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto;" onchange="changePerPage(this.value)">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                    </select>
                    <span class="text-muted">data per halaman</span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('tabungan.create') }}"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-wallet"></i> Setor
                    </a>

                    <a href="{{ url('tabungan/tarik') }}"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-money-withdraw"></i> Tarik
                    </a>

                    @if($total_pending > 0)
                    <button type="button" class="btn btn-warning rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm position-relative" onclick="showPendingTransactions()">
                        <i class="bx bx-time-five"></i> Pending
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ \App\Models\Keuangan_transaksi::where('status_verifikasi', 'pending')->whereIn('jenis_transaksi', ['setoran_tabungan', 'penarikan_tabungan'])->count() }}
                        </span>
                    </button>
                    @endif

                    <a href="{{ route('tabungan.print_laporan', request()->all()) }}" target="_blank"
                        class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                        <i class="bx bx-printer"></i> Cetak
                    </a>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Nama Unit</th>
                            <th>Nomor Induk</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas Sekarang</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th>Saldo Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksis as $siswa)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $siswa->nisn ?? '-' }}</td>
                                <td>{{ $siswa->user->name ?? '-' }}</td>
                                <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $siswa->tahun_ajaran->tahun_ajaran ?? '-' }}</td>
                                <td>
                                    @php
                                        $saldo = $siswa->user->saldo ?? null;
                                        $statusBadge = $saldo && $saldo->status == 1 ? 'primary' : 'secondary';
                                        $statusText = $saldo && $saldo->status == 1 ? 'Aktif' : 'Tidak Aktif';
                                    @endphp
                                    <span class="badge rounded-pill bg-{{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($saldo->saldo_akhir ?? 0, 0, ',', '.') }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ url('tabungan/show/'.$siswa->id) }}"
                                        class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>

                                    @if ($saldo && $saldo->status == 0)
                                        <a href="{{ route('tabungan.status', $saldo->id) }}"
                                            class="btn btn-sm btn-primary rounded-pill">Aktif</a>
                                    @elseif($saldo)
                                        <a href="{{ route('tabungan.status', $saldo->id) }}"
                                            class="btn btn-sm btn-danger rounded-pill">Non Aktif</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data siswa</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $transaksis->firstItem() ?? 0 }} sampai {{ $transaksis->lastItem() ?? 0 }} dari {{ $transaksis->total() }} data
                </div>
                <div>
                    {{ $transaksis->appends(request()->query())->links() }}
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

        /* Pending Modal Styles */
        #pendingModal .modal-header {
            border-bottom: 3px solid #ffc107;
        }

        #pendingModal .table thead th {
            position: sticky;
            top: 0;
            background-color: #fff3cd;
            z-index: 10;
        }

        #pendingModal .table tbody tr {
            transition: all 0.2s ease;
        }

        #pendingModal .table tbody tr:hover {
            background-color: #fff9e6;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        #pendingModal .badge {
            font-size: 0.85rem;
            font-weight: 600;
        }

        #pendingModal .btn-primary {
            transition: all 0.2s ease;
        }

        #pendingModal .btn-primary:hover {
            transform: scale(1.05);
        }
    </style>
@endpush

@push('scripts')
    <script>
        function changePerPage(perPage) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.delete('page'); // Reset to page 1
            window.location.href = url.toString();
        }

        function showPendingTransactions() {
            // Show Bootstrap Modal instead of SweetAlert
            const modalHtml = `
                <div class="modal fade" id="pendingModal" tabindex="-1" aria-labelledby="pendingModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title fw-bold" id="pendingModalLabel">
                                    <i class="bx bx-time-five me-2"></i>Transaksi Pending Approval
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                                    <div class="spinner-border text-warning" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-3">Memuat data transaksi pending...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            const existingModal = document.getElementById('pendingModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Append modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('pendingModal'));
            modal.show();

            // Fetch pending transactions
            fetch('/api/v1/tabungan/transaksi?status=pending', {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer {{ auth()->user()->api_token ?? "" }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    let tableRows = '';
                    data.data.forEach((trx, index) => {
                        const jenisClass = trx.jenis_transaksi === 'setoran_tabungan' ? 'success' : 'danger';
                        const jenisIcon = trx.jenis_transaksi === 'setoran_tabungan' ? 'plus-circle' : 'minus-circle';
                        const jenisText = trx.jenis_transaksi === 'setoran_tabungan' ? 'Setoran' : 'Penarikan';

                        // Format tanggal
                        const tanggal = new Date(trx.tanggal_transaksi).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        // Status approval untuk penarikan
                        let statusBadge = '';
                        if (trx.jenis_transaksi === 'penarikan_tabungan') {
                            if (trx.status_approval === 'pending') {
                                statusBadge = '<span class="badge bg-warning text-dark"><i class="bx bx-time-five me-1"></i>Belum Verify Token</span>';
                            }
                        } else {
                            statusBadge = '<span class="badge bg-info"><i class="bx bx-info-circle me-1"></i>Menunggu Verifikasi</span>';
                        }

                        tableRows += `
                            <tr class="align-middle">
                                <td class="text-center">${index + 1}</td>
                                <td class="text-center">
                                    <span class="badge bg-${jenisClass} px-3 py-2">
                                        <i class="bx bx-${jenisIcon} me-1"></i>${jenisText}
                                    </span>
                                </td>
                                <td><strong>${trx.nomor_transaksi || trx.code_pembayaran}</strong></td>
                                <td>${trx.siswa_nama || '-'}</td>
                                <td class="text-end"><strong>Rp ${new Intl.NumberFormat('id-ID').format(trx.jumlah)}</strong></td>
                                <td class="text-center"><small>${tanggal}</small></td>
                                <td class="text-center">${statusBadge}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary rounded-pill" onclick="viewDetailPending(${trx.transaksi_id})">
                                        <i class="bx bx-show me-1"></i>Detail
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    const modalBody = document.querySelector('#pendingModal .modal-body');
                    modalBody.innerHTML = `
                        <div class="alert alert-warning border-0 shadow-sm mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-info-circle fs-4 me-3"></i>
                                <div>
                                    <strong>Perhatian:</strong> Terdapat <strong>${data.data.length}</strong> transaksi yang menunggu approval.
                                    <br><small>Untuk penarikan tabungan, verifikasi token terlebih dahulu sebelum approve.</small>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-warning">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th class="text-center" style="width: 120px;">Jenis</th>
                                        <th style="width: 150px;">Nomor Transaksi</th>
                                        <th style="width: 200px;">Nama Siswa</th>
                                        <th class="text-end" style="width: 130px;">Jumlah</th>
                                        <th class="text-center" style="width: 150px;">Tanggal</th>
                                        <th class="text-center" style="width: 150px;">Status</th>
                                        <th class="text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tableRows}
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    const modalBody = document.querySelector('#pendingModal .modal-body');
                    modalBody.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle text-success" style="font-size: 80px;"></i>
                            <h4 class="mt-3">Tidak Ada Transaksi Pending</h4>
                            <p class="text-muted">Semua transaksi sudah diproses</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const modalBody = document.querySelector('#pendingModal .modal-body');
                modalBody.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bx bx-error-circle text-danger" style="font-size: 80px;"></i>
                        <h4 class="mt-3">Gagal Memuat Data</h4>
                        <p class="text-muted">Terjadi kesalahan saat memuat transaksi pending</p>
                        <button class="btn btn-primary" onclick="showPendingTransactions()">
                            <i class="bx bx-refresh me-1"></i>Coba Lagi
                        </button>
                    </div>
                `;
            });
        }

        function viewDetailPending(transaksiId) {
            // Close the pending modal first
            const pendingModal = bootstrap.Modal.getInstance(document.getElementById('pendingModal'));
            if (pendingModal) {
                pendingModal.hide();
            }

            // Redirect to detail page or show detail in new modal
            // You can customize this based on your needs
            window.location.href = `/keuangan-transaksi/show/${transaksiId}`;
        }

        function showDetailTransaksi(transaksiId) {
            Swal.fire({
                title: 'Memuat...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/api/v1/tabungan/${transaksiId}/detail`, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer {{ auth()->user()->api_token ?? "" }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const trx = data.data;
                    const statusBadge = getStatusBadge(trx.status_pembayaran);
                    const buktiHtml = trx.bukti_transfer
                        ? `<div class="mb-3">
                            <strong>Bukti Transfer:</strong><br>
                            <a href="${trx.bukti_transfer}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bx bx-image me-1"></i>Lihat Bukti Transfer
                            </a>
                        </div>`
                        : '<div class="alert alert-warning"><i class="bx bx-info-circle me-1"></i>Bukti transfer belum diupload</div>';

                    const catatanHtml = trx.catatan_verifikasi
                        ? `<div class="alert alert-info">
                            <strong>Catatan Verifikasi:</strong><br>
                            ${trx.catatan_verifikasi}<br>
                            <small class="text-muted">Oleh: ${trx.verified_by || '-'} pada ${trx.verified_at || '-'}</small>
                        </div>`
                        : '';

                    const actionButtons = trx.status_pembayaran === 'pending'
                        ? `<div class="mt-4 d-flex gap-2 justify-content-center">
                            <button class="btn btn-success" onclick="approveTransaksi(${transaksiId})">
                                <i class="bx bx-check-circle me-1"></i>Approve
                            </button>
                            <button class="btn btn-danger" onclick="rejectTransaksi(${transaksiId})">
                                <i class="bx bx-x-circle me-1"></i>Reject
                            </button>
                        </div>`
                        : '';

                    Swal.fire({
                        title: 'Detail Transaksi',
                        html: `
                            <div class="text-start">
                                <div class="mb-3">
                                    <strong>Nomor Transaksi:</strong><br>
                                    <span class="badge bg-secondary">${trx.nomor_transaksi}</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Jenis Transaksi:</strong><br>
                                    ${trx.jenis_transaksi}
                                </div>
                                <div class="mb-3">
                                    <strong>Jumlah:</strong><br>
                                    <h4 class="text-primary">Rp ${new Intl.NumberFormat('id-ID').format(trx.jumlah)}</h4>
                                </div>
                                <div class="mb-3">
                                    <strong>Tanggal Transaksi:</strong><br>
                                    ${trx.tanggal_transaksi}
                                </div>
                                <div class="mb-3">
                                    <strong>Deskripsi:</strong><br>
                                    ${trx.deskripsi || '-'}
                                </div>
                                <div class="mb-3">
                                    <strong>Status:</strong><br>
                                    ${statusBadge}
                                </div>
                                ${buktiHtml}
                                ${catatanHtml}
                                ${actionButtons}
                            </div>
                        `,
                        width: '600px',
                        showCloseButton: true,
                        showConfirmButton: false
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal memuat detail transaksi',
                    confirmButtonColor: '#f56565'
                });
            });
        }

        function getStatusBadge(status) {
            if (status === 'approved') {
                return '<span class="badge bg-success rounded-pill px-3 py-2"><i class="bx bx-check-circle me-1"></i>Approved</span>';
            } else if (status === 'rejected') {
                return '<span class="badge bg-danger rounded-pill px-3 py-2"><i class="bx bx-x-circle me-1"></i>Rejected</span>';
            } else {
                return '<span class="badge bg-warning rounded-pill px-3 py-2"><i class="bx bx-time-five me-1"></i>Pending</span>';
            }
        }

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

            fetch(`/api/v1/tabungan/${transaksiId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer {{ auth()->user()->api_token ?? "" }}',
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

            fetch(`/api/v1/tabungan/${transaksiId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer {{ auth()->user()->api_token ?? "" }}',
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
    </script>
@endpush

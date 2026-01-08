@extends('layouts.app')
@section('title', 'Detail Tabungan')

@section('content')
    @include('partials.page-title', [
        'title' => 'Detail Tabungan',
        'subTitle' => 'Tabungan / Keuangan'
    ])

    @if(session('success') && session('token'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="bx bx-check-circle fs-3 me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-2">{{ session('success') }}</h5>
                    @if(session('info'))
                        <div class="alert alert-warning mb-3">
                            <i class="bx bx-info-circle me-1"></i>
                            <small>{{ session('info') }}</small>
                        </div>
                    @endif
                    <div class="mb-3">
                        <strong>Token Verifikasi Anda:</strong>
                        <div class="bg-white p-3 rounded mt-2 border border-success">
                            <h2 class="text-success mb-0 text-center" style="letter-spacing: 8px; font-family: monospace;">
                                {{ session('token') }}
                            </h2>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="bx bx-time-five me-1"></i>
                        Token berlaku selama 24 jam. Simpan token ini untuk verifikasi transaksi.
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Detail Siswa --}}
        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                <div class="card-header bg-gradient-info text-white text-center py-3">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-user-circle me-1 text-white"></i>Informasi Siswa
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $siswa->image ? asset($siswa->image) : asset('assets/images/videa.png') }}"
                                 alt="Foto Siswa"
                                 class="img-fluid rounded-circle shadow border border-3 border-info"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 bg-info rounded-circle p-2 border border-2 border-white">
                                <i class="bx bx-wallet text-white"></i>
                            </span>
                        </div>
                    </div>

                    <div class="student-info">
                        <div class="info-item mb-2 p-2 bg-light rounded">
                            <small class="text-muted d-block mb-1">Nama Lengkap</small>
                            <strong class="text-dark">{{ $siswa->user->name }}</strong>
                        </div>
                        <div class="info-item mb-2 p-2">
                            <small class="text-muted d-block mb-1">Nomor Induk (NISN)</small>
                            <strong class="text-dark">{{ $siswa->nisn }}</strong>
                        </div>
                        <div class="info-item mb-3 p-2 bg-light rounded">
                            <small class="text-muted d-block mb-1">Kelas</small>
                            <strong class="text-dark">{{ $siswa->kelas->nama_kelas }}</strong>
                        </div>
                    </div>

                    <div class="saldo-info">

                        <div class="alert alert-success border-0 shadow-sm mb-0">
                            <small class="text-muted d-block mb-1">Saldo Saat Ini</small>
                            <div class="fs-5 fw-bold">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ url('tabungan/') }}" class="btn btn-secondary"><i class='bx  bx-chevron-left'></i> </a>
                        <a href="{{ route('tabungan.create', ['siswa_id' => $siswa->id, 'unit_id' => $siswa->unit_id, 'kelas_id' => $siswa->kelas_id]) }}" class="btn btn-success"><i class="bx bx-plus-circle me-1"></i> Setor</a>
                        <a href="{{ route('tabungan.tarik', ['siswa_id' => $siswa->id, 'unit_id' => $siswa->unit_id, 'kelas_id' => $siswa->kelas_id]) }}" class="btn btn-danger"><i class="bx bx-minus-circle me-1"></i>Tarik</a>
                        <button
                        type="button"
                        class="btn btn-info"
                        data-bs-toggle="modal"
                        data-bs-target="#qrModal"
                    >
                        <i class="bx bx-qr"></i>
                        QR
                    </button>
                        <a href="{{ route('tabungan.print_mutasi', $siswa->id) }}" class="btn btn-warning" target="_blank"><i class="bx bx-printer" style="font-size: 20px"></i></a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Log Transaksi --}}
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-list-ul me-2 text-white"></i>Riwayat Transaksi Tabungan
                    </h5>
                </div>
                <div class="card-body p-4">
                    {{-- Search & Filter Form --}}
                    <form method="GET" action="{{ route('tabungan.show', $siswa->id) }}" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select name="jenis_transaksi" class="form-select form-select-sm">
                                    <option value="">Semua Jenis</option>
                                    <option value="setoran_tabungan" {{ request('jenis_transaksi') == 'setoran_tabungan' ? 'selected' : '' }}>
                                        <i class="bx bx-plus-circle"></i>Setoran
                                    </option>
                                    <option value="penarikan_tabungan" {{ request('jenis_transaksi') == 'penarikan_tabungan' ? 'selected' : '' }}>
                                        <i class="bx bx-minus-circle"></i>Penarikan
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="dari_tanggal" class="form-control "
                                       placeholder="Dari Tanggal" value="{{ request('dari_tanggal') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="sampai_tanggal" class="form-control"
                                       placeholder="Sampai Tanggal" value="{{ request('sampai_tanggal') }}">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    <i class="bx bx-search me-1"></i>Cari
                                </button>
                                <a href="{{ route('tabungan.show', $siswa->id) }}" class="btn btn-sm btn-secondary w-100 mt-1">
                                    <i class="bx bx-x me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                            <tr class="border-bottom border-2 justify-middle text-center">
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-calendar me-1"></i>Waktu
                                </th>
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-transfer me-1"></i>Jenis
                                </th>
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-money me-1"></i>Jumlah
                                </th>
{{--                                <th class="text-muted fw-semibold text-nowrap">--}}
{{--                                    <i class="bx bx-transfer me-1"></i>Debit/Kredit--}}
{{--                                </th>--}}
                                <th class="text-muted fw-semibold text-nowrap">
                                    <i class="bx bx-note me-1"></i>Keterangan
                                </th>
                                <th class="text-muted fw-semibold text-center text-nowrap">
                                    <i class="bx bx-check-shield me-1"></i>Saldo Awal
                                </th>
                                <th class="text-muted fw-semibold text-center text-nowrap">
                                    <i class="bx bx-check-shield me-1"></i>Status
                                </th>
                                <th class="text-muted fw-semibold text-center text-nowrap">
                                    <i class="bx bx-check-shield me-1"></i>Saldo Akhir
                                </th>
                                <th class="text-muted fw-semibold text-center">
                                    <i class="bx bx-cog me-1"></i>Aksi
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr class="transaction-row text-center">
                                    <td>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}</small>
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($log->jenis_transaksi == 'setoran_tabungan')
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                <i class="bx bx-plus-circle me-1"></i>Setoran
                                            </span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                                <i class="bx bx-minus-circle me-1"></i>Penarikan
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->jenis_transaksi == 'setoran_tabungan')
                                            <span class="fw-bold text-success">+ Rp {{ number_format($log->jumlah, 0, ',', '.') }}</span>
                                        @else
                                            <span class="fw-bold text-danger">- Rp {{ number_format($log->jumlah, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
{{--                                    <td>--}}
{{--                                        <span class="text-muted">-</span>--}}
{{--                                    </td>--}}
                                    <td>
                                        <span class="text-muted">{{ $log->keterangan ?: '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-info">Rp {{ number_format($log->saldo_sebelum, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            // Untuk penarikan tabungan, cek status_approval (dengan token)
                                            // Untuk setoran, langsung approved
                                            $statusApproval = $log->jenis_transaksi == 'penarikan_tabungan'
                                                ? $log->status_approval
                                                : 'approved';
                                        @endphp
{{--                                        {{ $log->status_approval }}--}}

                                        @if($log->status_approval == 'approved')
                                            <span class="badge bg-success rounded-pill px-3 py-2">
                                                <i class="bx bx-check-circle me-1"></i>Approved
                                            </span>
                                        @elseif($log->status_approval == 'rejected')
                                            <span class="badge bg-danger rounded-pill px-3 py-2">
                                                <i class="bx bx-x-circle me-1"></i>Rejected
                                            </span>
                                        @elseif($log->status_approval == 'pending' || $log->status_approval =='verified')
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <span class="badge bg-warning rounded-pill px-3 py-2">
                                                    <i class="bx bx-time-five me-1"></i>Proses Verify
                                                </span>
                                                @if($log->token)
                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                        Token: <span class="badge bg-secondary">{{ $log->token }}</span>
                                                    </small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                                <i class="bx bx-info-circle me-1"></i>N/A
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">Rp {{ number_format($log->saldo_sesudah, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($log->status_approval != 'pending')
                                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                                            <a href="{{ route('tabungan.print_struk', $log->id) }}"
                                               class="btn btn-sm btn-info rounded-pill text-nowrap"
                                               target="_blank"
                                               title="Cetak Struk (Thermal Printer)">
                                                <i class="bx bx-printer"></i>
                                            </a>
                                            @endif
                                            @if($log->jenis_transaksi == 'penarikan_tabungan' && $log->status_approval == 'pending' || $log->status_approval == 'verified')
                                                <button type="button" class="btn btn-sm btn-success rounded-pill text-nowrap btn-verify"
                                                        data-id="{{ $log->id }}"
                                                        data-token="{{ $log->token }}"
                                                        data-jumlah="{{ $log->jumlah }}"
                                                        title="Verifikasi dengan Token">
                                                    <i class="bx bx-key"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bx bx-receipt fs-1 text-muted d-block mb-2"></i>
                                        <span class="text-muted">Belum ada transaksi</span>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($logs->total() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Menampilkan {{ $logs->count() }} dari {{ $logs->total() }} transaksi
                            (Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }})
                        </div>
                        <div>
                            {{ $logs->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


        {{-- Modal --}}
    <div
        class="modal fade"
        id="qrModal"
        tabindex="-1"
        aria-labelledby="qrModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">
                        <i class="bx bx-qr"></i>
                        QR Code Merchant
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body text-center">
                    <!-- QR Code -->
                    <div
                        id="qrcode"
                        class="mb-3 p-3 d-flex justify-content-center"
                        style="border: 1px solid #000"
                    ></div>

                    <!-- hidden input untuk simpan data QR -->
                    <input type="hidden" id="qrcode-text" name="qrcode_text" />

                    <small class="text-muted">
                        QR siswa
                    </small>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Tutup
                    </button>
                    <button
                        type="button"
                        id="downloadQrBtn"
                        class="btn btn-primary"
                        style="display: none"
                    >
                        <i class="bx bx-download"></i>
                        Download QR
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .bg-gradient-info {
            background: linear-gradient(135deg, #1bb394  0%, #1bb394 100%);
        }
        .student-info .info-item {
            transition: all 0.2s ease;
        }
        .student-info .info-item:hover {
            background-color: #e6f7ff !important;
            transform: translateX(5px);
        }
        .transaction-row {
            transition: all 0.2s ease;
        }
        .transaction-row:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
        }
        .saldo-info .alert {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .table thead th {
            border-bottom: 2px solid #1bb394  !important;
            padding: 1rem;
            background-color: #f8fafc;
        }
        .badge {
            font-weight: 600;
            font-size: 0.85rem;
        }
        .btn-verify {
            transition: all 0.2s ease;
        }
        .btn-verify:hover {
            transform: scale(1.05);
        }
    </style>
@endpush

@push('scripts')

    <script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.6.0/lib/qr-code-styling.js"></script>
    {{-- QR --}}
    <script>
        console.log('QR SCRIPT LOADED');

        document.addEventListener('DOMContentLoaded', function () {
            const vaInput = @json($siswa->va_siswa);
            const qrContainer = document.getElementById('qrcode');
            const downloadBtn = document.getElementById('downloadQrBtn');
            const qrModal = document.getElementById('qrModal');

            let qrCode = null;

            // LOGO UNIT dari database merchant (AMAN)
            const logo = @json(
                $siswa->unit && $siswa->unit->image
                    ? asset("" . $siswa->unit->image)
                    : asset("images/default-logo.png")
            );

            console.log('QR LOGO:', logo);

            qrModal.addEventListener('shown.bs.modal', function () {
                qrContainer.innerHTML = '';

                qrCode = new QRCodeStyling({
                    width: 200,
                    height: 200,
                    data: vaInput || '-',
                    image: logo,
                    dotsOptions: {
                        color: '#000',
                        type: 'rounded',
                    },
                    backgroundOptions: {
                        color: '#fff',
                    },
                    imageOptions: {
                        crossOrigin: 'anonymous',
                        margin: 4,
                        imageSize: 0.4,
                        hideBackgroundDots: true,
                        imageCornerRadius: 100,
                    },
                });

                qrCode.append(qrContainer);
                downloadBtn.style.display = 'inline-block';
            });

            // Download QR
            downloadBtn.addEventListener('click', function () {
                if (!qrCode) return;

                qrCode.download({
                    name: 'qr-' + (vaInput || '-'),
                    extension: 'png',
                });
            });
        });
    </script>


    <script>
        // Handle detail button click
        document.querySelectorAll('.btn-detail').forEach(button => {
            button.addEventListener('click', function() {
                const transaksiId = this.dataset.id;
                showDetailTransaksi(transaksiId);
            });
        });

        // Handle verify button click
        document.querySelectorAll('.btn-verify').forEach(button => {
            button.addEventListener('click', function() {
                const transaksiId = this.dataset.id;
                const expectedToken = this.dataset.token;
                const jumlah = this.dataset.jumlah;
                showVerifyModal(transaksiId, expectedToken, jumlah);
            });
        });

        function showDetailTransaksi(transaksiId) {
            // Show loading
            Swal.fire({
                title: 'Memuat...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch transaction detail
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
                        showConfirmButton: false,
                        customClass: {
                            popup: 'swal-detail-popup'
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal memuat detail transaksi',
                        confirmButtonColor: '#f56565'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat memuat detail',
                    confirmButtonColor: '#f56565'
                });
                console.error('Error:', error);
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
                html: 'Mohon tunggu sebentar',
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat approve transaksi',
                    confirmButtonColor: '#f56565'
                });
                console.error('Error:', error);
            });
        }

        function processRejection(transaksiId, catatan) {
            Swal.fire({
                title: 'Memproses...',
                html: 'Mohon tunggu sebentar',
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat reject transaksi',
                    confirmButtonColor: '#f56565'
                });
                console.error('Error:', error);
            });
        }

        // Show Verify Modal untuk input token dan approve/reject
        function showVerifyModal(transaksiId, expectedToken, jumlah) {
            Swal.fire({
                title: 'Verifikasi Token Penarikan',
                html: `
                    <div class="text-start">
                        <div class="alert alert-info mb-3">
                            <strong><i class="bx bx-info-circle me-1"></i>Informasi:</strong><br>
                            <small class="d-block">Masukkan 6 digit token untuk memverifikasi penarikan sebesar : </small>
                            <small class="d-block"><strong>Rp ${new Intl.NumberFormat('id-ID').format(jumlah)}</strong></small>
                        </div>
                        <label for="token-input" class="form-label">Token (6 Digit) <span class="text-danger">*</span></label>
                        <input type="text" id="token-input" class="form-control form-control-lg text-center"
                               placeholder="000000" maxlength="6"
                               style="letter-spacing: 8px; font-family: monospace; font-size: 1.5rem;">
                        <small class="text-muted mt-2 d-block">Token diberikan saat transaksi penarikan dibuat</small>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonColor: '#48bb78',
                denyButtonColor: '#f56565',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bx bx-check-circle me-1"></i> Approve',
                denyButtonText: '<i class="bx bx-x-circle me-1"></i> Reject',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                preConfirm: () => {
                    const token = document.getElementById('token-input').value;
                    if (!token || token.length !== 6) {
                        Swal.showValidationMessage('Token harus 6 digit');
                        return false;
                    }
                    return { token: token, action: 'approve' };
                },
                preDeny: () => {
                    const token = document.getElementById('token-input').value;
                    if (!token || token.length !== 6) {
                        Swal.showValidationMessage('Token harus 6 digit');
                        return false;
                    }
                    return { token: token, action: 'reject' };
                }
            }).then((result) => {
                if (result.isConfirmed || result.isDenied) {
                    processTokenVerification(transaksiId, result.value.token, result.value.action);
                }
            });

            // Auto focus on token input
            setTimeout(() => {
                document.getElementById('token-input').focus();
            }, 500);
        }

        // Process Token Verification
        function processTokenVerification(transaksiId, token, action) {
            Swal.fire({
                title: 'Memverifikasi...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('/tabungan/verify-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    transaksi_id: transaksiId,
                    token: token,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        html: `
                            <div class="text-start">
                                <p>${data.message}</p>
                                <div class="alert alert-info mt-3">
                                    <strong>Detail:</strong><br>
                                    <small>Transaksi ID: ${data.data.transaksi_id}</small><br>
                                    <small>Code: ${data.data.code_pembayaran}</small><br>
                                    <small>Status: <span class="badge bg-${action === 'approve' ? 'success' : 'danger'}">${data.data.status_approval}</span></small><br>
                                    <small>Saldo Akhir: Rp ${new Intl.NumberFormat('id-ID').format(data.data.saldo_akhir)}</small>
                                </div>
                            </div>
                        `,
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
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat verifikasi token',
                    confirmButtonColor: '#f56565'
                });
                console.error('Error:', error);
            });
        }

        // Auto show SweetAlert for new token
        @if(session('success') && session('token'))
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Transaksi Berhasil!',
                    html: `
                        <div class="mb-3">
                            <p>{{ session('success') }}</p>
                            <div class="alert alert-warning">
                                <strong>Token Verifikasi:</strong>
                                <h2 class="text-primary my-2" style="letter-spacing: 8px; font-family: monospace;">
                                    {{ session('token') }}
                                </h2>
                                <small class="text-muted">
                                    <i class="bx bx-time-five me-1"></i>
                                    Berlaku selama 24 jam
                                </small>
                            </div>
                        </div>
                    `,
                    confirmButtonColor: '#48bb78',
                    confirmButtonText: 'OK, Mengerti'
                });
            }, 500);
        });
        @endif
    </script>
@endpush

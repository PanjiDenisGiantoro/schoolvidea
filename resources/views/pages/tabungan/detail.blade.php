@extends('layouts.app')
@section('title', 'Detail Tabungan Siswa')

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
                    <h6 class="mb-0 fw-bold">
                        <i class="bx bx-user-circle me-1"></i>Informasi Siswa
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $siswa->image ? asset($siswa->image) : asset('images/default-user.png') }}"
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
                        <div class="alert alert-warning border-0 shadow-sm mb-2">
                            <small class="text-muted d-block mb-1">Saldo Sebelumnya</small>
                            <div class="fs-6 fw-bold">Rp {{ number_format($saldo_awal, 0, ',', '.') }}</div>
                        </div>
                        <div class="alert alert-success border-0 shadow-sm mb-0">
                            <small class="text-muted d-block mb-1">Saldo Saat Ini</small>
                            <div class="fs-5 fw-bold">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Log Transaksi --}}
        <div class="col-md-8">
            <div class="card shadow-sm rounded-4 border-0 overflow-hidden">
                <div class="card-header bg-gradient-info text-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-list-ul me-2"></i>Riwayat Transaksi Tabungan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                            <tr class="border-bottom border-2">
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-calendar me-1"></i>Tanggal
                                </th>
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-transfer me-1"></i>Jenis
                                </th>
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-money me-1"></i>Jumlah
                                </th>
                                <th class="text-muted fw-semibold">
                                    <i class="bx bx-note me-1"></i>Keterangan
                                </th>
                                <th class="text-muted fw-semibold text-center">
                                    <i class="bx bx-check-shield me-1"></i>Status
                                </th>
                                <th class="text-muted fw-semibold text-center">
                                    <i class="bx bx-cog me-1"></i>Aksi
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($logs as $log)
                                <tr class="transaction-row">
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
                                    <td>
                                        <span class="text-muted">{{ $log->keterangan ?: '-' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($log->jenis_transaksi == 'setoran_tabungan')
                                        @if($log->status_approval == 'approved')
                                            <span class="badge bg-success rounded-pill">
                                                <i class="bx bx-check-circle me-1"></i>Approved
                                            </span>
                                        @elseif($log->status_approval == 'rejected')
                                            <span class="badge bg-danger rounded-pill">
                                                <i class="bx bx-x-circle me-1"></i>Rejected
                                            </span>
                                        @else
                                            <span class="badge bg-warning rounded-pill">
                                                <i class="bx bx-time-five me-1"></i>Pending
                                            </span>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($log->jenis_transaksi == 'setoran_tabungan')
                                        @if($log->status_approval == 'pending' && $log->token_expired_at && now()->lessThan($log->token_expired_at))
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill btn-verify"
                                                    data-id="{{ $log->id }}"
                                                    data-token="{{ $log->token }}"
                                                    data-jenis="{{ $log->jenis_transaksi }}"
                                                    data-jumlah="{{ number_format($log->jumlah, 0, ',', '.') }}">
                                                <i class="bx bx-key me-1"></i>Verify
                                            </button>
                                        @elseif($log->status_approval == 'pending')
                                            <small class="text-danger">Token Expired</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                        @endif
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-gradient-info {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
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
            border-bottom: 2px solid #4299e1 !important;
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
    <script>
        // Handle verify button click
        document.querySelectorAll('.btn-verify').forEach(button => {
            button.addEventListener('click', function() {
                const transaksiId = this.dataset.id;
                const token = this.dataset.token;
                const jenis = this.dataset.jenis;
                const jumlah = this.dataset.jumlah;
                const jenisText = jenis === 'setoran_tabungan' ? 'Setoran' : 'Penarikan';

                Swal.fire({
                    title: 'Verifikasi Transaksi',
                    html: `
                        <div class="text-start mb-3">
                            <div class="alert alert-info">
                                <strong>Detail Transaksi:</strong><br>
                                <small>Jenis: ${jenisText}</small><br>
                                <small>Jumlah: Rp ${jumlah}</small>
                            </div>
                            <div class="alert alert-warning">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Token untuk transaksi ini: ${token}</strong>
                            </div>
                        </div>
                        <label for="token-input" class="form-label fw-semibold">Masukkan Token (5 Digit)</label>
                        <input type="text" id="token-input" class="form-control form-control-lg text-center mb-3"
                               placeholder="00000" maxlength="5" pattern="[0-9]{5}"
                               style="letter-spacing: 8px; font-size: 1.5rem; font-weight: bold;">
                        <small class="text-muted">Masukkan 6 digit token untuk memverifikasi transaksi</small>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonColor: '#48bb78',
                    denyButtonColor: '#f56565',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bx bx-check me-1"></i> Approve',
                    denyButtonText: '<i class="bx bx-x me-1"></i> Reject',
                    cancelButtonText: 'Batal',
                    focusConfirm: false,
                    didOpen: () => {
                        const input = document.getElementById('token-input');
                        input.focus();

                        // Only allow numbers
                        input.addEventListener('input', function(e) {
                            this.value = this.value.replace(/[^0-9]/g, '');
                        });
                    },
                    preConfirm: () => {
                        const inputToken = document.getElementById('token-input').value;
                        if (!inputToken || inputToken.length !== 6) {
                            Swal.showValidationMessage('Token harus 6 digit angka');
                            return false;
                        }
                        return inputToken;
                    },
                    preDeny: () => {
                        const inputToken = document.getElementById('token-input').value;
                        if (!inputToken || inputToken.length !== 6) {
                            Swal.showValidationMessage('Token harus 6 digit angka');
                            return false;
                        }
                        return inputToken;
                    }
                }).then((result) => {
                    if (result.isConfirmed || result.isDenied) {
                        const action = result.isConfirmed ? 'approve' : 'reject';
                        const inputToken = result.value;

                        verifyTransaction(transaksiId, inputToken, action);
                    }
                });
            });
        });

        function verifyTransaction(transaksiId, token, action) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send AJAX request
            fetch('{{ route("tabungan.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
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
                        text: data.message,
                        confirmButtonColor: '#48bb78'
                    }).then(() => {
                        // Reload page to show updated status
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
                    text: 'Terjadi kesalahan saat memproses verifikasi',
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

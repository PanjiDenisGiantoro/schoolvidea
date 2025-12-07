@extends('layouts.app')

@section('title', 'Backup Database')

@section('content')
    @include('partials.page-title', [
        'title' => 'Backup Database',
        'subTitle' => 'Manajemen Backup & Restore'
    ])

    {{-- Manual Backup Section --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="avatar-lg rounded-circle bg-primary-subtle mx-auto mb-3">
                        <i class="bx bx-data fs-1 text-primary"></i>
                    </div>
                    <h5 class="mb-2">Backup Manual</h5>
                    <p class="text-muted small mb-3">Buat backup database secara manual sekarang</p>
                    <button type="button" class="btn btn-primary" onclick="createManualBackup('full')">
                        <i class="bx bx-download me-1"></i> Backup Sekarang
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="avatar-lg rounded-circle bg-success-subtle mx-auto mb-3">
                        <i class="bx bx-calendar-week fs-1 text-success"></i>
                    </div>
                    <h5 class="mb-2">Backup Mingguan</h5>
                    <p class="text-muted small mb-3">Buat backup untuk periode minggu ini</p>
                    <button type="button" class="btn btn-success" onclick="createManualBackup('weekly')">
                        <i class="bx bx-download me-1"></i> Backup Mingguan
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="avatar-lg rounded-circle bg-warning-subtle mx-auto mb-3">
                        <i class="bx bx-calendar fs-1 text-warning"></i>
                    </div>
                    <h5 class="mb-2">Backup Bulanan</h5>
                    <p class="text-muted small mb-3">Buat backup untuk periode bulan ini</p>
                    <button type="button" class="btn btn-warning" onclick="createManualBackup('monthly')">
                        <i class="bx bx-download me-1"></i> Backup Bulanan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Configuration --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bx bx-time-five me-2"></i>Konfigurasi Backup Otomatis
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('backup.schedule.update') }}" method="POST" id="scheduleForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Aktifkan Backup Otomatis</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_backup" id="autoBackup" value="1" {{ $schedule && $schedule->auto_backup ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoBackup">
                                Ya, aktifkan backup otomatis
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Frekuensi Backup</label>
                        <select name="backup_frequency" class="form-select" required>
                            <option value="daily" {{ $schedule && $schedule->backup_frequency == 'daily' ? 'selected' : '' }}>Setiap Hari</option>
                            <option value="weekly" {{ $schedule && $schedule->backup_frequency == 'weekly' ? 'selected' : '' }}>Setiap Minggu</option>
                            <option value="monthly" {{ $schedule && $schedule->backup_frequency == 'monthly' ? 'selected' : '' }}>Setiap Bulan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Waktu Backup</label>
                        <input type="time" name="backup_time" class="form-control" value="{{ $schedule->backup_time ?? '02:00' }}" required>
                        <small class="text-muted">Backup akan dijalankan pada waktu ini setiap hari</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Simpan Backup Selama (Hari)</label>
                        <input type="number" name="keep_backups" class="form-control" value="{{ $schedule->keep_backups ?? 30 }}" min="1" max="365" required>
                        <small class="text-muted">Backup yang lebih lama dari ini akan dihapus otomatis</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Notifikasi Email</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="email_notification" id="emailNotif" value="1" {{ $schedule && $schedule->email_notification ? 'checked' : '' }}>
                            <label class="form-check-label" for="emailNotif">
                                Kirim email setelah backup selesai
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email Penerima</label>
                        <input type="email" name="notification_email" class="form-control" value="{{ $schedule->notification_email ?? '' }}" placeholder="email@example.com">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Backup List --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bx bx-list-ul me-2"></i>Daftar Backup
                </h5>
                <button type="button" class="btn btn-sm btn-danger" onclick="cleanOldBackups()">
                    <i class="bx bx-trash me-1"></i> Bersihkan Backup Lama
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="backupTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Filename</th>
                            <th>Tipe</th>
                            <th>Ukuran</th>
                            <th>Tanggal</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $index => $backup)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <i class="bx bx-file-blank me-1"></i>
                                    {{ $backup['filename'] }}
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($backup['type']) {
                                            'weekly' => 'success',
                                            'monthly' => 'warning',
                                            'scheduled' => 'info',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($backup['type']) }}</span>
                                </td>
                                <td>{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                                <td>{{ \Carbon\Carbon::parse($backup['date'])->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('backup.download', $backup['filename']) }}" class="btn btn-success" title="Download">
                                            <i class="bx bx-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-primary" onclick="restoreBackup('{{ $backup['filename'] }}')" title="Restore">
                                            <i class="bx bx-refresh"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="deleteBackup('{{ $backup['filename'] }}')" title="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle fs-1"></i>
                                        <p class="mt-2">Belum ada backup</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .avatar-lg {
        width: 5rem;
        height: 5rem;
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

    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#backupTable').DataTable({
            order: [[4, 'desc']], // Sort by date
            pageLength: 10,
            language: {
                url: '{{ asset("assets/datatables/id.json") }}'
            }
        });
    });

    // Create manual backup
    function createManualBackup(type) {
        Swal.fire({
            title: 'Konfirmasi',
            text: `Buat backup ${type}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Backup!',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch('{{ route("backup.manual") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ type: type })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message);
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.value.message,
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    location.reload();
                });
            }
        });
    }

    // Delete backup
    function deleteBackup(filename) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus backup ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('backup/delete') }}/${filename}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                });
            }
        });
    }

    // Restore backup
    function restoreBackup(filename) {
        Swal.fire({
            title: 'Peringatan!',
            html: `
                <p>Restore database akan <strong>menimpa semua data yang ada</strong>.</p>
                <p>Pastikan Anda telah membuat backup terbaru sebelum melanjutkan.</p>
                <p>Ketik <strong>RESTORE</strong> untuk melanjutkan:</p>
                <input type="text" id="confirmText" class="swal2-input" placeholder="Ketik RESTORE">
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Restore Database',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const confirmText = document.getElementById('confirmText').value;
                if (confirmText !== 'RESTORE') {
                    Swal.showValidationMessage('Silakan ketik RESTORE untuk melanjutkan');
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("backup.restore") }}';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';

                const fileInput = document.createElement('input');
                fileInput.type = 'hidden';
                fileInput.name = 'backup_file';
                fileInput.value = filename;

                form.appendChild(csrfInput);
                form.appendChild(fileInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Clean old backups
    function cleanOldBackups() {
        Swal.fire({
            title: 'Bersihkan Backup Lama',
            text: 'Hapus backup yang lebih lama dari periode yang ditentukan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Bersihkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route("backup.clean") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    }
</script>
@endpush

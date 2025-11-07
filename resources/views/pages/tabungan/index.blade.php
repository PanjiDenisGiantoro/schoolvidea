@extends('layouts.app')
@section('title', 'Tabungan Siswa')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    @include('partials.page-title', [
        'title' => 'Tabungan Siswa',
        'subTitle' => 'Kelola Tabungan Siswa',
    ])

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-primary">
                    <h6>Total Setoran</h6>
                    <h4>Rp {{ number_format($total_setoran ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-primary">
                    <h6>Total Penarikan</h6>
                    <h4>Rp {{ number_format($total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-primary">
                    <h6>Total Transaksi</h6>
                    <h4>Rp {{ number_format($total_setoran + $total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card rounded-3 border-0 text-center shadow-sm">
                <div class="card-body text-primary">
                    <h6>Saldo Saat Ini</h6>
                    <h4>Rp {{ number_format($total_setoran - $total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Utama --}}
    <div class="card rounded-3 border-0 shadow-sm">
        <div class="card-body">
            {{-- Action Buttons --}}
            <div class="d-flex justify-content-between mb-3 flex-wrap gap-2 text-end">
<div class="d-flex gap-2 align-items-center">
    <h6 class="text-center align-middle" style="font-size: 16px">Filter Data :</h6>
    <button type="button" class="btn btn-primary rounded-pill filter-status" data-status="all">Semua</button>
    <button type="button" class="btn btn-success rounded-pill filter-status" data-status="1">Aktif</button>
    <button type="button" class="btn btn-danger rounded-pill filter-status" data-status="0">Non Aktif</button>
</div>                <div class="d-flex gap-2">
                <a href="{{ route('tabungan.create') }}"
                    class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                    <i class="bx bx-wallet"></i> Setor
                </a>

                <a href="{{ url('tabungan/tarik') }}"
                    class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                    <i class="bx bx-money-withdraw"></i> Tarik
                </a>

                <a href="{{ route('keuangan_transaksi.index') }}"
                    class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                    <i class="bx bx-transfer"></i> Transaksi
                </a>

                <a href="#" target="_blank"
                    class="btn btn-primary rounded-pill d-flex align-items-center animate-btn gap-1 shadow-sm">
                    <i class="bx bx-printer"></i> Cetak
                </a>
                </div>

            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
            <div class="custom-card-header">
                <span><i class="fa fa-list"></i> Daftar Tabungan Siswa</span>
                <button id="btnProsesPembayaran" class="custom-btn-info">
                    <i class="ri-checkbox-multiple-line"></i>Aktifkan Semua Status
                </button>
            </div>
                <table class="table-bordered table-hover rounded-3 table overflow-hidden text-center align-middle" id="table-tabungan">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th><input class="custom-checkbox" type="checkbox" id="checkAll"></th>
                            <th>#</th>
                            <th>Unit</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas Sekarang</th>
                            <th>Tahun Ajaran</th>
                            <th>Status</th>
                            <th>Saldo Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-tabungan">
                        @forelse($transaksis as $siswa)
                             @php
                                $saldo = $siswa->user->saldo ?? null;
                                $statusBadge = $saldo && $saldo->status == 1 ? 'primary' : 'secondary';                                    $statusText = $saldo && $saldo->status == 1 ? 'Aktif' : 'Tidak Aktif';
                            @endphp
                            <tr data-status="{{ $saldo ? $saldo->status : '0' }}">
                                <td><input type="checkbox" name="checkbox" id="checkbox" class="custom-checkbox"
                                    data-id="{{ $saldo->id ?? '' }}"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $siswa->nisn ?? '-' }}</td>
                                <td>{{ $siswa->user->name ?? '-' }}</td>
                                <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $siswa->tahun_ajaran->tahun_ajaran ?? '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $statusBadge }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($saldo->saldo_akhir ?? 0, 0, ',', '.') }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('tabungan.show', $siswa->id) }}"
                                        class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>
                                    @if ($saldo && $saldo->status == 0)
                                        <a href="{{ route('tabungan.status', $saldo->id) }}"
                                            class="btn btn-sm btn-primary rounded-pill confirm-status">Aktif</a>
                                    @elseif($saldo)
                                        <a href="{{ route('tabungan.status', $saldo->id) }}"
                                            class="btn btn-sm btn-danger rounded-pill confirm-status">Non Aktif</a>
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

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.filter-status');
    const rows = document.querySelectorAll('#tbody-tabungan tr');

    // Filter Status
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const status = btn.dataset.status;
            rows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                } else if (row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            buttons.forEach(b => b.classList.remove('btn-dark'));
            btn.classList.add('btn-dark');
        });
    });

    // SweetAlert Konfirmasi Status
    document.querySelectorAll('.confirm-status').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const url = this.getAttribute('href');

            Swal.fire({
                title: `Anda yakin ingin mengubah status ini?`,
                text: "Tindakan ini akan mengubah status tabungan siswa.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, konfirmasi!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });

    // ✅ Check All Checkbox
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('input[name="checkbox"]');

    checkAll.addEventListener('change', function () {
        checkboxes.forEach(chk => {
            chk.checked = checkAll.checked;
        });
    });

    checkboxes.forEach(chk => {
        chk.addEventListener('change', function () {
            if (!this.checked) {
                checkAll.checked = false;
            } else if (Array.from(checkboxes).every(c => c.checked)) {
                checkAll.checked = true;
            }
        });
    });
// Pengaktifan Masal
const btnMass = document.getElementById('btnProsesPembayaran');

btnMass.addEventListener('click', function () {
    const selected = Array.from(document.querySelectorAll('input[name="checkbox"]:checked'))
        .map(chk => chk.dataset.id)
        .filter(id => id); // hanya ambil yang punya ID saldo

    if (selected.length === 0) {
        Swal.fire('Peringatan', 'Silakan pilih minimal satu siswa!', 'warning');
        return;
    }

    Swal.fire({
        title: 'Yakin ingin mengubah status?',
        text: "Status semua tabungan terpilih akan diubah (aktif/nonaktif).",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, ubah!',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (result.isConfirmed) {
            fetch("{{ route('tabungan.massStatus') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: selected })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', data.message, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error!', 'Terjadi kesalahan saat memproses.', 'error');
            });
        }
    });
});

});
</script>
@endpush


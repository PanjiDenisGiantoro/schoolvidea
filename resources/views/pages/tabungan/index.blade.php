@extends('layouts.app')
@section('title', 'Tabungan Siswa')

@section('content')
    @include('partials.page-title', [
        'title' => 'Tabungan Siswa',
        'subTitle' => 'Kelola transaksi tabungan siswa'
    ])

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm rounded-3 border-0">
                <div class="card-body text-primary">
                    <h6>Total Setoran</h6>
                    <h4>Rp {{ number_format($total_setoran ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm rounded-3 border-0">
                <div class="card-body text-primary">
                    <h6>Total Penarikan</h6>
                    <h4>Rp {{ number_format($total_penarikan ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm rounded-3 border-0">
                <div class="card-body text-primary">
                    <h6>Total Transaksi</h6>
                    <h4>Rp {{ number_format(($total_setoran + $total_penarikan) ?? 0, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Card Utama --}}
    <div class="card shadow-sm rounded-3 border-0">
        <div class="card-body">
            {{-- Action Buttons --}}
            <div class="text-end mb-3 d-flex justify-content-end gap-2 flex-wrap">
                <a href="{{ route('tabungan.create') }}"
                   class="btn btn-primary rounded-pill d-flex align-items-center gap-1 shadow-sm animate-btn">
                    <i class="bx bx-wallet"></i> Setor
                </a>

                <a href="{{ url('tabungan/tarik') }}"
                   class="btn btn-primary rounded-pill d-flex align-items-center gap-1 shadow-sm animate-btn">
                    <i class="bx bx-money-withdraw"></i> Tarik
                </a>

                <a href="#"
                   class="btn btn-primary rounded-pill d-flex align-items-center gap-1 shadow-sm animate-btn">
                    <i class="bx bx-transfer"></i> Transaksi
                </a>

                <a href="#" target="_blank"
                   class="btn btn-primary rounded-pill d-flex align-items-center gap-1 shadow-sm animate-btn">
                    <i class="bx bx-printer"></i> Cetak
                </a>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center rounded-3 overflow-hidden">
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
    <span class="badge rounded-pill
        bg-{{ ($siswa->saldo?->status == 1) ? 'primary' : 'secondary' }}">
        {{ ($siswa->saldo?->status == 1) ? 'Aktif' : 'Tidak Aktif' }}
    </span>
                            </td>
                            <td>Rp {{ number_format($siswa->saldo?->saldo_akhir ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('tabungan.show', $siswa->id) }}"
                                   class="btn btn-sm btn-outline-primary rounded-pill">Detail</a>

                                @if($siswa->saldo?->status === 0)
                                    <a href="{{ route('tabungan.status', $siswa->saldo->id) }}"
                                       class="btn btn-sm btn-primary rounded-pill">Aktif</a>
                                @elseif($siswa->saldo)
                                    <a href="{{ route('tabungan.status', $siswa->saldo->id) }}"
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
            box-shadow: 0 6px 14px rgba(0,0,0,0.15);
        }
    </style>
@endpush

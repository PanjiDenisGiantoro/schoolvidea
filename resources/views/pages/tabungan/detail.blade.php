@extends('layouts.app')
@section('title', 'Detail Tabungan Siswa')

@section('content')
    @include('partials.page-title', [
        'title' => 'Detail Tabungan',
        'subTitle' => 'Tabungan / Keuangan'
    ])

    <div class="row g-4">
        {{-- Detail Siswa --}}
        <div class="col-md-4">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <div class="text-center mb-3">
                    <img src="{{ $siswa->foto ? asset('storage/'.$siswa->foto) : asset('images/default-user.png') }}"
                         alt="Foto Siswa"
                         class="img-fluid rounded-circle shadow-sm border"
                         width="120">
                </div>
                <ul class="list-unstyled small">
                    <li><strong>Nama Lengkap:</strong> {{ $siswa->nama_lengkap }}</li>
                    <li><strong>Nomor Induk (NISN):</strong> {{ $siswa->nisn }}</li>
                    <li><strong>Kelas:</strong> {{ $siswa->kelas->nama_kelas }}</li>
                </ul>
                <hr>
                <ul class="list-unstyled small">
                    <li><strong>Saldo Sebelumnya:</strong> Rp {{ number_format($saldo_awal, 0, ',', '.') }}</li>
                    <li><strong>Saldo Akhir:</strong> <span class="fw-bold text-success">Rp {{ number_format($saldo_akhir, 0, ',', '.') }}</span></li>
                </ul>
            </div>
        </div>

        {{-- Log Transaksi --}}
        <div class="col-md-8">
            <div class="card p-4 shadow-sm rounded-4 border-0">
                <h5 class="fw-bold">Log Transaksi Tabungan</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($log->jenis_transaksi == 'setoran_tabungan')
                                        <span class="badge bg-success">Setoran (Debit)</span>
                                    @else
                                        <span class="badge bg-danger">Penarikan (Kredit)</span>
                                    @endif
                                </td>
                                <td>Rp {{ number_format($log->jumlah, 0, ',', '.') }}</td>
                                <td>{{ $log->keterangan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada transaksi</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

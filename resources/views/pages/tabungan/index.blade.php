    @extends('layouts.app')
    @section('title', 'Tabungan Siswa')

    @section('content')
        @include('partials.page-title', [
            'title' => 'Tabungan Siswa',
            'subTitle' => 'Kelola transaksi tabungan siswa'
        ])

    {{--    <div class="row g-3 mb-4">--}}
    {{--        <div class="col-md-3">--}}
    {{--            <div class="card text-center bg-primary text-white p-3">--}}
    {{--                <h6>Total Saldo Aktif</h6>--}}
    {{--                <h4>Rp {{ number_format($totalSaldoAktif, 0, ',', '.') }}</h4>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="col-md-3">--}}
    {{--            <div class="card text-center bg-success text-white p-3">--}}
    {{--                <h6>Total Setoran</h6>--}}
    {{--                <h4>Rp {{ number_format($totalSetoran, 0, ',', '.') }}</h4>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="col-md-3">--}}
    {{--            <div class="card text-center bg-warning text-white p-3">--}}
    {{--                <h6>Total Penarikan</h6>--}}
    {{--                <h4>Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</h4>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="col-md-3">--}}
    {{--            <div class="card text-center bg-info text-white p-3">--}}
    {{--                <h6>Total Transaksi</h6>--}}
    {{--                <h4>Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</h4>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}

        <div class="card">
            <div class="card-body">
                <div class="text-end mb-3">
                    <a href="{{ route('tabungan.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus-circle"></i> Tambah Transaksi
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
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
                        @forelse($transaksis as $key => $siswa)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $siswa->unit->nama_unit ?? '-' }}</td>
                                <td>{{ $siswa->nisn ?? '-' }}</td>
                                <td>{{ $siswa->user->name ?? '-' }}</td>
                                <td>{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $siswa->tahun_ajaran->tahun_ajaran ?? '-' }}</td>
                                <td>
                    <span class="badge bg-{{ ($siswa->status == 'Aktif' || $siswa->status == 1) ? 'success' : 'secondary' }}">
                        {{ ($siswa->status == 'Aktif' || $siswa->status == 1) ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                                </td>
                                <td>Rp {{ number_format($siswa->saldo->saldo_akhir ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('tabungan.show', $siswa->id) }}" class="btn btn-sm btn-info">Detail</a>
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

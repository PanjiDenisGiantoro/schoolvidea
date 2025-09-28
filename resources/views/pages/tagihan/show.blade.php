@extends('layouts.app')

@section('title', 'Detail Tagihan')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">DETAIL TAGIHAN</h3>

        <a href="{{ route('tagihan.index') }}" class="btn btn-secondary mb-3 rounded-pill">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>

        {{-- Info Siswa --}}
        {{-- Info Siswa --}}
        @php $siswa = $tagihanSiswa->first()->siswa ?? null; @endphp

        <div class="card mb-4 shadow-sm rounded-3 border-0">
            <div class="card-body">
                <h5>{{ optional($siswa->user)->name ?? '-' }} ({{ $siswa->nisn ?? '-' }})</h5>
                <p>Unit: {{ optional($tagihanSiswa->first()->tagihan->unit)->nama_unit ?? '-' }}</p>
                <p>Kelas: {{ optional($tagihanSiswa->first()->tagihan->kelas)->nama_kelas ?? '-' }}</p>
                <p>Jenis Tagihan: {{ ucfirst($tagihanSiswa->first()->tagihan->jenis_tagihan) ?? '-' }}</p>
                <p>Periode: {{ $tagihanSiswa->first()->tagihan->periode ?? 1 }} bulan</p>
                <p>Bulan Mulai: {{ $tagihanSiswa->first()->tagihan->bulan_mulai ?? '-' }}/{{ $tagihanSiswa->first()->tagihan->tahun_mulai ?? '-' }}</p>
            </div>
        </div>


        {{-- Jumlah Tagihan Seluruh Periode --}}
        {{-- Jumlah Tagihan Seluruh Periode --}}
        <div class="card mb-4 shadow-sm rounded-3 border-0">
            <div class="card-body">
                <h5>Jumlah Tagihan Seluruh Periode</h5>
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Nama Kategori</th>
                        <th>Nominal per Bulan</th>
                        <th>Bulan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal Bayar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($dataPerbulan as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['nama_kategori'] }}</td>
                            <td>Rp {{ number_format($row['nominal'], 0, ',', '.') }}</td>
                            <td>{{ $row['bulan'] }} {{ $row['tahun'] }}</td>
                            <td>Rp {{ number_format($row['nominal'], 0, ',', '.') }}</td>
                            <td>
                                @if($row['status'] === 'Lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-danger">Belum Lunas</span>
                                @endif
                            </td>
                            <td>
                                {{ $row['tanggal_bayar'] ? \Carbon\Carbon::parse($row['tanggal_bayar'])->format('d/m/Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Tidak ada data tagihan.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <div class="card mb-4 shadow-sm rounded-3 border-0">
            <div class="card-body">
                <h5>Riwayat Pembayaran</h5>
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Tanggal Bayar</th>
                        <th>Bulan</th>
                        <th>Jumlah</th>
                        <th>Petugas</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($pembayaranSiswa as $index => $pembayaran)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($pembayaran['tanggal_bayar'])->format('d/m/Y') }}</td>
                            <td>{{ $pembayaran['bulan'] ? $pembayaran['bulan'].' '.$pembayaran['tahun'] : '-' }}</td>
                            <td>Rp {{ number_format($pembayaran['jumlah_bayar'], 0, ',', '.') }}</td>
                            <td>{{ $pembayaran['create_by'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada pembayaran.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    </div>

    <script>
        function bayarTagihan(tagihanId, bulan, tahun, nominal, kategoriId) {
            if (!confirm(`Yakin ingin bayar ${bulan}/${tahun} sebesar Rp ${parseInt(nominal).toLocaleString('id-ID')} ?`)) {
                return;
            }

            fetch('/pembayaran/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    tagihan_siswa_id: tagihanId,
                    bulan: bulan,
                    tahun: tahun,
                    nominal: nominal,
                    kategori_id: kategoriId,
                    metode: 'manual',
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status == 1) {
                        alert('Pembayaran berhasil!');
                        location.reload(); // reload halaman supaya status berubah
                    } else {
                        alert('Gagal: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Terjadi kesalahan saat membayar.');
                });
        }
    </script>
@endsection

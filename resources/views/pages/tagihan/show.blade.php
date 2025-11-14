@extends('layouts.app')

@section('title', 'Detail Tagihan')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush
@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">DETAIL TAGIHAN</h3>

        <a href="{{ url()->previous() }}" class="btn btn-secondary rounded-pill mb-3">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>


{{-- Info Siswa --}}
@php
    $siswa = $tagihanSiswa->first()->siswa ?? null;
@endphp
        <div class="col-md-12">
            <div class="card rounded-4 overflow-hidden border-0 pr-4 shadow-lg">
                <div class="row align-items-center g-4">

                    {{-- Kolom kiri: Foto + Nama --}}
                    <div class="col-md-3 profile-card text-center">
                        <div class="profile-photo-wrapper mx-auto mb-3">
<img
    src="{{ $siswa && $siswa->image
        ? asset($siswa->image)
        : asset('images/default-user.png') }}"
    class="profile-photo"
    alt="Foto Siswa"
>
                        </div>
                        <h5 class="fw-bold mb-1 text-white" style="font-size: 18px" id="detail_nama">
                        {{ optional($siswa->user)->name ?? '-' }}</h5>
                        <p class="mb-0 opacity-75" id="detail_nisn">
                        ({{ $siswa->nisn ?? '-' }})</p>
                    </div>

                    {{-- Kolom kanan: Detail Siswa --}}
                    <div class="col-md-8 p-4">
                        <ul class="list-unstyled small mb-0 px-4" style="font-size: 14px">
                            <li class="mb-2"><i class="ri-building-line text-primary me-2"></i>
                                <strong>Unit :</strong> <span id="detail_unit" class="float-end">
                                {{ optional($tagihanSiswa->first()->tagihan->unit)->nama_unit ?? '-' }}</span>
                            </li>
                            <li class="mb-2"><i class="ri-book-line text-success me-2"></i>
                                <strong>Kelas :</strong> <span id="detail_kelas" class="float-end">
                                {{ optional($tagihanSiswa->first()->tagihan->kelas)->nama_kelas ?? '-' }}</span>
                            </li>
                            <li class="mb-2"><i class="ri-calendar-line text-warning me-2"></i>
                                <strong>Jenis Tagihan :</strong> <span id="detail_va" class="float-end">
                                {{ ucfirst($tagihanSiswa->first()->tagihan->jenis_tagihan) ?? '-' }}</span>
                            </li>
                            <li class="mb-2"><i class="ri-user-line text-secondary me-2"></i>
                                <strong>Periode :</strong> <span id="detail_bank" class="float-end">
                                {{ $tagihanSiswa->first()->tagihan->periode ?? 1 }} bulan</span>
                            </li>
                            <li class="mb-2"><i class="ri-map-pin-line text-danger me-2"></i>
                                <strong>Bulan Mulai :</strong> <span id="detail_norek" class="float-end">
                                {{ $tagihanSiswa->first()->tagihan->bulan_mulai ?? '-' }}/{{ $tagihanSiswa->first()->tagihan->tahun_mulai ?? '-' }}</span>
                            </li>
                            <li><i class="ri-phone-line text-success me-2"></i>
                                <strong>Telepon :</strong> <span id="detail_telp" class="float-end">{{ $siswa->no_hp }}</span>
                            </li>
                            <li><i class="ri-wallet-line text-success me-2"></i>
                                <strong>No VA :</strong> <span id="detail_telp" class="float-end">{{ $siswa->va_siswa }}</span>
                            </li>

                        </ul>
                    </div>

                </div>
            </div>
        </div>

        {{-- Jumlah Tagihan Seluruh Periode --}}
        {{-- Jumlah Tagihan Seluruh Periode --}}
        <div class="card rounded-3 mb-4 border-0 shadow-sm">
            <div class="card-body">
            <h5>Tagihan Seluruh Periode</h5>
            <div class="table-responsive">

                <table class="table-bordered table-hover table overflow-hidden text-center align-middle">
                    <thead class="table-primary text-center text-nowrap align-middle ">
                        <tr>
                            <th>#</th>
                            <th>Kode Kategori</th>
                            <th>Kode Tagihan</th>
                            <th>Nama Tagihan</th>
                            <th>Bulan</th>
                            <th>Biaya Tagihan</th>
                            <th>Potongan</th>
                            <th>Jml. Tunggakan</th>
                            <th>Jml. Dibayar</th>
                            <th>Status</th>
                            <th>Wkt. Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataPerbulan as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>{{ $row['nama_kategori'] }}</td>
                                <td>{{ $row['bulan'] }} {{ $row['tahun'] }}</td>

                                <td>Rp {{ number_format($row['nominal'], 0, ',', '.') }}</td>
                                 <td>-</td>
                                <td>Rp {{ number_format($row['nominal'], 0, ',', '.') }}</td>
                                <td>-</td>
                                <td>
                                    @if ($row['status'] === 'Lunas')
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
        </div>

        <div class="card rounded-3 mb-4 border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
<h5>Riwayat Transaksi</h5>
                <table class="table-bordered table-hover table overflow-hidden text-center align-middle">
                    <thead class="table-primary text-center text-nowrap align-middle">
                        <tr>
                            <th>#</th>
                            <th>Wkt. Transaksi</th>
                            <th>Bulan</th>
                            <th>Jml. Transaksi</th>
                            <th>Metode Bayar</th>
                            <th>Status</th>
                            <th>Petugas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayaranSiswa as $index => $pembayaran)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($pembayaran['tanggal_bayar'])->format('d/m/Y') }}</td>
                                <td>{{ $pembayaran['bulan'] ? $pembayaran['bulan'] . ' ' . $pembayaran['tahun'] : '-' }}</td>
                                <td>Rp {{ number_format($pembayaran['jumlah_bayar'], 0, ',', '.') }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>{{ $pembayaran['create_by'] }}</td>
                                <td>
                                    <a href="{{ url('#') }}" class="btn btn-warning"><i class="bx bx-eye" style="font-size: 20px"></i></a>
                                    <a href="{{ url('#') }}" class="btn btn-warning"><i class="bx bx-printer" style="font-size: 20px"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12">Belum ada pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                </div>
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

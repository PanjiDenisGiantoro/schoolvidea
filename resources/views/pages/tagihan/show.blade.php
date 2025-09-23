@extends('layouts.app')

@section('title', 'Detail Tagihan')

@section('content')
    <div class="container-fluid">
        <h3 class="mb-4">DETAIL TAGIHAN</h3>

        <a href="{{ route('tagihan.index') }}" class="btn btn-secondary mb-3 rounded-pill">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>

        {{-- Info Siswa --}}
        <div class="card mb-4 shadow-sm rounded-3 border-0">
            <div class="card-body">
                <h5>{{ $tagihanSiswa->siswa->user->name }} ({{ $tagihanSiswa->siswa->nisn }})</h5>
                <p>Unit: {{ $tagihanSiswa->tagihan->unit->nama_unit ?? '-' }}</p>
                <p>Kelas: {{ $tagihanSiswa->tagihan->kelas->nama_kelas ?? '-' }}</p>
                <p>Jenis Tagihan: {{ ucfirst($tagihanSiswa->tagihan->jenis_tagihan) }}</p>
                <p>Periode: {{ $tagihanSiswa->tagihan->periode ?? 1 }} bulan</p>
                <p>Bulan Mulai: {{ $tagihanSiswa->tagihan->bulan_mulai }}/{{ $tagihanSiswa->tagihan->tahun_mulai }}</p>
            </div>
        </div>

        {{-- 2. Jumlah Tagihan --}}
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
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tagihanSiswa->tagihan->items ?? [] as $index => $item)
                        @php
                            $periode = $tagihanSiswa->tagihan->periode ?? 1;
                            $bulan_mulai = $tagihanSiswa->tagihan->bulan_mulai;
                            $tahun_mulai = $tagihanSiswa->tagihan->tahun_mulai;
                        @endphp

                        @for($i = 0; $i < $periode; $i++)
                            @php
                                $bulan = ($bulan_mulai + $i - 1) % 12 + 1;
                                $tahun = $tahun_mulai + intdiv(($bulan_mulai + $i - 1), 12);

                                // cek sudah dibayar
                                $sudah_bayar = optional($tagihanSiswa->siswa->pembayaran_tagihan)
                                    ->where('tagihan_id', $tagihanSiswa->tagihan_id)
                                    ->where('kategori_id', $item->kategori_id)
                                    ->where('bulan', $bulan)
                                    ->where('tahun', $tahun)
                                    ->sum('jumlah_bayar');

                                $sisa = $item->nominal - $sudah_bayar;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}.{{ $i + 1 }}</td>
                                <td>{{ $item->kategori->nama_kategori ?? '-' }}</td>
                                <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->format('F Y') }}</td>
                                <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td>
                                    @if($sisa > 0)
                                        <a href="{{ route('tagihan.bayar', [
                                        'id' => $tagihanSiswa->id,
                                        'kategori_id' => $item->kategori_id,
                                        'bulan' => $bulan,
                                        'tahun' => $tahun
                                    ]) }}" class="btn btn-success btn-sm rounded-pill">
                                            Bayar
                                        </a>
                                    @else
                                        <span class="badge bg-success rounded-pill">Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        @endfor
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. Pembayaran --}}
        <div class="card mb-4 shadow-sm rounded-3 border-0">
            <div class="card-body">
                <h5>Riwayat Pembayaran</h5>
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>Tanggal Bayar</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tagihanSiswa->siswa->pembayaran_tagihan ?? [] as $index => $pembayaran)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($pembayaran->tanggal_bayar)->format('d/m/Y') }}</td>
                            <td>{{ optional($pembayaran->kategori)->nama_kategori ?? '-' }}</td>
                            <td>Rp {{ number_format($pembayaran->jumlah_bayar, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('tagihan.bayar', $tagihanSiswa->id) }}" class="btn btn-success rounded-pill">
                <i class="fa fa-credit-card"></i> Bayar Tagihan
            </a>
        </div>
    </div>
@endsection

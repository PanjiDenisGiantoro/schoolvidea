@extends('layouts.app')

@section('title', 'Detail Potongan')

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Back Button -->
            <a href="{{ route('potongan.index') }}" class="btn btn-secondary mb-3">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>

            <!-- Potongan Detail Section -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Detail Potongan</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><strong>Nama Unit:</strong> {{ $potongan->unit->nama_unit }}</li>
                                <li><strong>Kelas:</strong> {{ $potongan->kelas->nama_kelas }}</li>
                                <li><strong>Kategori Tagihan:</strong> {{ $potongan->kategoriTagihan->nama_kategori }}</li>
                                <li><strong>Tipe Potongan:</strong> {{ $potongan->tipe_potongan }}</li>
                                <li><strong>Nilai Potongan:</strong> Rp {{ number_format($potongan->nilai, 2, ',', '.') }}</li>
                                <li><strong>Keterangan:</strong> {{ $potongan->keterangan ?? '-' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <!-- Potongan Siswa Section -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Potongan Tagihan</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Tagihan</th>
                                    <th>Biaya Tagihan</th>
                                    <th>Nilai Potongan</th>
                                    <th>Nominal Potongan</th>
                                    <th>Keterangan Tambahan</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($potongan->potonganSiswa as $potonganSiswa)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $potongan->kategoritagihan->nama_kategori }}</td>
                                        <td>Rp {{ number_format($potonganSiswa->tagihan->jumlah, 2, ',', '.') }}</td>
                                        <td>Rp {{ number_format($potonganSiswa->nominal, 2, ',', '.') }}</td>
                                        <td>Rp {{ number_format($potonganSiswa->tagihan->jumlah - $potonganSiswa->nominal, 2, ',', '.') }}</td>
                                        <td>-</td> <!-- Keterangan Tambahan, adjust as needed -->
                                        <td>
                                            <div class="d-flex gap-3">

                                            <a href="" class="link-warning text-muted">
                                                <i class="ri-edit-line align-middle fs-20"></i>
                                                Edit
                                            </a>
                                            <a href="" class="link-danger text-muted">
                                                <i class="ri-delete-bin-5-line align-middle fs-20"></i>
                                                Hapus
                                            </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

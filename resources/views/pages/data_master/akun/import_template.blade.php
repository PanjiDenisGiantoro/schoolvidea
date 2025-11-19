@extends('layouts.app')
@section('title', 'Import Template Akuns')

@section('content')

    @include('partials.page-title', [
        'title' => 'Import Template Akuns',
        'subTitle' => 'Data Akun',
    ])

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Import Template Akuns</h5>

                    <form action="{{ route('akun.import-template.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="file" class="form-label fw-bold">Pilih File Template</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted d-block mt-2">Format yang didukung: Excel (.xlsx, .xls) atau CSV</small>
                            @error('file')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="alert alert-info" role="alert">
                            <h6 class="alert-heading mb-2">Format File Template</h6>
                            <p class="mb-3">File harus memiliki kolom header berikut:</p>
                            <ul class="mb-3">
                                <li><strong>kode_akun</strong> (wajib) - Kode akun unik, contoh: 11, 11.1, 11.1.01</li>
                                <li><strong>nama_akun</strong> (wajib) - Nama akun, contoh: ASET LANCAR</li>
                                <li><strong>tipe</strong> (wajib) - Tipe akun: ASET, LIABILITAS, EKUITAS, PENDAPATAN, BEBAN</li>
                                <li><strong>parent_kode</strong> (opsional) - Kode akun induk, contoh: 11 (untuk sub-akun)</li>
                                <li><strong>kategori_akun</strong> (opsional) - Kategori, contoh: transaksi, tabungan</li>
                                <li><strong>status</strong> (opsional) - 1 untuk aktif, 0 untuk tidak aktif (default: 1)</li>
                                <li><strong>keterangan</strong> (opsional) - Deskripsi atau catatan akun</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading mb-2">Informasi Penting</h6>
                            <ul class="mb-0">
                                <li>Unit ID akan otomatis diambil dari unit Anda (Auth::user()->unit_id)</li>
                                <li>Jika akun sudah ada dengan kode_akun yang sama, akan dilewati</li>
                                <li>Jika parent_kode tidak ditemukan, parent_id akan kosong</li>
                                <li>Semua akun yang diimpor akan memiliki status aktif (1) secara default</li>
                            </ul>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload me-2"></i> Import Template
                            </button>
                            <a href="{{ route('akun.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-2"></i> Batal
                            </a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <h6 class="mb-3">Contoh Format File Excel/CSV:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>kode_akun</th>
                                    <th>nama_akun</th>
                                    <th>tipe</th>
                                    <th>parent_kode</th>
                                    <th>kategori_akun</th>
                                    <th>status</th>
                                    <th>keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>11</td>
                                    <td>ASET LANCAR</td>
                                    <td>ASET</td>
                                    <td></td>
                                    <td></td>
                                    <td>1</td>
                                    <td>Aset lancar sekolah</td>
                                </tr>
                                <tr>
                                    <td>11.1</td>
                                    <td>KAS & BANK</td>
                                    <td>ASET</td>
                                    <td>11</td>
                                    <td></td>
                                    <td>1</td>
                                    <td>Kas dan rekening bank</td>
                                </tr>
                                <tr>
                                    <td>11.1.01</td>
                                    <td>KAS SEKOLAH</td>
                                    <td>ASET</td>
                                    <td>11.1</td>
                                    <td>transaksi</td>
                                    <td>1</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>11.1.02</td>
                                    <td>KAS BANK</td>
                                    <td>ASET</td>
                                    <td>11.1</td>
                                    <td>transaksi</td>
                                    <td>1</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

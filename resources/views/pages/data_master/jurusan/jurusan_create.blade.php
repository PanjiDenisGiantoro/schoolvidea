@extends('layouts.app')
@section('title', isset($jurusan) ? (isset($show) && $show ? 'Lihat Jurusan' : 'Edit Jurusan') : 'Tambah Jurusan')

@section('content')
    @include('partials.page-title', [
        'title' => isset($jurusan) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Jurusan',
    ])

    <div class="card">
        <div class="card-body">
            <form id="jurusanForm"
                action="{{ isset($jurusan) ? route('jurusan.update', $jurusan->id) : route('jurusan.store') }}"
                method="POST">
                @csrf
                @if (isset($jurusan))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" required>
                                <option>-- Pilih Unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $jurusan->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahun_ajaran as $t)
                                    <option value="{{ $t->id }}"
                                        {{ old('tahun_ajaran_id', $jurusan->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                        {{ $t->tahun_ajaran }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Kode Jurusan --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="kode_jurusan" label="Kode Jurusan"
                            placeholder="Masukkan Kode Jurusan" icon="bx bx-barcode" :value="old('kode_jurusan', $jurusan->kode_jurusan ?? '')" required />
                    </div>

                    {{-- Nama Jurusan --}}
                    <div class="col-md-4">
                        <x-input-field type="text" name="nama_jurusan" label="Nama Jurusan"
                            placeholder="Masukkan Nama Jurusan" icon="bx bx-book" :value="old('nama_jurusan', $jurusan->nama_jurusan ?? '')" required />
                    </div>





                    {{-- Status --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="1"
                                    {{ old('status', $jurusan->status ?? '') == '1' ? 'selected' : '' }}>
                                    Aktif
                                </option>
                                <option value="0"
                                    {{ old('status', $jurusan->status ?? '') == '0' ? 'selected' : '' }}>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control" rows="2" placeholder="Tambahkan keterangan">{{ old('keterangan', $jurusan->keterangan ?? '') }}</textarea>
                        </div>
                    </div>

                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($jurusan) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('jurusan/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (isset($show) && $show)
            document.addEventListener('DOMContentLoaded', function() {
                const formElements = document.querySelectorAll(
                    '#jurusanForm input, #jurusanForm textarea, #jurusanForm select, #jurusanForm button[type="submit"]'
                    );
                formElements.forEach(el => {
                    el.disabled = true;
                    if (el.type === 'submit') {
                        el.style.display = 'none';
                    }
                });
            });
        @endif
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('jurusanForm');

            form.addEventListener('submit', function(e) {
                e.preventDefault(); // cegah submit langsung

                Swal.fire({
                    title: 'Apakah data sudah benar?',
                    text: "Pastikan semua data sudah diisi dengan benar sebelum menyimpan.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // tampilkan loading sebelum submit
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Harap tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // submit form setelah konfirmasi
                        form.submit();
                    }
                });
            });
        });
    </script>

    {{-- Alert Sukses & Error --}}
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `
        <ul style="text-align:left;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
                </ul>
`,
                confirmButtonColor: '#d33',
            });
        </script>
    @endif
@endpush

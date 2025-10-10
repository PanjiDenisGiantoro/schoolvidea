@extends('layouts.app')
@section('title', isset($kelas) ? (isset($show) && $show ? 'Lihat Kelas' : 'Edit Kelas') : 'Tambah Kelas')

@section('content')
    @include('partials.page-title', [
        'title' => isset($kelas) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Kelas'
    ])

    <div class="card">
        <div class="card-body">
            <form id="kelasForm"
                  action="{{ isset($kelas) ? route('kelas.update', $kelas->id) : route('kelas.store') }}"
                  method="POST">
                @csrf
                @if(isset($kelas))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Nama Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $kelas->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                            <select name="tahun_ajaran_id"  id="choices-single-no-sorting"  data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach($tahun_ajaran as $t)
                                    <option value="{{ $t->id }}"
                                        {{ old('tahun_ajaran_id', $officer->tahun_ajaran_id ?? ($tahun_ajaran_selected->id ?? '')) == $t->id ? 'selected' : '' }}>
                                        {{ $t->tahun_ajaran ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">-- Pilih Status --</option>
                                <option value="1" {{ old('status', $kelas->status ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status', $kelas->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <x-input-field type="text" name="kode_kelas" label="Kode Kelas"
                                           placeholder="Masukkan Kode Kelas" icon="bx bx-book"
                                           :value="old('kode_kelas', $kelas->kode_kelas ?? '')" required />
                        </div>

                        <div class="mb-3">
                            <label for="jurusan_id">Jurusan</label>
                            <select name="jurusan_id" id="jurusan_id"  class="form-select" data-choices data-choices-sorting-false>
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusan as $jurusans)
                                    <option value="{{ $jurusans->id }}"
                                        {{ old('jurusan_id', $kelas->jurusan_id ?? '') == $jurusans->id ? 'selected' : '' }}>
                                        {{ $jurusans->nama_jurusan }} ({{ $jurusans->unit->nama_unit ?? '-' }}
                                        - {{ $jurusans->tahun_ajaran->tahun_ajaran ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <x-input-field type="text" name="nama_kelas" label="Nama Kelas"
                                           placeholder="Masukkan Nama Kelas" icon="bx bx-book"
                                           :value="old('nama_kelas', $kelas->nama_kelas ?? '')" required />
                        </div>
                        <div class="mb-3">
                            <label for="officer_id" class="form-label">Wali Kelas</label>
                            <select name="officer_id" id="officer_id" class="form-select" data-choices data-choices-sorting-false required>
                                <option value="">-- Pilih Wali Kelas --</option>
                                @foreach($wali as $w)
                                    <option value="{{ $w->id }}" {{ old('officer_id', $kelas->officer_id ?? '') == $w->id ? 'selected' : '' }}>
                                        {{ $w->user->name ?? 'Tanpa Nama' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>











                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($kelas) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('kelas/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(isset($show) && $show)
        document.addEventListener('DOMContentLoaded', function() {
            const formElements = document.querySelectorAll('#kelasForm input, #kelasForm textarea, #kelasForm select, #kelasForm button[type="submit"]');
            formElements.forEach(el => {
                el.disabled = true;
                if(el.type === 'submit'){
                    el.style.display = 'none';
                }
            });
        });
        @endif
    </script>
    {{-- Konfirmasi Submit --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('kelasForm');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
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
                        Swal.fire({
                            title: 'Menyimpan...',
                            text: 'Harap tunggu sebentar.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        });
    </script>

    {{-- Alert Sukses & Error --}}
    @if(session('success'))
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

    @if($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                html: `
        <ul style="text-align:left;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
                </ul>
`,
                confirmButtonColor: '#d33',
            });
        </script>
    @endif
@endpush

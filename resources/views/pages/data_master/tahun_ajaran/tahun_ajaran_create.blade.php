@extends('layouts.app')
@section('title', isset($tahun_ajaran) ? (isset($show) && $show ? 'Lihat Tahun Ajaran' : 'Edit Tahun Ajaran') : 'Tambah Tahun Ajaran')

@section('content')
    @include('partials.page-title', [
        'title' => isset($tahun_ajaran) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Tahun Ajaran'
    ])
    <div class="card">
        <div class="card-body">
            <form id="tahunAjaranForm"
                  action="{{ isset($tahun_ajaran) ? route('tahun_ajaran.update', $tahun_ajaran->id) : route('tahun_ajaran.store') }}"
                  method="POST">
                @csrf
                @if(isset($tahun_ajaran))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-field type="text" name="tahun_ajaran" label="Tahun Ajaran"
                                       placeholder="Contoh: 2024/2025" icon="bx bx-calendar"
                                       :value="old('tahun_ajaran', $tahun_ajaran->tahun_ajaran ?? '')" required />
                    </div>

                    <div class="col-md-6">
                        <label for="semester" class="form-label">Semester</label>
                        <select name="semester" id="semester" class="form-select">
                            <option value="Ganjil" {{ old('semester', $tahun_ajaran->semester ?? '') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                            <option value="Genap" {{ old('semester', $tahun_ajaran->semester ?? '') == 'Genap' ? 'selected' : '' }}>Genap</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control form-control-lg rounded"
                               value="{{ old('tanggal_mulai', isset($tahun_ajaran->tanggal_mulai) ? \Carbon\Carbon::parse($tahun_ajaran->tanggal_mulai)->format('Y-m-d') : '') }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control form-control-lg rounded"
                               value="{{ old('tanggal_selesai', isset($tahun_ajaran->tanggal_selesai) ? \Carbon\Carbon::parse($tahun_ajaran->tanggal_selesai)->format('Y-m-d') : '') }}"
                               required>
                    </div>




                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select w-full">
                            <option value="1" {{ old('status', $tahun_ajaran->status ?? '') == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $tahun_ajaran->status ?? '') == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($tahun_ajaran) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('tahun_ajaran/') }}" class="btn btn-secondary">Batal</a>
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
            const formElements = document.querySelectorAll('#tahunAjaranForm input, #tahunAjaranForm select, #tahunAjaranForm button[type="submit"]');
            formElements.forEach(el => {
                el.disabled = true;
                if(el.type === 'submit'){
                    el.style.display = 'none';
                }
            });
        });
        @endif
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('tahunAjaranForm');

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
@endpush

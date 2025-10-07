@extends('layouts.app')
@section('title', isset($tipe_unit) ? (isset($show) && $show ? 'Lihat Tipe Unit' : 'Edit Tipe Unit') : 'Tambah Tipe Unit')

@section('content')
    @include('partials.page-title', [
        'title' => isset($tipe_unit) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Tipe Unit'
    ])
    <div class="card">
        <div class="card-body">
            <form id="tahunAjaranForm"
                  action="{{ isset($tipe_unit) ? route('tipe_unit.update', $tipe_unit->id) : route('tipe_unit.store') }}"
                  method="POST">
                @csrf
                @if(isset($tipe_unit))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-field type="text" name="nama_tipe_unit" label="Tipe Unit"
                                       placeholder="Masukkan Tipe Unit" icon="bx bx-unit"
                                       :value="old('nama_tipe_unit', $tipe_unit->nama_tipe_unit ?? '')" required />
                    </div>


                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="1" {{ old('status', $tipe_unit->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $tipe_unit->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($tipe_unit) ? 'Update' : 'Simpan' }}
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

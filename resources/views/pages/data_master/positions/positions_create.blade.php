@extends('layouts.app')
@section('title', isset($positions) ? (isset($show) && $show ? 'Lihat Jabatan' : 'Edit Jabatan') : 'Tambah Jabatan')

@section('content')
    @include('partials.page-title', [
        'title' => isset($positions) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Jabatan'
    ])
    <div class="card">
        <div class="card-body">
            <form id="positionsForm"
                  action="{{ isset($positions) ? route('positions.update', $positions->id) : route('positions.store') }}"
                  method="POST">
                @csrf
                @if(isset($positions))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-field type="text" name="positions_name" label="Jabatan"
                                    placeholder="Masukkan Jabatan" icon="bx bx-unit"
                                    :value="old('positions_name', $positions->positions_name ?? '')" required />
                    </div>


                    <div class="col-md-6">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select">
                            <option value="1" {{ old('status', $positions->status ?? '') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('status', $positions->status ?? '') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($positions) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('positions/') }}" class="btn btn-secondary">Batal</a>
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
            const formElements = document.querySelectorAll('#positionsForm input, #positionsForm select, #positionsForm button[type="submit"]');
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
            const form = document.getElementById('positionsForm');

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

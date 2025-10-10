@extends('layouts.app')
@section('title', isset($roles) ? (isset($show) && $show ? 'Lihat Roles' : 'Edit Roles') : 'Tambah Roles')

@section('content')
    @include('partials.page-title', [
        'title' => isset($roles) ? (isset($show) && $show ? 'Lihat Data' : 'Edit Data') : 'Tambah Data',
        'subTitle' => 'Roles'
    ])
    <div class="card">
        <div class="card-body">
            <form id="tahunAjaranForm"
                  action="{{ isset($roles) ? route('roles.update', $roles->id) : route('roles.store') }}"
                  method="POST">
                @csrf
                @if(isset($roles))
                    @method('PUT')
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <x-input-field type="text" name="name" label="Roles"
                                       placeholder="Masukkan Roles" icon="bx bx-user "
                                       :value="old('name', $roles->name ?? '')" required />
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        {{ isset($roles) ? 'Update' : 'Simpan' }}
                    </button>
                    <a href="{{ url('roles/') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
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

    {{-- Konfirmasi Submit --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('tahunAjaranForm');

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

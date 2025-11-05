@extends('layouts.app')
@section('title', 'Update Password')

@section('content')

    @include('partials.page-title', [
        'title' => 'Update Password',
        'subTitle' => 'Ganti Kata Sandi'
    ])

    <div class="card">
        <div class="card-body">
            <div class="row g-5">
                <div class="col-lg-12">
                    <h5 class="card-title mb-3">Update Kata Sandi</h5>
                    <!-- Form Update Password -->
                    <form action="{{ route('profile.updatePassword') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="new_password">Password Baru</label>
                            <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Masukkan password baru" required>
                            @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Konfirmasi password baru" required>
                        </div>

                        <div class="text-center d-grid">
                            <button type="submit" class="btn btn-primary link-danger">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        // SweetAlert2 untuk konfirmasi
        $('.link-danger').on('click', function(e) {
            e.preventDefault(); // cegah link langsung ke href
            var url = $(this).attr('href');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // redirect ke URL hapus
                    window.location.href = url;
                }
            });
        });
    </script>
@endpush

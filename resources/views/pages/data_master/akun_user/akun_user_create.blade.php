@extends('layouts.app')
@section('title', isset($show) && $show ? 'Lihat Akun User' : 'Edit Akun User')

@section('content')
    @include('partials.page-title', [
        'title' => isset($show) && $show ? 'Lihat Data' : 'Edit Data',
        'subTitle' => 'Akun User',
    ])

    <div class="card">
        <div class="card-body">
            <form id="userForm" action="{{ route('akun-user.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Unit --}}
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="unit_id" class="form-label">Unit</label>
                            <select name="unit_id" id="unit_id" class="form-select" required
                                {{ isset($show) && $show ? 'disabled' : '' }}>
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}"
                                        {{ old('unit_id', $user->unit_id ?? '') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nama_unit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Nama --}}
                    <div class="col-md-4">
                        <x-input-field type="text" id="name" name="name" label="Nama Lengkap"
                            placeholder="Nama Lengkap" icon="bx bx-user" :value="old('name', $user->name ?? '')" :disabled="isset($show) && $show" />
                    </div>

                    {{-- Email --}}
                    <div class="col-md-4">
                        <x-input-field type="email" id="email" name="email" label="Email"
                            placeholder="Alamat Email" icon="bx bx-envelope" :value="old('email', $user->email ?? '')" :disabled="isset($show) && $show" />
                    </div>

                    {{-- Username --}}
                    <div class="col-md-4">
                        <x-input-field type="text" id="username" name="username" label="Username" placeholder="Username"
                            icon="bx bx-user" :value="old('username', $user->username ?? '')" :disabled="isset($show) && $show" />
                    </div>

                    {{-- Password --}}
                    <div class="col-md-4">
                        <x-input-field type="password" id="password" name="password" label="Password"
                            placeholder="Kosongkan jika tidak ingin mengubah password" icon="bx bx-lock" :value="old('password')"
                            :disabled="isset($show) && $show" />
                    </div>

                    {{-- RFID --}}
                    <div class="col-md-4">
                        <x-input-field type="text" id="rfid_no" name="rfid_no" label="Nomor RFID" placeholder="RFID"
                            icon="bx bx-card" :value="old('rfid_no', $user->rfid_no ?? '')" :disabled="isset($show) && $show" />
                    </div>
                    {{--                    yayasan_id dropdown --}}
                    <div class="mb-4">
                        <label for="akses_yayasan" class="form-label">Akses Yayasan</label>
                        <select name="akses_yayasan" id="akses_yayasan" class="form-select" data-choices
                            data-choices-sorting-false @if (isset($show) && $show) disabled @endif>
                            <option value="">-- Pilih Yayasan --</option>
                            @foreach ($yayasan as $y)
                                <option value="{{ $y->id }}"
                                    {{ old('akses_yayasan', $user->yayasan_id ?? '') == $y->id ? 'selected' : '' }}>
                                    {{ $y->nama_yayasan }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Jika "Ya", user akan memiliki akses ke semua unit dalam yayasan</small>
                    </div>

                </div>

                <div class="mt-3 text-end">
                    @if (!isset($show) || !$show)
                        <button type="submit" class="btn btn-success">
                            Update
                        </button>
                    @endif
                    <a href="{{ url('akun-user') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Hanya aktifkan konfirmasi ketika mode edit --}}
    @if (!isset($show) || !$show)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('userForm');

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
                                didOpen: () => Swal.showLoading()
                            });
                            form.submit();
                        }
                    });
                });
            });
        </script>
    @endif

    {{-- Alert sukses --}}
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

    {{-- Alert error --}}
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

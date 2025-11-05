<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Title meta --}}
    @include('partials.title-meta', ['subTitle' => 'Pendaftaran Portal VideaClass'])

    {{-- CSS --}}
    @include('partials.head-css')

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="authentication-bg">

<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="card auth-card shadow">
                    <div class="card-body">
                        <div class="p-3">

                            <div class="mx-auto mb-5 auth-logo text-center">
                                <a href="{{ url('/') }}" class="logo-dark">
                                    <img src="{{ asset('assets/images/videa.png') }}" height="30" alt="logo dark">
                                </a>

                                <a href="{{ url('/') }}" class="logo-light">
                                    <img src="{{ asset('assets/images/videa.png') }}" height="30" alt="logo light">
                                </a>
                            </div>

                            {{-- Heading --}}
                            <div class="text-center">
                                <h3 class="fw-bold text-dark fs-20">Buat Portal VideaClass Sekarang</h3>
                                <p class="text-muted mt-1 mb-4">
                                    Beritahu kami sedikit tentang sekolah Anda untuk memulai.
                                </p>
                            </div>

                            {{-- Flash messages --}}
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Form Pendaftaran --}}
                            <div class="p-3">
                                <form action="{{ route('landing.store') }}" method="POST" class="authentication-form" novalidate>
                                    @csrf

                                    {{-- Nama Sekolah --}}
                                    <x-input-field type="text" name="school_name" label="Nama Sekolah"
                                                   placeholder="Contoh: SMA Negeri 2 Bengkulu Tengah" icon="bx bx-buildings"
                                                   :value="old('school_name')" />

                                    {{-- NPSN Sekolah --}}
                                    <x-input-field type="text" name="npsn" label="NPSN Sekolah"
                                                   placeholder="Contoh: 12345678" icon="bx bx-id-card"
                                                   :value="old('npsn')" />

                                    {{-- Alamat Sekolah --}}
                                    <x-input-field type="text" name="address" label="Alamat Sekolah"
                                                   placeholder="Jalan, Kota, Provinsi" icon="bx bx-map"
                                                   :value="old('address')" />

                                    {{-- Nama Lengkap Anda --}}
                                    <x-input-field type="text" name="full_name" label="Nama Lengkap Anda"
                                                   placeholder="Nama PIC" icon="bx bx-user"
                                                   :value="old('full_name')" />

                                    {{-- Email Anda --}}
                                    <x-input-field type="email" name="email" label="Email Anda"
                                                   placeholder="nama@sekolah.sch.id" icon="bx bx-envelope"
                                                   :value="old('email')" />

                                    {{-- No HP --}}
                                    <x-input-field type="text" name="no_hp" label="No HP"
                                                   placeholder="08xxxxxxxxxx" icon="bx bx-phone"
                                                   :value="old('no_hp')" />

                                    {{-- Tipe Unit --}}
                                    <div class="mb-3">
                                        <label for="tipe_unit_id" class="form-label">Tipe Unit</label>
                                        <select name="tipe_unit_id" id="tipe_unit_id" class="form-select" data-choices data-choices-sorting-false>
                                            <option value="">-- Pilih Tipe Unit --</option>
                                            @foreach($tipeunit as $tu)
                                                <option value="{{ $tu->id }}" {{ old('tipe_unit_id', $tu->id ?? '') == $tu->id ? 'selected' : '' }}>
                                                    {{ $tu->nama_tipe_unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Yayasan ID --}}
                                    <x-input-field type="text" name="yayasan_id" label="Yayasan"
                                                   placeholder="Masukkan yayasan " icon="bx bx-building-house"
                                                   :value="old('yayasan_id')" />

                                    {{-- Persetujuan S&K --}}
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="agree" id="agree"
                                                   value="1" {{ old('agree') ? 'checked' : '' }}>
                                            <label class="form-check-label text-muted" for="agree">
                                                * Melanjutkan berarti anda telah menyetujui
                                                <a href="#" class="text-primary text-decoration-underline">Syarat &amp; Ketentuan Layanan</a> yang berlaku
                                            </label>
                                        </div>
                                    </div>

                                    <div class="text-center d-grid">
                                        <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit">
                                            <i class='bx bx-send fs-18'></i> Lanjutkan Pendaftaran
                                        </button>
                                    </div>
                                </form>

                            </div>

                            {{-- Footer text --}}
                            <p class="text-muted text-center mt-4 mb-0">
                                &copy; {{ date('Y') }} VideaClass • Data Anda aman dan hanya digunakan untuk aktivasi portal.
                            </p>
                        </div>
                    </div>
                </div> <!-- end card -->
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
@include('partials.vendor-scripts')

<script>
    document.querySelector('.authentication-form').addEventListener('submit', function(e) {
        e.preventDefault();  // Mencegah pengiriman form secara langsung

        // Menampilkan SweetAlert2
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Pastikan semua data sudah benar!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika konfirmasi, kirimkan form
                this.submit();  // Mengirimkan form setelah konfirmasi
            }
        });
    });
</script>

</body>
</html>

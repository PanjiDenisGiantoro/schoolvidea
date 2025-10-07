
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
                                    <img src="{{ asset('assets/images/logo-dark.png') }}" height="30" alt="logo dark">
                                </a>

                                <a href="{{ url('/') }}" class="logo-light">
                                    <img src="{{ asset('assets/images/logo-white.png') }}" height="30" alt="logo light">
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
                                <form action="{{ route('landing.storeportal',[$trialRegistration->id]) }}" method="POST">
                                    @method('PUT')
                                    @csrf {{-- Nama Lengkap --}}
                                    <x-input-field type="text" name="full_name" label="Nama Lengkap" placeholder="Nama Lengkap Anda"
                                                   :value="old('full_name')"/> {{-- Username --}}
                                    <x-input-field type="text" name="username" label="Username" placeholder="Username"
                                                   :value="old('username')"/> {{-- Password --}}
                                   <x-input-field type="text" name="no_hp" label="No HP" placeholder="No HP"
                                                   :value="old('no_hp')"/> {{-- Email --}}
                                    <x-input-field type="email" name="email" label="Email" placeholder="Email"
                                                   :value="old('email')"/> {{-- Jenis Kelamin --}}
                                    <x-input-field type="text" name="gender" label="Jenis Kelamin" placeholder="Jenis Kelamin"
                                                   :value="old('gender')"/> {{-- Tempat Lahir --}}
                                    <x-input-field type="text" name="place_of_birth" label="Tempat Lahir" placeholder="Tempat Lahir"
                                                   :value="old('place_of_birth')"/> {{-- Tanggal Lahir --}}
                                    <x-input-field type="date" name="dob" label="Tanggal Lahir" placeholder="dd/mm/yyyy"
                                                   :value="old('dob')"/> {{-- Agama --}}
                                    <x-input-field type="text" name="religion" label="Agama" placeholder="Agama" :value="old('religion')"/>
                                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
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


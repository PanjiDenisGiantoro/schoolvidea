<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Title meta --}}
    @include('partials.title-meta', ['subTitle' => 'Login Sekolah'])

    {{-- CSS --}}
    @include('partials.head-css')
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
                                <h3 class="fw-bold text-dark fs-20">Login Sistem Sekolah</h3>
                                <p class="text-muted mt-1 mb-4">
                                    Silakan masukkan Email dan password untuk masuk ke sistem akademik.
                                </p>
                            </div>

                            {{-- Form Login --}}
                            <div class="p-3">
                                <form action="" method="POST" class="authentication-form">
                                    @csrf

                                    <div class="mb-4">
                                        <label class="form-label" for="kodeSekolah">Email</label>
                                        <div class="position-relative w-100">
                                            <input type="text"
                                                   class="form-control form-control-lg rounded"
                                                   id="kodeSekolah"
                                                   name="kode_sekolah"
                                                   value="{{ old('kode_sekolah') }}"
                                                   placeholder="Masukkan Kode Sekolah"
                                                   required>
                                            <p class="text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2 mb-0">
                                                <i class="bx bx-building fs-20 mt-1 text-muted"></i>
                                            </p>
                                        </div>
                                        @error('kode_sekolah')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label" for="UserPass">Password</label>
                                        <div class="position-relative w-100">
                                            <input type="password"
                                                   class="form-control form-control-lg rounded"
                                                   id="UserPass"
                                                   name="password"
                                                   placeholder="Masukkan Password"
                                                   required>
                                            <button type="button"
                                                    class="btn text-muted p-0 position-absolute end-0 top-50 border-0 fs-4 translate-middle-y me-2">
                                                <i class="bx bx-show fs-20 mt-1 text-muted"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="remember" id="checkbox-signin">
                                            <label class="form-check-label" for="checkbox-signin">Ingat saya</label>
                                        </div>
                                    </div>

                                    <div class="text-center d-grid">
                                        <button class="btn btn-primary d-flex align-items-center justify-content-center gap-1 fw-medium" type="submit">
                                            <i class='bx bx-log-in fs-18'></i> Masuk
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- Footer text --}}
                            <p class="text-muted text-center mt-4 mb-0">
                                &copy; {{ date('Y') }} Sistem Akademik Sekolah
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

</body>
</html>

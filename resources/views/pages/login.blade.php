<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Title meta --}}
    @include('partials.title-meta', ['subTitle' => 'Login Sekolah'])

    {{-- CSS --}}
    @include('partials.head-css')
</head>

<body class="authentication-bg">

    <div class="account-pages pt-sm-5 pb-sm-5 pb-4 pt-2">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-5">
                    <div class="card auth-card shadow">
                        <div class="card-body">
                            <div class="p-3">

                                <div class="auth-logo mx-auto mb-5 text-center">
                                    <a href="{{ url('/') }}" class="logo-dark">
                                        <img src="{{ asset('assets/images/videa.png') }}" height="100"
                                            alt="logo dark">
                                    </a>

                                    <a href="{{ url('/') }}" class="logo-light">
                                        <img src="{{ asset('assets/images/videa.png') }}" height="100"
                                            alt="logo light">
                                    </a>
                                </div>

                                {{-- Heading --}}
                                <div class="text-center">
                                    <h3 class="fw-bold text-dark fs-20">Videa School Payment System</h3>
                                    <p class="text-muted mb-4 mt-1">
                                        Masukkan Email & Password Untuk Login SPS
                                    </p>
                                </div>

                                {{-- Form Login --}}
                                <div class="p-3">
                                    <form action="{{ route('login.process') }}" method="POST"
                                        class="authentication-form">
                                        @csrf

                                        <x-input-field type="text" name="email" label="Email"
                                            placeholder="Masukkan email" icon="bx bx-envelope" />
                                        <x-input-field name="password" label="Password" type="password"
                                            icon="bx bx-lock" />
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="remember"
                                                    id="checkbox-signin">
                                                <label class="form-check-label" for="checkbox-signin">Ingat saya</label>
                                            </div>
                                        </div>

                                        <div class="d-grid text-center">
                                            <button
                                                class="btn btn-primary d-flex align-items-center justify-content-center fw-medium gap-1"
                                                type="submit">
                                                <i class='bx bx-log-in fs-18'></i> Masuk
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                {{-- Footer text --}}
                                <p class="text-muted mb-0 mt-4 text-center">
                                    &copy; {{ date('Y') }} VideaClass by PT. Inovasi Dalam Negeri - All Rights Reserveds

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

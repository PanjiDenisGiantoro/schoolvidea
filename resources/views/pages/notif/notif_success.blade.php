<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Title meta --}}
    @include('partials.title-meta', ['subTitle' => 'Pendaftaran Portal VideaClass'])

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
                                <h3 class="fw-bold text-dark fs-20">Pendaftaran Berhasil</h3>
                                <p class="text-muted mt-1 mb-4">
                                    Terima kasih telah mendaftar. Kami telah mengirimkan informasi login Anda melalui email.
                                </p>
                            </div>

                            {{-- Success Message --}}
                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            {{-- Lanjutkan Login Button --}}
                            <div class="text-center d-grid">
                                <a href="{{ url('login') }}" class="btn btn-primary">Lanjutkan untuk Login</a>
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

</body>
</html>

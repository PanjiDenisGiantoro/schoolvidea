<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Include title meta --}}
    @include('partials.title-meta', ['subTitle' => 'Input Kode Sekolah'])
        <link rel="stylesheet" href="{{ asset("assets/css/login.css") }}" />
    {{-- Include CSS --}}
    @include('partials.head-css')
</head>

<body class="authentication-bg">

<body class="authentication-bg">

<div class="auth-wrapper">

    <!-- LEFT - ILLUSTRATION -->
    <div class="auth-left">
        <img src="{{ asset('assets/images/1.png') }}" alt="School Illustration">
    </div>

    <!-- RIGHT - FORM -->
    <div class="auth-right">
        <div class="auth-form-container">

            <div class="card auth-card" style="border-radius: 40px; padding: 20px">
                <div class="card-body p-4">

                    <!-- Logo -->
                    <div class="auth-logo text-center mb-4">
                        <img src="{{ asset('assets/images/videa.png') }}" height="90">
                    </div>

                    <!-- Alert -->
                    @if(session('success'))
                        <div class="alert alert-success text-center mb-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger text-center mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Heading -->
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark fs-20">
                            Masukkan Kode Sekolah
                        </h3>
                        <p class="text-muted">
                            Silakan input kode sekolah Anda untuk melanjutkan
                        </p>
                    </div>

                    <!-- Form -->
                    <form action="{{ url('portalpost') }}" method="POST"
                          class="authentication-form">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Kode Sekolah</label>
                            <input type="text"
                                   name="kode_sekolah"
                                   class="form-control"
                                   placeholder="Masukkan kode sekolah"
                                   required>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary fw-medium" type="submit">
                                Lanjutkan
                            </button>
                        </div>
                    </form>

                    <p class="text-muted mt-4 mb-0 text-center small">
                        &copy; {{ date('Y') }} VideaClass by PT. Inovasi Dalam Negeri
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

@include('partials.vendor-scripts')
</body>

</html>

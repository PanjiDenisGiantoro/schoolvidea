<!DOCTYPE html>
<html lang="en">

<head>
    {{-- Include title meta --}}
    @include('partials.title-meta', ['subTitle' => 'Input Kode Sekolah'])

    {{-- Include CSS --}}
    @include('partials.head-css')
</head>

<body class="authentication-bg">

<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5">
                <div class="card auth-card">
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

                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark fs-20">Masukkan Kode Sekolah</h3>
                                <p class="text-muted mt-1">Silakan input kode sekolah Anda untuk melanjutkan.</p>
                            </div>

                            <div class="p-3">
                                <form action="" method="POST" class="authentication-form">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="kode-sekolah">Kode Sekolah</label>
                                        <input type="text" id="kode-sekolah" name="kode_sekolah"
                                               class="form-control"
                                               placeholder="Masukkan kode sekolah" required>
                                    </div>
                                    <div class="mb-1 text-center d-grid">
                                        <button class="btn btn-primary" type="submit">Lanjutkan</button>
                                    </div>
                                </form>
                            </div>

                        </div> <!-- end inner div -->
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
</div>

{{-- Include vendor scripts --}}
@include('partials.vendor-scripts')

</body>
</html>

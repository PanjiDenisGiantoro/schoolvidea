<!DOCTYPE html>
<html lang="en">
    <head>
        {{-- Title meta --}}
        @include("partials.title-meta", ["subTitle" => "Login Sekolah"])
        <link rel="stylesheet" href="{{ asset("assets/css/login.css") }}" />
        {{-- CSS --}}
        @include("partials.head-css")
    </head>

    <body class="authentication-bg">
        <div class="auth-wrapper">
            <!-- LEFT - ILLUSTRATION -->
            <div class="auth-left">
                <img
                    src="{{ asset("assets/images/1.png") }}"
                    alt="Login Illustration"
                />
            </div>

            <!-- RIGHT - LOGIN FORM -->
            <div class="auth-right">
                <div class="auth-form-container">
                    <div class="card auth-card" style="border-radius: 40px; padding: 20px">
                        <div class="card-body p-4" > 


                            <!-- Logo -->
                            <div class="auth-logo text-center mb-4">
                                <img
                                    src="{{ asset("assets/images/videa.png") }}"
                                    height="90"
                                />
                            </div>

                            <!-- Heading -->
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark fs-20">
                                    Videa School Payment System
                                </h3>
                                <p class="text-muted">
                                    Masukkan Email & Password Untuk Login SPS
                                </p>
                            </div>

                            <!-- Form -->
                            <form
                                action="{{ route("login.process") }}"
                                method="POST"
                                class="authentication-form"
                            >
                                @csrf

                                <x-input-field
                                    type="text"
                                    name="email"
                                    label="Email"
                                    placeholder="Masukkan email"
                                    icon="bx bx-envelope"
                                />

                                <x-input-field
                                    name="password"
                                    label="Password"
                                    type="password"
                                    icon="bx bx-lock"
                                />

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            id="remember"
                                            name="remember"
                                        />
                                        <label
                                            class="form-check-label"
                                            for="remember"
                                        >
                                            Ingat saya
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button
                                        class="btn btn-primary fw-medium"
                                        type="submit"
                                    >
                                        <i class="bx bx-log-in"></i>
                                        Masuk
                                    </button>
                                </div>
                            </form>

                            <p class="text-muted mt-4 mb-0 text-center small">
                                &copy; {{ date("Y") }} VideaClass by PT.
                                Inovasi Dalam Negeri
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include("partials.vendor-scripts")
    </body>
</html>

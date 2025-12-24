<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <title>Login Merchant</title>

        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <link rel="stylesheet" href="{{ asset("assets/css/merchant.css") }}" />
        @include("partials.head-css")

        {{-- SweetAlert --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <div class="hero">
            <div class="login-box">
                <h3>
                    LOGIN
                    <br />
                    MERCHANT
                </h3>

                <form id="loginForm">
                    @csrf

                    <input
                        type="text"
                        name="no_hp"
                        placeholder="Nomor Telepon / HP"
                    />
                    <small class="text-danger" id="error-no_hp"></small>

                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                    />
                    <small class="text-danger" id="error-password"></small>

                    <button type="submit">Login</button>
                    <a href="{{ url("/merchant") }}">Kembali</a>
                </form>
            </div>
        </div>

        {{-- SweetAlert --}}
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document
                .getElementById('loginForm')
                .addEventListener('submit', function (e) {
                    e.preventDefault();

                    const form = this;
                    const formData = new FormData(form);

                    // reset error
                    document.getElementById('error-no_hp').innerText = '';
                    document.getElementById('error-password').innerText = '';

                    fetch('{{ route("merchant.login") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            Accept: 'application/json',
                        },
                        body: formData,
                    })
                        .then(async (res) => {
                            const data = await res.json();

                            if (!res.ok) throw data;

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => {
                                window.location.href = data.redirect;
                            });
                        })
                        .catch((err) => {
                            if (err.errors) {
                                if (err.errors.no_hp) {
                                    document.getElementById(
                                        'error-no_hp',
                                    ).innerText = err.errors.no_hp[0];
                                }
                                if (err.errors.password) {
                                    document.getElementById(
                                        'error-password',
                                    ).innerText = err.errors.password[0];
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal Login',
                                    text: err.message ?? 'Terjadi kesalahan',
                                });
                            }
                        });
                });
        </script>
    </body>
</html>

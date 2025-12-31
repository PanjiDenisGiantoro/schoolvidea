<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>@yield("title")</title>
        @include("partials.head-css")
        <link rel="stylesheet" href="{{ asset("assets/css/merchant.css") }}" />
        <link rel="stylesheet" href="{{ asset("assets/css/tabungan.css") }}" />
        <link rel="stylesheet" href="{{ asset("assets/css/style.css") }}" />
        <link
            href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
            rel="stylesheet"
        />
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        />
        <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/dropzone@5/dist/min/dropzone.min.css"
        />

        @stack("styles")
    </head>
    <body>
        <div class="dash">
            <div class="dashboard-container">
                @include("pages.ekantin.dashboard_merchant.partials.sidebar")

                <div class="main-wrapper">
                    @include("pages.ekantin.dashboard_merchant.partials.topbar")

                    <main class="content-area">
                        @yield("content")
                    </main>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/dropzone@5/dist/min/dropzone.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const logoutForm = document.getElementById('logoutForm');

                logoutForm.addEventListener('submit', function (e) {
                    e.preventDefault(); // cegah logout langsung

                    Swal.fire({
                        title: 'Yakin ingin logout?',
                        text: 'Sesi Anda akan diakhiri.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            logoutForm.submit();
                        }
                    });
                });
            });
        </script>
        @stack("scripts")
    </body>
</html>

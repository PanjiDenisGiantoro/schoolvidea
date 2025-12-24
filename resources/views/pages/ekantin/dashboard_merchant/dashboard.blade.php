@extends("layouts.merchant")

@section("title", "Dashboard")

@section("content")
    <div class="welcome-section">
        <h1>Dashboard</h1>
    </div>
    <div class="row g-3 mb-4 mt-4">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all"
            >
                <div class="card-body position-relative">
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <div>
                            <p
                                class="text-muted fw-500 mb-1 text-uppercase"
                                style="font-size: 12px; letter-spacing: 0.5px"
                            >
                                Saldo
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp
                                {{ number_format(auth("merchant")->user()->saldo_aktif ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-wallet-alt text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-up-arrow-alt text-success"></i>
                        Total Saldo Saat Ini
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all"
            >
                <div class="card-body position-relative">
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <div>
                            <p
                                class="text-muted fw-500 mb-1 text-uppercase"
                                style="font-size: 12px; letter-spacing: 0.5px"
                            >
                                Total Produk
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp
                                {{ number_format($total_setoran ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-wallet-alt text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-up-arrow-alt text-success"></i>
                        Total Produk Aktif
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div
                class="card border-0 rounded-4 overflow-hidden stat-card stat-card-green shadow-sm h-100 transition-all"
            >
                <div class="card-body position-relative">
                    <div
                        class="d-flex justify-content-between align-items-start"
                    >
                        <div>
                            <p
                                class="text-muted fw-500 mb-1 text-uppercase"
                                style="font-size: 12px; letter-spacing: 0.5px"
                            >
                                Total Transaksi
                            </p>
                            <h3 class="fw-bold text-success mb-0 text-absolute">
                                Rp
                                {{ number_format($total_setoran ?? 0, 0, ",", ".") }}
                            </h3>
                        </div>
                        <div
                            class="stat-icon bg-success bg-opacity-10 rounded-3 p-3"
                        >
                            <i
                                class="bx bx-wallet-alt text-success"
                                style="font-size: 24px"
                            ></i>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-up-arrow-alt text-success"></i>
                        Tabungan masuk
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-4 mt-4">
        <div class="col-md-8 col-sm-12">
            <div class="card border-0 rounded-4 shadow-sm h-100 transition-all">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3 text-center">
                        Pendapatan Per Hari
                        ({{ now()->translatedFormat("F Y") }})
                    </h6>

                    <canvas id="pendapatanChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="card border-0 rounded-4 shadow-sm h-100 transition-all">
                <div class="card-body text-center">
                    <div
                        id="qrcode"
                        class="d-flex justify-content-center p-3 mb-3 border"
                    ></div>
                    <input type="hidden" id="qrcode-text" name="qrcode_text" />
                    <small class="text-muted d-block mb-3">
                        QR akan berubah otomatis sesuai kode merchant & unit
                    </small>
                    <button
                        type="button"
                        id="downloadQrBtn"
                        class="btn btn-primary"
                        style="display: none"
                    >
                        <i class="bx bx-download"></i>
                        Download QR
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qr-code-styling@1.6.0/lib/qr-code-styling.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrContainer = document.getElementById('qrcode');
        const downloadBtn = document.getElementById('downloadQrBtn');

        // ambil data merchant dari backend
        const qrData = '{{ auth("merchant")->user()->kode_merchant ?? "" }}';

        const defaultLogo =
            '{{
                auth("merchant")->user()->unit && auth("merchant")->user()->unit->image
                    ? asset(auth("merchant")->user()->unit->image)
                    : asset("images/default-logo.png")
            }}';
        if (!qrData) {
            qrContainer.innerHTML =
                '<small class="text-muted">Kode merchant belum tersedia</small>';
            return;
        }

        const qrCode = new QRCodeStyling({
            width: 220,
            height: 220,
            data: qrData,
            image: defaultLogo,
            dotsOptions: {
                color: '#000',
                type: 'rounded',
            },
            backgroundOptions: {
                color: '#fff',
            },
            imageOptions: {
                crossOrigin: 'anonymous',
                margin: 6,
                imageSize: 0.4,
                hideBackgroundDots: true,
                imageCornerRadius: 100,
            },
        });

        qrContainer.innerHTML = '';
        qrCode.append(qrContainer);

        // tampilkan tombol download
        downloadBtn.style.display = 'inline-block';

        downloadBtn.addEventListener('click', function () {
            qrCode.download({
                name: 'qr-merchant-' + qrData,
                extension: 'png',
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($pendapatan->map(
            fn ($d) => \Carbon\Carbon::parse($d->tanggal)->format("d"),
        ));

        const dataPendapatan = @json($pendapatan->pluck("total"));

        const ctx = document.getElementById('pendapatanChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: dataPendapatan,
                        backgroundColor: '#198754',
                        borderRadius: 6,
                        barThickness: 20,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return (
                                    'Rp ' +
                                    new Intl.NumberFormat('id-ID').format(
                                        ctx.raw,
                                    )
                                );
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal',
                        },
                    },
                    y: {
                        ticks: {
                            callback: function (value) {
                                return (
                                    'Rp ' +
                                    new Intl.NumberFormat('id-ID').format(value)
                                );
                            },
                        },
                    },
                },
            },
        });
    });
</script>

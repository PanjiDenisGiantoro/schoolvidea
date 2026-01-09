<header class="topbar d-flex">
    <!-- Sidebar Logo -->
    @php
        $user = auth()->user();

        $logoUrl = asset("assets/images/videa.png");
        $unitName = "Videa Class";

        if ($user && $user->unit_id) {
            $unit = \App\Models\Unit::find($user->unit_id);
            if ($unit) {
                $unitName = $unit->nama_unit ?? "Videa Class";
                if (! empty($unit->image)) {
                    $logoUrl = asset($unit->image);
                }
            }
        }
    @endphp

    <!-- LOGO + UNIT -->
    <div class="logo-box">
        <a
            href="{{ url("dashboard") }}"
            class="logo-link d-flex align-items-center gap-2"
        >
            <img
                src="{{ $logoUrl }}"
                alt="Logo"
                width="42"
                height="42"
                class="logo-img"
            />
            <span
                class="logo-text unit-active-text fw-semibold text-white"
                title="{{ $unitName }}"
            >
                {{ $unitName }}
            </span>
        </a>
    </div>

    <!-- NAVBAR -->
    <div class="container-fluid px-3">
        <div class="navbar-header d-flex align-items-center w-100">
            <!-- TOGGLE -->
            <button
                type="button"
                class="btn btn-link d-flex button-sm-hover button-toggle-menu"
                aria-label="Show Full Sidebar"
            >
                <i class="ri-menu-2-line text-white fs-20"></i>
            </button>

            <!-- SEARCH -->
            <form class="app-search d-none d-md-block ms-3">
                <div class="position-relative">
                    <input
                        type="search"
                        class="form-control"
                        placeholder="Pencarian Cepat..."
                        autocomplete="off"
                    />
                    <i class="ri-search-line search-widget-icon"></i>
                </div>
            </form>

            <!-- RIGHT MENU -->
            <div class="d-flex align-items-center ms-auto gap-2">
                <!-- Theme Color (Light/Dark) -->
                <div class="topbar-item">
                    <button
                        type="button"
                        class="topbar-button"
                        id="light-dark-mode"
                    >
                        <i
                            class="ri-moon-line fs-20 light-mode align-middle"
                        ></i>
                        <i class="ri-sun-line fs-20 dark-mode align-middle"></i>
                    </button>
                </div>

                <!-- Notification -->
                <!-- Notification -->
<div class="dropdown topbar-item">
    <button type="button" class="topbar-button position-relative" data-bs-toggle="dropdown">
        <i class="ri-notification-3-line"></i>
        @if($totalPending > 0)
            <span class="topbar-badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle">
                {{ $totalPending }}
            </span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-lg dropdown-menu-end pt-0">
        <div class="border-top-0 border-start-0 border-end-0 border border-dashed p-3">
            <h6 class="fs-16 fw-semibold m-0">Notifikasi Transaksi</h6>
        </div>

        <div data-simplebar style="max-height: 280px;">

            {{-- Pending Tabungan --}}
            @if($pendingTabungan->isNotEmpty())
                <div class="px-2 py-1 text-muted fw-bold">Pending Tabungan</div>
                @foreach($pendingTabungan as $trx)
                    <a href="{{ route('keuangan.transaksi.show', $trx->id) }}" class="dropdown-item border-bottom py-2">
                        <strong>{{ $trx->code_pembayaran }}</strong> - Rp {{ number_format($trx->jumlah,0,',','.') }}
                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->translatedFormat('d F Y') }}</small>
                    </a>
                @endforeach
            @endif

            {{-- Pending Tagihan --}}
            @if($pendingTagihan->isNotEmpty())
                <div class="px-2 py-1 text-muted fw-bold">Pending Tagihan</div>
                @foreach($pendingTagihan as $trx)
                    <a href="{{ route('keuangan.transaksi.show', $trx->id) }}" class="dropdown-item border-bottom py-2">
                        <strong>{{ $trx->code_pembayaran }}</strong> - Rp {{ number_format($trx->jumlah,0,',','.') }}
                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->translatedFormat('d F Y') }}</small>
                    </a>
                @endforeach
            @endif

            @if($totalPending === 0)
                <div class="text-center p-2 text-muted">Tidak ada transaksi pending</div>
            @endif
        </div>

        <div class="p-2 text-center">
            <a href="{{ route('keuangan_transaksi.index') }}" class="text-primary">Lihat Semua Transaksi</a>
        </div>
    </div>
</div>


                <!-- User -->
                <div class="dropdown topbar-item">
                    <a
                        type="button"
                        class="topbar-button p-0"
                        id="page-header-user-dropdown"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <span class="d-flex align-items-center gap-2">
                            <img
                                class="rounded-circle"
                                style="
                                    width: 32px;
                                    height: 32px;
                                    object-fit: cover;
                                "
                                src="{{
                                    optional(Auth::user()->officers)->image
                                        ? asset(Auth::user()->officers->image)
                                        : asset("assets/images/users/avatar-1.jpeg")
                                }}"
                                alt="user-image"
                            />

                            <span class="d-lg-flex flex-column d-none gap-1">
                                <h5
                                    class="fs-13 text-uppercase text-reset fw-bold my-0"
                                >
                                    {{ \Illuminate\Support\Facades\Auth::user()->name ?? "" }}
                                </h5>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a
                            class="dropdown-item"
                            href="{{ route("profile.showupdate") }}"
                        >
                            <i
                                class="bx bx-user-circle fs-18 me-2 align-middle"
                            ></i>
                            <span class="align-middle">My Account</span>
                        </a>

                        <a
                            class="dropdown-item"
                            href="#"
                            onclick="
                                event.preventDefault();
                                document.getElementById('logout-form').submit();
                            "
                        >
                            <i
                                class="bx bx-log-out fs-18 me-2 align-middle"
                            ></i>
                            <span class="align-middle">Logout</span>
                        </a>

                        <form
                            id="logout-form"
                            action="{{ route("logout") }}"
                            method="POST"
                            style="display: none"
                        >
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

@push("styles")
    <style>
        .logo-box {
            padding: 0 16px;
        }

        .logo-img {
            object-fit: contain;
        }

        .unit-active-text {
            max-width: 180px;
            font-size: 15px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Sinkron dengan class */
        body.dark-theme .unit-active-text {
            color: #fff;
        }

        body:not(.dark-theme) .unit-active-text {
            color: #fff;
        }

        /* Default */
        .logo-img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }

        .logo-text {
            color: #fff;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        /* Saat sidebar collapse */

        /* Sembunyikan text saat sidebar collapse */
        html.sidebar-hover .logo-text,
        html.sidebar-collapsed .logo-text,
        body.sidebar-enable .logo-text,
        body.vertical-collapsed .logo-text,
        body.enlarged .logo-text,
        body[data-sidebar-size='sm'] .logo-text {
            display: none !important;
        }
    </style>
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('light-dark-mode');
        const savedTheme = localStorage.getItem('theme');

        if (savedTheme === 'dark') {
            document.body.classList.add('dark-theme');
        }

        toggleBtn.addEventListener('click', function () {
            document.body.classList.toggle('dark-theme');

            localStorage.setItem(
                'theme',
                document.body.classList.contains('dark-theme')
                    ? 'dark'
                    : 'light',
            );
        });
    });
</script>

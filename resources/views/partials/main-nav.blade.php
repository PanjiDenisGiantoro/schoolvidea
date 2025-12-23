<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="index.html" class="logo-dark">
            <img
                src="{{ asset("assets/images/videa.png") }}"
                class="logo-sm"
                alt="logo sm"
            />
            <img
                src="{{ asset("assets/images/videa.png") }}"
                class="logo-lg"
                alt="logo dark"
            />
        </a>

        <a href="index.html" class="logo-light">
            <img
                src="{{ asset("assets/images/videa.png") }}"
                class="logo-sm"
                alt="logo sm"
            />
            <img
                src="{{ asset("assets/images/videa.png") }}"
                class="logo-lg"
                alt="logo light"
            />
        </a>
    </div>

    <div class="h-100" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            @canViewMenu("view_dashboard")
                <li class="menu-item pt-2">
                    <a class="menu-link" href="{{ url("dashboard") }}">
                        <span class="nav-icon">
                            <i class="ri-dashboard-2-line"></i>
                        </span>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            @endcanViewMenu

            @canViewMenu([
                "view_tahun_ajaran",
                "view_role",
                "view_lembagaunit",
                "view_tipe_unit",
                "view_unit",
                "view_positions",
                "view_jurusan",
                "view_officer",
                "view_kelas",
                "view_siswa",
                "view_user",
            ])
                {{-- 'view_data_rekening' --}}
                <li class="menu-item">
                    <a
                        class="menu-link menu-arrow"
                        href="#sidebarMasterData"
                        data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="false"
                        aria-controls="sidebarMasterData"
                    >
                        <span class="nav-icon">
                            <i class="ri-database-2-line"></i>
                        </span>
                        <span class="nav-text">Master Data</span>
                    </a>
                    <div class="collapse" id="sidebarMasterData">
                        <ul class="sub-menu-nav">
                            @hasPermission("view_tahun_ajaran")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("tahun_ajaran") }}"
                                    >
                                        Tahun Ajaran
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_role")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("roles") }}"
                                    >
                                        Role
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_lembagaunit")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("lembagaunit") }}"
                                    >
                                        Lembaga
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_tipe_unit")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("tipe_unit") }}"
                                    >
                                        Tipe Unit
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_unit")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("unit") }}"
                                    >
                                        Unit
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_positions")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("positions") }}"
                                    >
                                        Jabatan
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_jurusan")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("jurusan") }}"
                                    >
                                        Jurusan
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_officer")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("officer") }}"
                                    >
                                        Guru Dan Staff
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_kelas")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("kelas") }}"
                                    >
                                        Kelas
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_siswa")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("siswa") }}"
                                    >
                                        Siswa
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_user")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("akun-user") }}"
                                    >
                                        Akun User
                                    </a>
                                </li>
                            @endhasPermission

                            {{--
                                @hasPermission('view_data_rekening')
                                <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('data-rekening') }}">Data
                                Rekening</a>
                                </li>
                                @endhasPermission
                            --}}
                            <li class="sub-menu-item">
                                <a
                                    class="sub-menu-link"
                                    href="{{ url("data-rekening") }}"
                                >
                                    Data Rekening
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endcanViewMenu

            @canViewMenu(["view_akun", "view_setting_akun", "view_kategoritagihan"])
                <li class="menu-item">
                    <a
                        class="menu-link menu-arrow"
                        href="#sidebarKeuangan"
                        data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="false"
                        aria-controls="sidebarKeuangan"
                    >
                        <span class="nav-icon">
                            <i class="ri-bank-line"></i>
                        </span>
                        <span class="nav-text">Keuangan</span>
                    </a>
                    <div class="collapse" id="sidebarKeuangan">
                        <ul class="sub-menu-nav">
                            @hasPermission("view_akun")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("akun") }}"
                                    >
                                        Set Akun
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_setting_akun")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("setting_akun") }}"
                                    >
                                        Jenis Transaksi
                                    </a>
                                </li>
                            @endhasPermission

                            @hasPermission("view_kategoritagihan")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("kategoritagihan") }}"
                                    >
                                        Kategori Tagihan
                                    </a>
                                </li>
                            @endhasPermission
                        </ul>
                    </div>
                </li>
            @endcanViewMenu

            @canViewMenu(["view_report", "view_tabungan", "view_tagihan", "view_potongan", "view_pembayaran"])
                <li class="menu-item">
                    <a
                        class="menu-link menu-arrow"
                        href="#sidebarTransaksi"
                        data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="false"
                        aria-controls="sidebarTransaksi"
                    >
                        <span class="nav-icon">
                            <i class="ri-exchange-dollar-line"></i>
                        </span>
                        <span class="nav-text">Transaksi</span>
                    </a>
                    <div class="collapse" id="sidebarTransaksi">
                        <ul class="sub-menu-nav">
                            @hasPermission("view_tabungan")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-link"
                                        href="{{ url("tabungan") }}"
                                    >
                                        Tabungan
                                    </a>
                                </li>
                            @endhasPermission

                            @canViewMenu(["view_tagihan", "view_potongan"])
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-item menu-link menu-arrow"
                                        href="#sidebarTagihan"
                                        data-bs-toggle="collapse"
                                        role="button"
                                        aria-expanded="false"
                                        aria-controls="sidebarTagihan"
                                    >
                                        Tagihan
                                    </a>
                                    <div class="collapse" id="sidebarTagihan">
                                        <ul class="sub-menu-nav">
                                            @hasPermission("view_tagihan")
                                                <li class="sub-menu-item">
                                                    <a
                                                        class="sub-menu-link"
                                                        href="{{ url("tagihan") }}"
                                                    >
                                                        Kelola Data
                                                    </a>
                                                </li>
                                            @endhasPermission

                                            @hasPermission("view_potongan")
                                                <li class="sub-menu-item">
                                                    <a
                                                        class="sub-menu-link"
                                                        href="{{ url("potongan") }}"
                                                    >
                                                        Potongan
                                                    </a>
                                                </li>
                                            @endhasPermission
                                        </ul>
                                    </div>
                                </li>
                            @endcanViewMenu

                            @hasPermission("view_pembayaran")
                                <li class="sub-menu-item">
                                    <a
                                        class="sub-menu-item menu-link menu-arrow"
                                        href="#sidebarPembayaran"
                                        data-bs-toggle="collapse"
                                        role="button"
                                        aria-expanded="false"
                                        aria-controls="sidebarPembayaran"
                                    >
                                        Pembayaran
                                    </a>
                                    <div
                                        class="collapse"
                                        id="sidebarPembayaran"
                                    >
                                        <ul class="sub-menu-nav">
                                            <li class="sub-menu-item">
                                                <a
                                                    class="sub-menu-link"
                                                    href="{{ url("pembayaran") }}"
                                                >
                                                    Proses Pembayaran
                                                </a>
                                            </li>
                                            @hasPermission("view_report")
                                                <li class="sub-menu-item">
                                                    <a
                                                        class="sub-menu-link"
                                                        href="{{ url("keuangan-transaksi") }}"
                                                    >
                                                        Daftar Transaksi
                                                    </a>
                                                </li>
                                            @endhasPermission
                                        </ul>
                                    </div>
                                </li>
                            @endhasPermission
                        </ul>
                    </div>
                </li>
            @endcanViewMenu

            <li class="menu-item">
                <a
                    class="menu-link menu-arrow"
                    href="#sidebarPenggajian"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarPenggajian"
                >
                    <span class="nav-icon">
                        <i class="ri-currency-line"></i>
                    </span>
                    <span class="nav-text">Penggajian</span>
                </a>
                <div class="collapse" id="sidebarPenggajian">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("payroll-components") }}"
                            >
                                Komponen Gaji
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("payroll-deductions") }}"
                            >
                                Potongan
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("payroll-setting") }}"
                            >
                                Setting Gaji
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("payroll-payment") }}"
                            >
                                Pembayaran Gaji
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a
                    class="menu-link menu-arrow"
                    href="#sidebarEkantin"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarEkantin"
                >
                    <span class="nav-icon">
                        <i class="ri-store-3-line"></i>
                    </span>
                    <span class="nav-text">E-Kantin</span>
                </a>
                <div class="collapse" id="sidebarEkantin">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("merchant") }}"
                            >
                                Data Merchant
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("merchant-transaction") }}"
                            >
                                Data Transaksi
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("merchant-withdrawal") }}"
                            >
                                Penarikan Dana
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-link"
                                href="{{ url("login-merchant") }}"
                            >
                                Akses Halaman
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a
                                class="sub-menu-item menu-link menu-arrow"
                                href="#sidebarSetEkantin"
                                data-bs-toggle="collapse"
                                role="button"
                                aria-expanded="false"
                                aria-controls="sidebarSetEkantin"
                            >
                                Setting
                            </a>
                            <div class="collapse" id="sidebarSetEkantin">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item">
                                        <a
                                            class="sub-menu-link"
                                            href="{{ url("#") }}"
                                        >
                                            Set. Umum
                                        </a>
                                    </li>
                                    <li class="sub-menu-item">
                                        <a
                                            class="sub-menu-link"
                                            href="{{ url("#") }}"
                                        >
                                            Set. Transaksi
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a
                    class="menu-link menu-arrow"
                    href="#sidebarDonasi"
                    data-bs-toggle="collapse"
                    role="button"
                    aria-expanded="false"
                    aria-controls="sidebarDonasi"
                >
                    <span class="nav-icon">
                        <i class="ri-hand-coin-line"></i>
                    </span>
                    <span class="nav-text">Donasi</span>
                </a>
                <div class="collapse" id="sidebarDonasi">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ url("#") }}">
                                Data Lembaga
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ url("#") }}">
                                Riwayat Donasi
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ url("#") }}">
                                Setting Donasi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            @hasPermission("view_report")
                <li class="menu-item">
                    <a
                        class="menu-link menu-arrow"
                        href="#sidebarReport"
                        data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="false"
                        aria-controls="sidebarReport"
                    >
                        <span class="nav-icon">
                            <i class="ri-file-chart-line"></i>
                        </span>
                        <span class="nav-text">Reports</span>
                    </a>
                    <div class="collapse" id="sidebarReport">
                        <ul class="sub-menu-nav">
                            <li class="sub-menu-item">
                                <a
                                    class="sub-menu-link"
                                    href="{{ url("report/jurnal") }}"
                                >
                                    Jurnal
                                </a>
                            </li>
                            <li class="sub-menu-item">
                                <a
                                    class="sub-menu-link"
                                    href="{{ url("report/buku_besar") }}"
                                >
                                    Buku Besar
                                </a>
                            </li>
                            {{-- <li class="sub-menu-item"><a class="sub-menu-link" --}}
                            {{-- href="{{ url('report/neraca_saldo') }}">Neraca Saldo</a></li> --}}
                            {{-- <li class="sub-menu-item"><a class="sub-menu-link" --}}
                            {{-- href="{{ url('report/neraca') }}">Neraca</a></li> --}}
                            {{-- <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('report/labarugi') }}">Laba --}}
                            {{-- Rugi</a></li> --}}
                            <li class="sub-menu-item">
                                <a
                                    class="sub-menu-link"
                                    href="{{ url("tabungan/report-all") }}"
                                >
                                    Tabungan
                                </a>
                            </li>
                            <li class="sub-menu-item">
                                <a
                                    class="sub-menu-link"
                                    href="{{ url("report/tagihan") }}"
                                >
                                    Tagihan
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endhasPermission

            @hasPermission("view_migrasi")
                <li class="menu-item">
                    <a class="menu-link" href="{{ url("migrasi/import") }}">
                        <span class="nav-icon">
                            <i class="ri-database-fill"></i>
                        </span>
                        <span class="nav-text">Migrasi Data</span>
                    </a>
                </li>
            @endhasPermission

            <li class="menu-item">
                <a class="menu-link" href="{{ url("#") }}">
                    <span class="nav-icon">
                        <i class="ri-hard-drive-3-line"></i>
                    </span>
                    <span class="nav-text">Backup Data</span>
                </a>
            </li>
            @hasPermission("view_activity")
                <li class="menu-item">
                    <a class="menu-link" href="{{ url("activity") }}">
                        <span class="nav-icon">
                            <i class="ri-file-history-line"></i>
                        </span>
                        <span class="nav-text">Logs Aktivitas</span>
                    </a>
                </li>
            @endhasPermission
        </ul>
    </div>
</div>

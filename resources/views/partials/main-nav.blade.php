<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="index.html" class="logo-dark">
            <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="assets/images/logo-dark.png" class="logo-lg" alt="logo dark">
        </a>

        <a href="index.html" class="logo-light">
            <img src="assets/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="assets/images/logo-white.png" class="logo-lg" alt="logo light">
        </a>
    </div>

    <div class="h-100" data-simplebar>

        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-item pt-2">
                <a class="menu-link" href="{{ url('dashboard') }}">
                         <span class="nav-icon">
                              <i class="ri-dashboard-2-line"></i>
                         </span>
                    <span class="nav-text"> Dashboard </span>
                    {{--                    <span class="badge bg-success badge-pill text-end">9+</span>--}}
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarMasterData" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarMasterData">
        <span class="nav-icon">
            <i class="ri-database-2-line"></i>
        </span>
                    <span class="nav-text"> Master Data </span>
                </a>
                <div class="collapse" id="sidebarMasterData">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tahun_ajaran') }}">Tahun Ajaran</a>
                        </li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('roles') }}">Role</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('lembagaunit') }}"> Lembaga</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tipe_unit') }}">Tipe Unit</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('unit') }}">Unit</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('positions') }}">Jabatan</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('jurusan') }}">Jurusan</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('officer') }}">Guru Dan Staff</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('kelas') }}">Kelas</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('siswa') }}">Siswa</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Akun User</a></li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarKeuangan" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarKeuangan">
                    <span class="nav-icon">
                        <i class="ri-bank-line"></i>
                    </span>
                    <span class="nav-text"> Keuangan </span>
                </a>
                <div class="collapse" id="sidebarKeuangan">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('akun') }}">Set Akun</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('setting_akun') }}">Jenis Transaksi</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('kategoritagihan') }}">Kategori Tagihan</a></li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarTransaksi" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarTransaksi">
                    <span class="nav-icon">
                        <i class="ri-exchange-dollar-line"></i>
                    </span>    
                    <span class="nav-text"> Transaksi </span>
                </a>
                <div class="collapse" id="sidebarTransaksi">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('orders.html') }}">Daftar Transaksi</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tabungan') }}">Tabungan</a></li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-item menu-link menu-arrow" href="#sidebarTagihan" data-bs-toggle="collapse" role="button"
                                            aria-expanded="false" aria-controls="sidebarTagihan">
                                Tagihan
                            </a>
                            <div class="collapse" id="sidebarTagihan">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('potongan') }}">Potongan</a></li>
                                    <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tagihan') }}">Kelola Data</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-item menu-link menu-arrow" href="#sidebarPembayaran" data-bs-toggle="collapse" role="button"
                                            aria-expanded="false" aria-controls="sidebarPembayaran">
                                Pembayaran
                            </a>
                            <div class="collapse" id="sidebarPembayaran">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('pembayaran') }}">Proses Pembayaran</a></li>
                                    <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('') }}">Pembayaran Online</a></li>
                                </ul>
                            </div>
                        </li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('') }}">Transaksi Lainnya</a></li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarPenggajian" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarPenggajian">
                    <span class="nav-icon">
                        <i class="ri-currency-line"></i>
                    </span>
                    <span class="nav-text"> Penggajian </span>
                </a>
                <div class="collapse" id="sidebarPenggajian">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Setting</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Transaksi</a></li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarEkantin" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarEkantin">
                    <span class="nav-icon">
                        <i class="ri-store-3-line"></i>
                    </span>
                    <span class="nav-text"> E-Kantin </span>
                </a>
                <div class="collapse" id="sidebarEkantin">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Data Merchant</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Data Transaksi</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Penarikan Dana</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Akses Halaman</a></li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-item menu-link menu-arrow" href="#sidebarSetEkantin" data-bs-toggle="collapse" role="button"
                                            aria-expanded="false" aria-controls="sidebarSetEkantin">
                                Setting
                            </a>
                            <div class="collapse" id="sidebarSetEkantin">
                                <ul class="sub-menu-nav">
                                    <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Set. Umum</a></li>
                                    <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Set. Transaksi</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarDonasi" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarDonasi">
                    <span class="nav-icon">
                        <i class="ri-hand-coin-line"></i>
                    </span>
                    <span class="nav-text"> Donasi </span>
                </a>
                <div class="collapse" id="sidebarDonasi">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Data Lembaga</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Riwayat Donasi</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('#') }}">Setting Donasi</a></li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarReport" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarReport">
        <span class="nav-icon">
            <i class="ri-file-chart-line"></i>
        </span>
                    <span class="nav-text"> Reports </span>
                </a>
                <div class="collapse" id="sidebarReport">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('report/jurnal') }}">Jurnal</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('report/buku_besar') }}">Buku Besar</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('report/neraca_saldo') }}">Neraca Saldo</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('report/neraca') }}">Neraca</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('report/labarugi') }}">Laba Rugi</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tabungan/report-all') }}">Tabungan</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('report/tagihan') }}">Tagihan</a></li>
                    </ul>
                </div>
            </li>
            {{--            <li class="menu-item">--}}
            {{--                <a class="menu-link" href="reports.html">--}}
            {{--                         <span class="nav-icon">--}}
            {{--                              <i class="ri-coin-line"></i>--}}
            {{--                         </span>--}}
            {{--                    <span class="nav-text"> Pengeluaran </span>--}}
            {{--                </a>--}}
            {{--            </li>--}}
            <li class="menu-item">
                <a class="menu-link" href="reports.html">
                         <span class="nav-icon">
                              <i class="ri-database-fill"></i>
                         </span>
                    <span class="nav-text"> Migrasi Data </span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link" href="reports.html">
                         <span class="nav-icon">
                            <i class="ri-file-history-line"></i>
                         </span>
                    <span class="nav-text"> Logs Aktivitas  </span>
                </a>
            </li>

        </ul>
    </div>
</div>

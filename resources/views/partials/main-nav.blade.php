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
                <a class="menu-link" href="index.html">
                         <span class="nav-icon">
                              <i class="ri-dashboard-2-line"></i>
                         </span>
                    <span class="nav-text"> Dashboard </span>
                    <span class="badge bg-success badge-pill text-end">9+</span>
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

                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('officer') }}">Petugas</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('lembagaunit') }}"> Lembaga
                                Unit</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('unit') }}">Unit</a></li>

                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tahun_ajaran') }}">Tahun</a>
                        </li>

                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('kelas') }}">Kelas</a></li>

                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('siswa') }}">Siswa</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('jurusan') }}">Jurusan</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('akun') }}">Akun</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('setting_akun') }}">Setting
                                Akun</a></li>

                        <li class="sub-menu-item"><a class="sub-menu-link"
                                                     href="{{ url('kategoritagihan') }}">Kategori</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('roles') }}">Role</a></li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link" href="orders.html">
                         <span class="nav-icon">
                              <i class="ri-shopping-cart-line"></i>
                         </span>
                    <span class="nav-text"> Transaksi </span>
                </a>
            </li>


            <li class="menu-item">
                <a class="menu-link" href="{{ url('tabungan') }}">
                         <span class="nav-icon">
                              <i class="ri-store-3-line"></i>
                         </span>
                    <span class="nav-text">Tabungan</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarTagihan" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarTagihan">
        <span class="nav-icon">
            <i class="ri-database-2-line"></i>
        </span>
                    <span class="nav-text"> Tagihan </span>
                </a>
                <div class="collapse" id="sidebarTagihan">
                    <ul class="sub-menu-nav">

                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('potongan') }}">Potongan</a></li>
                        <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tagihan') }}"> Kelola Data
                                </a></li>

                    </ul>
                </div>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="settings.html">
                         <span class="nav-icon">
                              <i class="ri-store-3-line"></i>
                         </span>
                    <span class="nav-text">E-Kantin</span>
                </a>
            </li>


            <li class="menu-item">
                <a class="menu-link" href="{{ url('pembayaran') }}">
                         <span class="nav-icon">
                              <i class="ri-mac-line"></i>
                         </span>
                    <span class="nav-text"> Pembayaran </span>
                </a>
            </li>


            <li class="menu-item">
                <a class="menu-link menu-arrow" href="#sidebarReport" data-bs-toggle="collapse" role="button"
                   aria-expanded="false" aria-controls="sidebarReport">
        <span class="nav-icon">
            <i class="ri-database-2-line"></i>
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
                    </ul>
                </div>
            </li>
            <li class="menu-item">
                <a class="menu-link" href="reports.html">
                         <span class="nav-icon">
                              <i class="ri-coin-line"></i>
                         </span>
                    <span class="nav-text"> Pengeluaran </span>
                </a>
            </li>


        </ul>
    </div>
</div>

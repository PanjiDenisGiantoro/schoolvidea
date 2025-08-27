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

                          <!-- Petugas -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('petugas') }}">Petugas</a></li>
                          <!-- Unit -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('unit') }}">Unit</a></li>

                          <!-- Petugas Unit -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('petugas-unit') }}">Petugas Unit</a></li>

                          <!-- Tahun -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('tahun') }}">Tahun</a></li>

                          <!-- Kelas -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('kelas') }}">Kelas</a></li>

                          <!-- Siswa -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('siswa') }}">Siswa</a></li>
                          <!-- Akun -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('siswa') }}">Akun</a></li>

                          <!-- Kategori -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('kategori') }}">Kategori</a></li>
                          <!-- Rekening -->
                          <li class="sub-menu-item"><a class="sub-menu-link" href="{{ url('kategori') }}">Rekening</a></li>
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
                  <a class="menu-link" href="settings.html">
                         <span class="nav-icon">
                              <i class="ri-store-3-line"></i>
                         </span>
                      <span class="nav-text">Tabungan</span>
                  </a>
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
                    <a class="menu-link" href="pos.html">
                         <span class="nav-icon">
                              <i class="ri-mac-line"></i>
                         </span>
                         <span class="nav-text"> Pembayaran </span>
                    </a>
               </li>

               <li class="menu-item">
                    <a class="menu-link" href="reports.html">
                         <span class="nav-icon">
                              <i class="ri-bar-chart-box-ai-line"></i>
                         </span>
                         <span class="nav-text"> Reports </span>
                    </a>
               </li>


          </ul>
     </div>
</div>

<aside class="sidebar">
    <div>
        <h3 class="text-primary">
            {{ auth("merchant")->user()->nama_merchant }}
        </h3>
    </div>
    <div class="menu-label text-success" style="margin-top: 20px">MENU</div>
    <ul class="nav-menu">
        <li
            class="nav-item {{ request()->is("merchant/dashboard") ? "active" : "" }}"
        >
            <a href="/merchant/dashboard">
                <i class="fas fa-dashboard me-2"></i>
                Dashboard
            </a>
        </li>
        <li
            class="nav-item {{ request()->is("merchant/product") ? "active" : "" }}"
        >
            <a href="/merchant/product">
                <i class="fas fa-store me-2"></i>
                Produk
            </a>
        </li>
        <li class="nav-item">
            <a href="#">
                <i class="fas fa-calendar"></i>
                Informasi Transaksi
            </a>
        </li>
        <li class="nav-item">
            <a href="#">
                <i class="fas fa-users"></i>
                Informasi Saldo
            </a>
        </li>
    </ul>

    <div class="menu-label text-success" style="margin-top: 20px">GENERAL</div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="#">
                <i class="fas fa-cog"></i>
                Profil
            </a>
        </li>
        <li class="nav-item">
            <a href="#">
                <i class="fas fa-question-circle"></i>
                Kontak
            </a>
        </li>
        <li class="nav-item">
            <form action="{{ route("merchant.logout") }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </form>
        </li>
    </ul>
</aside>

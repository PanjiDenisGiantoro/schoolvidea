<aside class="sidebar">
    <div>
        <h3 class="text-primary">
            <i class="fas fa-store me-2"></i>
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
        <li
            class="nav-item {{ request()->is("merchant/transaction") ? "active" : "" }}"
        >
            <a href="/merchant/transaction">
                <i class="fas fa-arrow-trend-up me-2"></i>
                Informasi Transaksi
            </a>
        </li>
        <li
            class="nav-item {{ request()->is("merchant/balance") ? "active" : "" }}"
        >
            <a href="/merchant/balance">
                <i class="fas fa-wallet me-2"></i>
                Informasi Saldo
            </a>
        </li>
    </ul>

    <div class="menu-label text-success" style="margin-top: 20px">GENERAL</div>
    <ul class="nav-menu">
        <li
            class="nav-item {{ request()->is("merchant/profile") ? "active" : "" }}"
        >
            <a href="/merchant/profile">
                <i class="fas fa-cog me-2"></i>
                Profil
            </a>
        </li>
        <li class="nav-item">
            <form
                id="logoutForm"
                action="{{ route("merchant.logout") }}"
                method="POST"
            >
                @csrf
                <button type="submit" class="btn-logout-link">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </button>
            </form>
        </li>
    </ul>
</aside>

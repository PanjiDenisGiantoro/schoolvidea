<header class="topbar">
    <div class="search-wrapper" hidden>
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search Task..." />
        <span class="search-shortcut">⌘ F</span>
    </div>

    <div></div>

    <div class="topbar-right">
        <div class="icon-btns">
            <i class="far fa-envelope"></i>
            <i class="far fa-bell"></i>
        </div>
        <div class="user-info">
            <div class="user-text">
                <span class="user-name">
                    {{ auth("merchant")->user()->pemilik }} |
                </span>
                <span class="user-email">
                    {{ auth("merchant")->user()->kode_merchant ?? "merchant@mail.com" }}
                </span>
            </div>
            <img
                src="https://ui-avatars.com/api/?name={{ auth("merchant")->user()->nama_merchant }}"
                class="avatar"
            />
        </div>
    </div>
</header>

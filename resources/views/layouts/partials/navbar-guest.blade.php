<div class="navbar-nav flex-row flex-wrap align-items-center justify-content-center mx-auto gap-1 gap-lg-2 my-2 my-lg-0">
    <a class="nav-pill-link {{ request()->routeIs('home', 'books.*') ? 'active' : '' }}"
        href="{{ route('books.index') }}">
        <i class="bi bi-shop"></i> Katalog Buku
    </a>
    <a class="nav-pill-link {{ request()->routeIs('about') ? 'active' : '' }}"
        href="{{ route('about') }}">
        <i class="bi bi-info-circle"></i> About Us
    </a>
</div>

<div class="d-flex align-items-center justify-content-end gap-2 my-2 my-lg-0 flex-shrink-0">
    <a href="{{ route('login') }}" class="btn btn-soft-secondary btn-sm px-3">
        Masuk Akun
    </a>
    <a href="{{ route('register') }}" class="btn btn-primary btn-sm px-3">
        Daftar Akun <i class="bi bi-arrow-right"></i>
    </a>
</div>

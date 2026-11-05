<div class="navbar-nav me-auto gap-1 mt-2 mt-lg-0">
    <a class="nav-pill-link {{ request()->routeIs('home', 'books.*') ? 'active' : '' }}"
        href="{{ route('books.index') }}">
        <i class="bi bi-compass"></i> Katalog OPAC
    </a>
</div>

<div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
    <a href="{{ route('login') }}" class="btn btn-soft-secondary btn-sm px-3">
        Masuk Akun
    </a>
    <a href="{{ route('register') }}" class="btn btn-primary btn-sm px-3">
        Daftar Anggota <i class="bi bi-arrow-right"></i>
    </a>
</div>

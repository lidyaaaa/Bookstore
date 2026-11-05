@php
    $statusInfo = auth()->user()->accountStatusInfo();
@endphp

<div class="navbar-nav me-auto gap-1 mt-2 mt-lg-0">
    <a class="nav-pill-link {{ request()->routeIs('home', 'books.*') ? 'active' : '' }}"
        href="{{ route('books.index') }}">
        <i class="bi bi-compass"></i> Katalog OPAC
    </a>

    <a class="nav-pill-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
        href="{{ route('dashboard') }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
    </a>

    <a class="nav-pill-link {{ request()->routeIs('loans.*') ? 'active' : '' }}"
        href="{{ route('loans.index') }}">
        <i class="bi bi-arrow-left-right"></i> Peminjaman Saya
    </a>

    <a class="nav-pill-link {{ request()->routeIs('member.card') ? 'active' : '' }}"
        href="{{ route('member.card') }}">
        <i class="bi bi-person-vcard"></i> Kartu Anggota
    </a>
</div>

<div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
    <span class="badge bg-{{ $statusInfo['color'] }} bg-opacity-10 text-{{ $statusInfo['color'] === 'warning' ? 'dark' : $statusInfo['color'] }} border border-{{ $statusInfo['color'] }} border-opacity-25 d-inline-flex align-items-center gap-1 py-2 px-3">
        <i class="bi {{ $statusInfo['icon'] }}"></i>
        {{ $statusInfo['status'] }}
    </span>

    <div class="d-flex align-items-center gap-2 ps-2 border-start ms-1">
        <span class="avatar-chip">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </span>
        <div class="lh-sm d-none d-sm-block me-1">
            <div class="fw-bold text-slate-900" style="font-size: 0.82rem;">{{ auth()->user()->name }}</div>
            <div class="text-slate-600 font-monospace" style="font-size: 0.7rem;">
                {{ auth()->user()->nomor_anggota }}
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-soft-danger btn-sm" title="Keluar / Logout">
                <i class="bi bi-power"></i>
                <span class="d-lg-none">Logout</span>
            </button>
        </form>
    </div>
</div>

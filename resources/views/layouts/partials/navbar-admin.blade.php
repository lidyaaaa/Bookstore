@php
    \App\Models\Transaction::syncPendingTransactions();
    $pendingTransactionsCount = \App\Models\Transaction::where('status', 'menunggu')->count();
@endphp

<div class="navbar-nav flex-row flex-wrap align-items-center justify-content-center mx-auto gap-1 gap-lg-2 my-2 my-lg-0">
    <a class="nav-pill-link {{ request()->routeIs('home', 'books.*') ? 'active' : '' }}"
        href="{{ route('books.index') }}">
        <i class="bi bi-shop"></i> Katalog Buku
    </a>

    <a class="nav-pill-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
        href="{{ route('dashboard') }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
    </a>

    <a class="nav-pill-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}"
        href="{{ route('transactions.index') }}">
        <i class="bi bi-receipt-cutoff"></i> Transaksi Penjualan
        @if($pendingTransactionsCount > 0)
            <span class="badge rounded-pill bg-danger ms-1" title="{{ $pendingTransactionsCount }} pesanan menunggu konfirmasi">{{ $pendingTransactionsCount }}</span>
        @endif
    </a>

    <a class="nav-pill-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
        href="{{ route('reports.index') }}">
        <i class="bi bi-bar-chart-line"></i> Laporan Penjualan
    </a>

    <a class="nav-pill-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
        href="{{ route('users.index') }}">
        <i class="bi bi-people"></i> Daftar Pengguna
    </a>
</div>

<div class="d-flex align-items-center justify-content-end gap-2 my-2 my-lg-0 flex-shrink-0">
    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 py-2 px-3">
        <i class="bi bi-shield-check me-1"></i> Admin Toko
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

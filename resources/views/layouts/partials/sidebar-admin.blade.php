@php
    \App\Models\Transaction::syncPendingTransactions();
    $pendingTransactionsCount = \App\Models\Transaction::where('status', 'menunggu')->count();
    $unreadChatCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
@endphp

{{-- Sidebar Mobile Offcanvas & Desktop Sticky --}}
<aside class="admin-sidebar bg-white border-end shadow-sm d-flex flex-column flex-shrink-0 p-3 no-print" id="adminSidebar">
    <!-- Brand / Logo -->
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none text-slate-900 pb-3 mb-2 border-bottom">
        <span class="brand-logo-box">
            <i class="bi bi-bag-check-fill fs-5"></i>
        </span>
        <div>
            <div class="lh-1 fw-extrabold" style="font-size: 1.05rem;">BookStore</div>
            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mt-1 py-1 px-2 fw-semibold" style="font-size: 0.68rem;">
                <i class="bi bi-shield-check me-1"></i> Admin Toko
            </div>
        </div>
    </a>

    <!-- Menu Links -->
    <div class="sidebar-menu-wrapper flex-grow-1 overflow-y-auto py-2">
        <div class="text-uppercase text-slate-400 fw-bold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 0.05em;">Menu Utama</div>
        <ul class="nav nav-pills flex-column gap-1 mb-3">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') ? 'active' : 'text-slate-700' }}"
                   href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('home', 'books.*') ? 'active' : 'text-slate-700' }}"
                   href="{{ route('books.index') }}">
                    <i class="bi bi-shop"></i>
                    <span>Katalog Buku</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('transactions.*') ? 'active' : 'text-slate-700' }}"
                   href="{{ route('transactions.index') }}">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span>Transaksi</span>
                    </span>
                    @if($pendingTransactionsCount > 0)
                        <span class="badge rounded-pill bg-danger" title="{{ $pendingTransactionsCount }} pesanan menunggu konfirmasi">{{ $pendingTransactionsCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('chat.*') ? 'active' : 'text-slate-700' }}"
                   href="{{ route('chat.index') }}">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-dots"></i>
                        <span>Chat Pelanggan</span>
                    </span>
                    @if($unreadChatCount > 0)
                        <span class="badge rounded-pill bg-danger" title="{{ $unreadChatCount }} pesan belum dibaca">{{ $unreadChatCount }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <div class="text-uppercase text-slate-400 fw-bold px-2 mb-2" style="font-size: 0.68rem; letter-spacing: 0.05em;">Manajemen</div>
        <ul class="nav nav-pills flex-column gap-1 mb-2">
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('reports.*') ? 'active' : 'text-slate-700' }}"
                   href="{{ route('reports.index') }}">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Laporan Penjualan</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('users.*') ? 'active' : 'text-slate-700' }}"
                   href="{{ route('users.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Daftar Pengguna</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Admin Profile & Logout -->
    <div class="border-top pt-3 mt-auto">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <span class="avatar-chip flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div class="lh-sm text-truncate">
                    <div class="fw-bold text-slate-900 text-truncate" style="font-size: 0.85rem;" title="{{ auth()->user()->name }}">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="text-slate-500 font-monospace" style="font-size: 0.72rem;">
                        {{ auth()->user()->nomor_anggota }}
                    </div>
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" class="ms-2 flex-shrink-0">
                @csrf
                <button class="btn btn-soft-danger btn-sm rounded-circle p-2" title="Keluar / Logout" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-power"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

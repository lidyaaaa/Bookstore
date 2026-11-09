@extends('layouts.app')

@section('title', 'Katalog Buku — BookStore')

@section('content')
    {{-- Header Katalog Bookstore --}}
    <div class="opac-header-surface p-4 p-lg-5 mb-4">
        <div class="row align-items-center justify-content-between g-3 mb-4">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2"
                    style="background: #dbeafe; color: #1e40af; font-size: 0.76rem; font-weight: 700;">
                    <i class="bi bi-shop"></i>
                    <span>KATALOG TOKO BUKU DIGITAL</span>
                </div>
                <h1 class="fw-extrabold text-slate-900 mb-1" style="font-size: clamp(1.55rem, 2.6vw, 2.2rem);">
                    Temukan &amp; Beli <span class="text-gradient-brand">Buku Pilihanmu</span>
                </h1>
                <p class="text-slate-600 mb-0" style="font-size: 0.94rem;">
                    Cari buku secara instan berdasarkan Judul, Penulis, Kategori, Nomor ISBN, atau Rak.
                </p>
            </div>

            {{-- Panel Kanan Sesuai Role --}}
            <div class="col-lg-5 d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                <div class="stat-glass-pill">
                    <i class="bi bi-journals text-primary"></i>
                    <span>Total: <strong class="text-slate-900">{{ $books->total() }}</strong> Judul</span>
                </div>

                @auth
                    @if(auth()->user()->role === 'admin')
                        <button type="button" class="btn btn-soft-primary bg-white"
                            data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="bi bi-tags-fill"></i> + Kelola Kategori
                        </button>
                        <a href="{{ route('books.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> Tambah Buku
                        </a>
                    @else
                        @php
                            $cartCount = (int) auth()->user()->carts()->sum('jumlah');
                        @endphp
                        <a href="{{ route('cart.index') }}" class="btn btn-primary">
                            <i class="bi bi-cart3"></i> Keranjang Saya ({{ $cartCount }})
                        </a>
                        <a href="{{ route('transactions.index') }}" class="btn btn-soft-primary bg-white">
                            <i class="bi bi-receipt"></i> Pesanan Saya
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk untuk Membeli
                    </a>
                @endauth
            </div>
        </div>

        {{-- Search Bar --}}
        <form method="GET" action="{{ route('books.index') }}">
            @if(request()->filled('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            @if(request()->filled('availability'))
                <input type="hidden" name="availability" value="{{ request('availability') }}">
            @endif

            <div class="opac-search-bar mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-9 col-lg-10">
                        <div class="d-flex align-items-center ps-2">
                            <i class="bi bi-search text-primary fs-5 me-2"></i>
                            <input type="text" name="search" class="form-control ps-1"
                                placeholder="Ketik judul buku, nama penulis, penerbit, atau nomor ISBN..."
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            Cari Buku
                        </button>
                        @if(request()->hasAny(['search', 'category_id', 'availability']))
                            <a href="{{ route('books.index') }}" class="btn btn-soft-danger px-3" title="Reset Semua Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- Interactive Filter Chips --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-1">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="small fw-bold text-slate-600 me-1">
                    <i class="bi bi-funnel me-1"></i>Kategori:
                </span>
                <a href="{{ route('books.index', array_filter(['search' => request('search'), 'availability' => request('availability')])) }}"
                    class="filter-chip {{ !request()->filled('category_id') ? 'active' : '' }}">
                    Semua
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('books.index', array_filter(['search' => request('search'), 'category_id' => $cat->id, 'availability' => request('availability')])) }}"
                        class="filter-chip {{ (string) request('category_id') === (string) $cat->id ? 'active' : '' }}">
                        <span>{{ $cat->nama }}</span>
                        <span class="opacity-75">({{ $cat->books_count }})</span>
                    </a>
                @endforeach

                @auth
                    @if(auth()->user()->role === 'admin')
                        <button type="button" class="filter-chip text-primary border-primary border-opacity-50"
                            style="border-style: dashed !important; background: rgba(239, 246, 255, 0.85);"
                            data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="bi bi-plus-circle-fill"></i> Tambah / Edit Kategori
                        </button>
                    @endif
                @endauth
            </div>

            <div class="d-flex flex-wrap align-items-center gap-1">
                <a href="{{ route('books.index', array_filter(['search' => request('search'), 'category_id' => request('category_id')])) }}"
                    class="filter-chip {{ !request()->filled('availability') ? 'active' : '' }}">
                    Semua Stok
                </a>
                <a href="{{ route('books.index', array_filter(['search' => request('search'), 'category_id' => request('category_id'), 'availability' => 'available'])) }}"
                    class="filter-chip {{ request('availability') === 'available' ? 'active' : '' }}">
                    <span class="status-dot bg-success"></span> Stok Tersedia
                </a>
                <a href="{{ route('books.index', array_filter(['search' => request('search'), 'category_id' => request('category_id'), 'availability' => 'out_of_stock'])) }}"
                    class="filter-chip {{ request('availability') === 'out_of_stock' ? 'active' : '' }}">
                    <span class="status-dot bg-danger"></span> Stok Habis
                </a>
            </div>
        </div>
    </div>

    {{-- Grid Kartu Buku --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
        @forelse($books as $book)
            @include('books.partials.book-card', ['book' => $book])
        @empty
            <div class="col-12">
                <div class="card border-0 p-5 text-center">
                    <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 64px; height: 64px;">
                        <i class="bi bi-search fs-3 text-slate-600"></i>
                    </div>
                    <h5 class="fw-bold text-slate-900">Buku Tidak Ditemukan</h5>
                    <p class="text-slate-600 small mb-3">Tidak ada buku yang cocok dengan kata kunci atau filter yang Anda pilih.</p>
                    <div>
                        <a href="{{ route('books.index') }}" class="btn btn-soft-primary btn-sm px-3">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Filter Pencarian
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $books->links('pagination::bootstrap-5') }}
    </div>

    {{-- Modal Kategori khusus Admin --}}
    @auth
        @if(auth()->user()->role === 'admin')
            @include('admin.books.partials.category-modal')
        @endif
    @endauth
@endsection

@php
    $gradientClass = 'cover-gradient-' . (($book->category_id ?? $book->id) % 4);
    $soldCount = (int) ($book->terjual_count ?? 0);
    $inUserCart = auth()->check()
        && auth()->user()->role === 'user'
        && auth()->user()->carts()->where('book_id', $book->id)->exists();
@endphp

<div class="col">
    <div class="card book-card h-100 d-flex flex-column">
        {{-- Framed Sampul Rasio 3:4 --}}
        <div class="book-cover-wrapper" data-bs-toggle="modal" data-bs-target="#bookDetailModal-{{ $book->id }}">
            @if($book->cover)
                <img src="{{ asset('storage/' . $book->cover) }}" class="book-cover-img"
                    alt="Cover {{ $book->judul }}">
            @else
                <div class="cover-placeholder {{ $gradientClass }}">
                    <div class="d-flex justify-content-between align-items-center opacity-75">
                        <i class="bi bi-book-half fs-5"></i>
                        <span class="small fw-bold font-monospace">
                            {{ $book->category->kode_ddc ?? 'BOOK' }}
                        </span>
                    </div>
                    <div class="my-auto py-2">
                        <div class="fw-extrabold line-clamp-3 lh-sm mb-2" style="font-size: 1.02rem;">
                            {{ $book->judul }}
                        </div>
                        <div class="small fw-semibold opacity-75 text-truncate">
                            {{ $book->penulis }}
                        </div>
                    </div>
                    <div class="small opacity-75 border-top border-dark border-opacity-10 pt-2 d-flex justify-content-between align-items-center" style="font-size: 0.72rem;">
                        <span class="fw-semibold">{{ $book->tahun_terbit ?? 'Buku' }}</span>
                        <span>Lihat Detail <i class="bi bi-arrow-up-right"></i></span>
                    </div>
                </div>
            @endif

            {{-- Floating Glass Status Pill --}}
            @if($inUserCart)
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="status-pill-floating text-primary">
                        <span class="status-dot bg-primary"></span>
                        Di Keranjang
                    </span>
                </div>
            @elseif($book->stok > 0)
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="status-pill-floating text-success">
                        <span class="status-dot bg-success"></span>
                        Stok: {{ $book->stok }}
                    </span>
                </div>
            @else
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="status-pill-floating text-danger">
                        <span class="status-dot bg-danger"></span>
                        Habis
                    </span>
                </div>
            @endif
        </div>

        {{-- Body Kartu --}}
        <div class="px-2 pt-3 pb-1 d-flex flex-column flex-grow-1">
            <div class="d-flex justify-content-between align-items-center gap-1 mb-2">
                <span class="rack-badge text-truncate" title="Kode Rak">
                    <i class="bi bi-tag-fill text-primary"></i>
                    {{ $book->category->nama ?? 'Umum' }}
                </span>
                <span class="fw-extrabold text-primary" style="font-size: 0.92rem;">
                    {{ $book->formatted_harga }}
                </span>
            </div>

            <h6 class="fw-bold text-slate-900 line-clamp-2 mb-1"
                style="min-height: 2.45rem; font-size: 0.96rem; line-height: 1.35; cursor: pointer;"
                data-bs-toggle="modal" data-bs-target="#bookDetailModal-{{ $book->id }}"
                title="{{ $book->judul }}">
                {{ $book->judul }}
            </h6>

            <div class="text-slate-600 fw-medium text-truncate mb-3" style="font-size: 0.82rem;">
                {{ $book->penulis }}
            </div>

            {{-- Footer Kartu Minimalis --}}
            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center gap-1" style="font-size: 0.74rem;">
                <div class="d-flex align-items-center gap-1 text-nowrap">
                    <span class="text-slate-600">Stok: <strong class="{{ $book->stok > 0 ? 'text-slate-900' : 'text-danger' }}">{{ $book->stok }}</strong></span>
                    <span class="text-slate-300">|</span>
                    <span class="fw-medium text-slate-600">{{ $soldCount }} terjual</span>
                </div>

                <button type="button" class="btn btn-soft-secondary btn-sm py-1 px-2 flex-shrink-0"
                    data-bs-toggle="modal" data-bs-target="#bookDetailModal-{{ $book->id }}"
                    style="font-size: 0.72rem;">
                    Detail
                </button>
            </div>

            {{-- Komponen Tombol Aksi Terpisah Sesuai Role --}}
            @auth
                @if(auth()->user()->role === 'admin')
                    @include('admin.books.partials.card-actions', ['book' => $book])
                @else
                    @include('user.books.partials.card-actions', ['book' => $book])
                @endif
            @endauth
        </div>
    </div>
</div>

@include('books.partials.detail-modal', [
    'book' => $book,
    'gradientClass' => $gradientClass,
    'soldCount' => $soldCount,
])

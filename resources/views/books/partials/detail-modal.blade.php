{{-- Modal Pop-Up Detail Buku (Sinopsis Lengkap, ISBN, Metadata & Form Pinjam) --}}
<div class="modal fade" id="bookDetailModal-{{ $book->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-1">
                        {{ $book->category ? ($book->category->kode_ddc ? "[{$book->category->kode_ddc}] " : '') . $book->category->nama : 'Koleksi Umum' }}
                    </span>
                    <h5 class="modal-title fw-bold text-slate-900 mb-0">{{ $book->judul }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4 bg-white">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="book-cover-wrapper rounded-3 shadow-sm">
                            @if($book->cover)
                                <img src="{{ asset('storage/' . $book->cover) }}" class="book-cover-img" alt="Cover {{ $book->judul }}">
                            @else
                                <div class="cover-placeholder {{ $gradientClass }}">
                                    <i class="bi bi-book-half fs-3 opacity-75"></i>
                                    <div class="fw-bold my-auto">{{ $book->judul }}</div>
                                    <div class="small opacity-75">{{ $book->penulis }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-8 d-flex flex-column">
                        <div class="row g-3 small mb-3 p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="col-sm-6">
                                <div class="text-slate-600 mb-1">Pengarang / Penulis</div>
                                <div class="fw-bold text-slate-900">{{ $book->penulis }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-slate-600 mb-1">Penerbit & Tahun</div>
                                <div class="fw-semibold text-slate-900">{{ $book->penerbit ?? '-' }} ({{ $book->tahun_terbit ?? '-' }})</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-slate-600 mb-1">Nomor ISBN</div>
                                <div class="fw-semibold font-monospace text-slate-900">{{ $book->isbn ?? 'Belum tercatat' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-slate-600 mb-1">Lokasi Nomor Panggil Rak</div>
                                <div><span class="rack-badge"><i class="bi bi-geo-alt-fill text-primary"></i> {{ $book->call_number ?? 'Rak Umum' }}</span></div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-slate-900 mb-1">Sinopsis & Abstrak Koleksi</h6>
                        <p class="text-slate-700 small lh-lg mb-3">
                            {{ $book->sinopsis ?: 'Koleksi referensi resmi perpustakaan. Buku ini tersedia untuk dipinjam oleh anggota aktif sesuai ketentuan sirkulasi perpustakaan.' }}
                        </p>

                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <span class="small fw-semibold text-slate-700">Ketersediaan:</span>
                                <div class="d-flex gap-2">
                                    @if($book->stok > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                                            Ketersediaan: {{ $book->stok }} Eksemplar
                                        </span>
                                    @endif
                                    <span class="badge bg-secondary bg-opacity-10 text-slate-700 border px-3 py-2">
                                        Dipinjam: {{ $borrowedCount }} Eksemplar
                                    </span>
                                </div>
                            </div>

                            @auth
                                @if(auth()->user()->role === 'user')
                                    @php
                                        $canUserBorrow = auth()->user()->evaluateBorrowEligibility()['allowed'];
                                        $isBorrowedByUser = $isBorrowedByUser ?? auth()->user()->activeLoans()->where('book_id', $book->id)->exists();
                                    @endphp
                                    @if($book->stok > 0 && $canUserBorrow && ! $isBorrowedByUser)
                                        <form action="{{ route('loans.store', $book) }}" method="POST"
                                            class="p-3 rounded-4" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                                            @csrf
                                            <label class="form-label small fw-bold text-slate-900 mb-2">
                                                <i class="bi bi-calendar-check text-primary me-1"></i>
                                                Batas Pengembalian (Maksimal 3 Minggu / 21 Hari)
                                            </label>
                                            <div class="d-flex gap-2">
                                                <input type="date" name="tanggal_kembali" class="form-control"
                                                    min="{{ now()->addDay()->toDateString() }}"
                                                    max="{{ now()->addDays(21)->toDateString() }}"
                                                    value="{{ now()->addDays(21)->toDateString() }}" required>
                                                <button type="submit" class="btn btn-primary px-4 text-nowrap">
                                                    <i class="bi bi-check2-circle"></i> Ajukan Pinjam
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-box-arrow-in-right"></i> Masuk Akun untuk Meminjam Koleksi Ini
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@extends('layouts.app')

@section('title', 'Edit Koleksi Buku')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Edit Koleksi Buku</h3>
                <p class="text-secondary small mb-0">Perbarui informasi bibliografi, lokasi rak, dan ketersediaan stok.</p>
            </div>
            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <form action="{{ route('books.update', $book) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-slate-700">Judul Buku <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control"
                        value="{{ old('judul', $book->judul) }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-slate-700">Pilih Subjek / Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Pilih Subjek --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $book->category_id) == $cat->id)>
                                {{ $cat->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-primary">+ Atau Buat Subjek Baru</label>
                    <input type="text" name="kategori_baru" class="form-control"
                        placeholder="Misal: Novel, Teknologi..." value="{{ old('kategori_baru') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Penulis / Pengarang <span class="text-danger">*</span></label>
                    <input type="text" name="penulis" class="form-control"
                        value="{{ old('penulis', $book->penulis) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control"
                        value="{{ old('penerbit', $book->penerbit) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" class="form-control"
                        value="{{ old('tahun_terbit', $book->tahun_terbit) }}" min="1000" max="{{ date('Y') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Nomor ISBN</label>
                    <input type="text" name="isbn" class="form-control"
                        value="{{ old('isbn', $book->isbn) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Nomor Panggil Rak (Call Number)</label>
                    <input type="text" name="call_number" class="form-control"
                        value="{{ old('call_number', $book->call_number) }}">
                </div>

                @php
                    $borrowedCount = $book->activeLoans()->count();
                @endphp

                <div class="col-md-2">
                    <label class="form-label fw-medium">Ketersediaan</label>
                    <input type="number" id="ketersediaanInput" class="form-control bg-light"
                        value="{{ max(0, (int) old('total_koleksi', $book->total_koleksi) - $borrowedCount) }}" readonly>
                    <div class="form-text">{{ $borrowedCount }} dipinjam</div>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-medium">Total Fisik <span class="text-danger">*</span></label>
                    <input type="number" name="total_koleksi" class="form-control"
                        value="{{ old('total_koleksi', $book->total_koleksi) }}"
                        min="{{ $borrowedCount }}"
                        oninput="document.getElementById('ketersediaanInput').value = Math.max(0, (parseInt(this.value, 10) || 0) - {{ $borrowedCount }})"
                        required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Sinopsis Ringkas</label>
                    <textarea name="sinopsis" rows="3" class="form-control">{{ old('sinopsis', $book->sinopsis) }}</textarea>
                </div>

                @if($book->cover)
                    <div class="col-12">
                        <label class="form-label d-block fw-medium">Cover Saat Ini</label>
                        <img src="{{ asset('storage/' . $book->cover) }}" alt="Cover buku" class="rounded border p-1" style="max-height:140px">
                    </div>
                @endif

                <div class="col-12">
                    <label class="form-label fw-medium">Ganti Cover (Opsional)</label>
                    <input type="file" name="cover" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
                <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit Buku — BookStore')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Edit Data Buku</h3>
                <p class="text-secondary small mb-0">Perbarui informasi buku, harga jual, dan jumlah stok tersedia (minimal 0).</p>
            </div>
            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <form action="{{ route('books.update', $book) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label fw-semibold small text-slate-700">Judul Buku <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control"
                        value="{{ old('judul', $book->judul) }}" required>
                </div>

                <div class="col-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small text-slate-700 mb-0">Pilih Kategori</label>
                        <button type="button" class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none"
                            data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="bi bi-plus-circle-fill me-1"></i>Tambah / Kelola Kategori Baru
                        </button>
                    </div>
                    <div class="input-group">
                        <select name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $book->category_id) == $cat->id)>
                                    {{ $cat->nama }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-soft-primary"
                            data-bs-toggle="modal" data-bs-target="#categoryModal"
                            title="Buka Modal Kelola Kategori">
                            <i class="bi bi-tags-fill"></i> + Kategori
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Penulis / Pengarang <span class="text-danger">*</span></label>
                    <input type="text" name="penulis" class="form-control"
                        value="{{ old('penulis', $book->penulis) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Penerbit <span class="text-danger">*</span></label>
                    <input type="text" name="penerbit" class="form-control"
                        value="{{ old('penerbit', $book->penerbit) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" class="form-control"
                        value="{{ old('tahun_terbit', $book->tahun_terbit) }}" min="1000" max="{{ date('Y') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Nomor ISBN</label>
                    <input type="text" name="isbn" class="form-control"
                        value="{{ old('isbn', $book->isbn) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Kode Rak Toko</label>
                    <input type="text" name="call_number" class="form-control"
                        value="{{ old('call_number', $book->call_number) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Harga Jual (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="harga" class="form-control"
                        value="{{ old('harga', $book->harga) }}" min="0"
                        oninput="if(this.value < 0) this.value = 0;"
                        required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Stok Tersedia <span class="text-danger">*</span></label>
                    <input type="number" name="stok" class="form-control"
                        value="{{ old('stok', $book->stok) }}" min="0"
                        oninput="if(this.value < 0) this.value = 0;"
                        required>
                    <div class="form-text">Stok tidak boleh minus (minimal 0).</div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Sinopsis / Deskripsi Buku</label>
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

@include('admin.books.partials.category-modal')
@endsection

@extends('layouts.app')

@section('title', 'Tambah Koleksi Buku')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Tambah Koleksi Buku Baru</h3>
                <p class="text-secondary small mb-0">Lengkapi metadata bibliografi untuk katalog OPAC perpustakaan.</p>
            </div>
            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke OPAC
            </a>
        </div>

        <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-slate-700">Judul Buku <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small text-slate-700">Pilih Subjek / Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Pilih Subjek --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
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
                    <input type="text" name="penulis" class="form-control" value="{{ old('penulis') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control" value="{{ old('penerbit') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" class="form-control"
                        value="{{ old('tahun_terbit', date('Y')) }}" min="1000" max="{{ date('Y') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Nomor ISBN</label>
                    <input type="text" name="isbn" class="form-control"
                        placeholder="978-602-xxxx-xx-x" value="{{ old('isbn') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Nomor Panggil Rak (Call Number)</label>
                    <input type="text" name="call_number" class="form-control"
                        placeholder="Contoh: 005.133 KUS p" value="{{ old('call_number') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-medium">Ketersediaan</label>
                    <input type="number" id="ketersediaanCreateInput" class="form-control bg-light"
                        value="{{ old('total_koleksi', 1) }}" readonly>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-medium">Total Fisik <span class="text-danger">*</span></label>
                    <input type="number" name="total_koleksi" class="form-control"
                        value="{{ old('total_koleksi', 1) }}" min="0"
                        oninput="document.getElementById('ketersediaanCreateInput').value = Math.max(0, parseInt(this.value, 10) || 0)"
                        required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Sinopsis Ringkas</label>
                    <textarea name="sinopsis" rows="3" class="form-control"
                        placeholder="Ringkasan isi buku untuk ditampilkan pada katalog OPAC...">{{ old('sinopsis') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Sampul / Cover Buku</label>
                    <input type="file" name="cover" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                    <small class="text-secondary">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Koleksi
                </button>
                <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

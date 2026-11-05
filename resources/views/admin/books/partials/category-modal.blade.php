{{-- Modal Manajemen Subjek / Kategori Koleksi (Khusus Admin) --}}
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-slate-900 mb-0">
                        <i class="bi bi-tags-fill text-primary me-1"></i> Kelola Subjek & Kategori Buku
                    </h5>
                    <div class="text-slate-600" style="font-size: 0.78rem;">
                        Tambahkan atau ubah nama subjek (misal: Novel, Teknologi, Komik, Bisnis) agar filter katalog lebih fleksibel.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('categories.store') }}" method="POST" class="row g-2 mb-4 p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    @csrf
                    <div class="col-12 mb-1">
                        <span class="fw-bold text-slate-700" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">
                            <i class="bi bi-plus-circle-fill text-primary me-1"></i> Tambah Subjek / Kategori Baru
                        </span>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="nama" class="form-control form-control-sm" placeholder="Nama Subjek * (mis: Novel, Teknologi)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="kode_ddc" class="form-control form-control-sm" placeholder="Kode Rak/DDC (opsional)">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="deskripsi" class="form-control form-control-sm" placeholder="Keterangan singkat (opsional)">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 34%;">Nama Subjek / Kategori</th>
                                <th style="width: 18%;">Kode Rak</th>
                                <th style="width: 24%;">Keterangan</th>
                                <th style="width: 10%;">Buku</th>
                                <th class="text-end" style="width: 14%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                                <tr>
                                    <td colspan="5" class="p-0 border-0">
                                        <form action="{{ route('categories.update', $cat) }}" method="POST" class="row g-2 align-items-center py-2 px-2 border-bottom m-0">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-md-4">
                                                <input type="text" name="nama" value="{{ $cat->nama }}" class="form-control form-control-sm fw-bold text-slate-900" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" name="kode_ddc" value="{{ $cat->kode_ddc }}" class="form-control form-control-sm font-monospace" placeholder="Kode">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="deskripsi" value="{{ $cat->deskripsi }}" class="form-control form-control-sm text-slate-600" placeholder="Keterangan">
                                            </div>
                                            <div class="col-md-1 text-center">
                                                <span class="badge bg-secondary bg-opacity-10 text-slate-700">{{ $cat->books_count }}</span>
                                            </div>
                                            <div class="col-md-2 text-end d-flex justify-content-end gap-1">
                                                <button type="submit" class="btn btn-soft-primary btn-sm" title="Simpan Perubahan">
                                                    <i class="bi bi-check2"></i> Simpan
                                                </button>
                                                <button type="button" class="btn btn-soft-danger btn-sm btn-icon" title="Hapus Subjek"
                                                    onclick="if(confirm('Hapus subjek {{ addslashes($cat->nama) }}?')) document.getElementById('del-cat-{{ $cat->id }}').submit();">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <form id="del-cat-{{ $cat->id }}" action="{{ route('categories.destroy', $cat) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-slate-600 py-4">Belum ada subjek terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

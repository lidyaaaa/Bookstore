{{-- Modal Manajemen Kategori Buku (Khusus Admin) --}}
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-slate-900 mb-0">
                        <i class="bi bi-tags-fill text-primary me-1"></i> Kelola Kategori Buku
                    </h5>
                    <div class="text-slate-600" style="font-size: 0.78rem;">
                        Kategori yang bukunya sedang berada di dalam keranjang belanja pengguna tidak dapat dihapus.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('categories.store') }}" method="POST" class="row g-2 mb-4 p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    @csrf
                    <div class="col-12 mb-1">
                        <span class="fw-bold text-slate-700" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">
                            <i class="bi bi-plus-circle-fill text-primary me-1"></i> Tambah Kategori Baru
                        </span>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="nama" class="form-control form-control-sm" placeholder="Nama Kategori * (mis: Novel, Teknologi)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="kode_ddc" class="form-control form-control-sm" placeholder="Kode Rak (opsional)">
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
                                <th style="width: 34%;">Nama Kategori</th>
                                <th style="width: 18%;">Kode Rak</th>
                                <th style="width: 24%;">Keterangan</th>
                                <th style="width: 10%;">Buku</th>
                                <th class="text-end" style="width: 14%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                                @php
                                    $bookCount = $cat->books_count ?? $cat->books()->count();
                                    $catHasBooks = $bookCount > 0;
                                    $catHasCart = $cat->hasBooksInCart();
                                @endphp
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
                                                <span class="badge bg-secondary bg-opacity-10 text-slate-700">{{ $bookCount }}</span>
                                            </div>
                                            <div class="col-md-2 text-end d-flex justify-content-end gap-1">
                                                <button type="submit" class="btn btn-soft-primary btn-sm" title="Simpan Perubahan">
                                                    <i class="bi bi-check2"></i> Simpan
                                                </button>
                                                @if($catHasBooks)
                                                    <button type="button" class="btn btn-soft-secondary btn-sm btn-icon"
                                                        title="Tidak bisa dihapus: masih ada {{ $bookCount }} buku yang terhubung dengan kategori ini"
                                                        onclick="alert('Kategori {{ addslashes($cat->nama) }} tidak dapat dihapus karena masih terhubung dengan {{ $bookCount }} buku!');">
                                                        <i class="bi bi-lock-fill text-muted"></i>
                                                    </button>
                                                @elseif($catHasCart)
                                                    <button type="button" class="btn btn-soft-secondary btn-sm btn-icon"
                                                        title="Tidak bisa dihapus: buku di kategori ini sedang ada di keranjang user"
                                                        onclick="alert('Kategori {{ addslashes($cat->nama) }} tidak dapat dihapus karena buku di dalamnya sedang berada di keranjang belanja pengguna!');">
                                                        <i class="bi bi-lock-fill text-warning"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-soft-danger btn-sm btn-icon" title="Hapus Kategori"
                                                        onclick="if(confirm('Hapus kategori {{ addslashes($cat->nama) }}?')) document.getElementById('del-cat-{{ $cat->id }}').submit();">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                @endif
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
                                    <td colspan="5" class="text-center text-slate-600 py-4">Belum ada kategori terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

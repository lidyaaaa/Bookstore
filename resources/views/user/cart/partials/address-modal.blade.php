{{-- Modal Manajemen Alamat Pengiriman (Khusus User / Pelanggan, mirip Kelola Kategori) --}}
<div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-slate-900 mb-0">
                        <i class="bi bi-geo-alt-fill text-primary me-1"></i> Kelola Alamat Pengiriman
                    </h5>
                    <div class="text-slate-600" style="font-size: 0.78rem;">
                        Simpan daftar alamat Anda (Rumah, Kos, Kantor) agar bisa dipakai berulang kali saat memesan buku.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                {{-- Form Tambah Alamat Baru --}}
                <form action="{{ route('addresses.store') }}" method="POST" class="row g-2 mb-4 p-3 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    @csrf
                    <div class="col-12 mb-1">
                        <span class="fw-bold text-slate-700" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em;">
                            <i class="bi bi-plus-circle-fill text-primary me-1"></i> Tambah Alamat Baru
                        </span>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="label" class="form-control form-control-sm" placeholder="Label (Rumah/Kos)" value="Rumah" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="penerima" class="form-control form-control-sm" placeholder="Nama Penerima *" value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="telepon" class="form-control form-control-sm" placeholder="No. HP *" value="{{ auth()->user()->telepon }}" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="alamat_lengkap" class="form-control form-control-sm" placeholder="Alamat Lengkap (Jl, No, Kota) *" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </button>
                    </div>
                </form>

                {{-- Tabel Daftar Alamat Tersimpan --}}
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Label</th>
                                <th style="width: 22%;">Penerima</th>
                                <th style="width: 18%;">No. HP</th>
                                <th style="width: 31%;">Alamat Lengkap</th>
                                <th class="text-end" style="width: 14%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($addresses as $addr)
                                <tr>
                                    <td colspan="5" class="p-0 border-0">
                                        <form action="{{ route('addresses.update', $addr) }}" method="POST" class="row g-2 align-items-center py-2 px-2 border-bottom m-0">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-md-2">
                                                <input type="text" name="label" value="{{ $addr->label }}" class="form-control form-control-sm fw-bold text-slate-900" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" name="penerima" value="{{ $addr->penerima }}" class="form-control form-control-sm" required>
                                            </div>
                                            <div class="col-md-2">
                                                <input type="text" name="telepon" value="{{ $addr->telepon }}" class="form-control form-control-sm font-monospace" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" name="alamat_lengkap" value="{{ $addr->alamat_lengkap }}" class="form-control form-control-sm text-slate-700" required>
                                            </div>
                                            <div class="col-md-2 text-end d-flex justify-content-end gap-1">
                                                <button type="submit" class="btn btn-soft-primary btn-sm" title="Simpan Perubahan">
                                                    <i class="bi bi-check2"></i> Simpan
                                                </button>
                                                <button type="button" class="btn btn-soft-danger btn-sm btn-icon" title="Hapus Alamat"
                                                    onclick="if(confirm('Hapus alamat {{ addslashes($addr->label) }}?')) document.getElementById('del-addr-{{ $addr->id }}').submit();">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <form id="del-addr-{{ $addr->id }}" action="{{ route('addresses.destroy', $addr) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-slate-600 py-4">
                                        Belum ada alamat pengiriman tersimpan. Silakan tambahkan alamat di atas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Input Peminjaman Loket (Khusus Admin) --}}
<div class="modal fade" id="adminLoanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-slate-900">Input Peminjaman Loket Sirkulasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('loans.store-admin') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-slate-700">Pilih Anggota / Siswa</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih Anggota --</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">
                                    {{ $member->nomor_anggota }} - {{ $member->name }}
                                    ({{ $member->accountStatusInfo()['status'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-slate-700">Pilih Buku Tersedia</label>
                        <select name="book_id" class="form-select" required>
                            <option value="">-- Pilih Koleksi Buku --</option>
                            @foreach($availableBooks as $book)
                                <option value="{{ $book->id }}">
                                    [Tersedia: {{ $book->stok }}] {{ $book->judul }} ({{ $book->call_number ?? 'Rak Umum' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-slate-700">Batas Tanggal Kembali (3 Minggu / 21 Hari)</label>
                        <input type="date" name="tanggal_kembali" class="form-control"
                            min="{{ now()->addDay()->toDateString() }}"
                            max="{{ now()->addDays(21)->toDateString() }}"
                            value="{{ now()->addDays(21)->toDateString() }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Catat & Serahkan Buku
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

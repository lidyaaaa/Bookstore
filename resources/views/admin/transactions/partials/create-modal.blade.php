{{-- Modal Input Penjualan Kasir Langsung (Khusus Admin) --}}
<div class="modal fade" id="adminTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-slate-900">Input Penjualan Kasir Langsung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('transactions.store-admin') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-slate-700">Pilih Pelanggan / Member</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">
                                    {{ $member->nomor_anggota }} - {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-slate-700">Pilih Buku Tersedia</label>
                        <select name="book_id" class="form-select" required>
                            <option value="">-- Pilih Buku --</option>
                            @foreach($availableBooks as $book)
                                <option value="{{ $book->id }}">
                                    [Stok: {{ $book->stok }}] {{ $book->judul }} — {{ $book->formatted_harga }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small text-slate-700">Jumlah Beli</label>
                            <input type="number" name="jumlah" class="form-control" min="1" value="1"
                                oninput="if(this.value < 1) this.value = 1;" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold small text-slate-700">Metode Pembayaran</label>
                            <select name="metode_pembayaran" class="form-select" required>
                                @foreach(\App\Models\Transaction::PAYMENT_METHODS as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Catat Penjualan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@extends('layouts.app')

@section('title', 'Keranjang Belanja — BookStore')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-extrabold text-slate-900 mb-1">
                <i class="bi bi-cart3 text-primary me-1"></i> Keranjang Belanja Saya
            </h2>
            <p class="text-slate-600 mb-0 small">
                Centang produk yang ingin di-checkout, atur jumlah dengan tombol <code>-</code> / <code>+</code>, dan pilih alamat pengiriman.
            </p>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-soft-primary bg-white"
                data-bs-toggle="modal" data-bs-target="#addressModal">
                <i class="bi bi-geo-alt-fill"></i> + Kelola Alamat
            </button>
            <a href="{{ route('books.index') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Buku Lainnya
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Daftar Item di Keranjang --}}
        <div class="col-lg-8">
            <div class="card overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 44px;" class="text-center">
                                    <input type="checkbox" id="checkAllCart" class="form-check-input"
                                        style="width: 1.15rem; height: 1.15rem; cursor: pointer;"
                                        @checked($cartItems->isNotEmpty())
                                        @disabled($cartItems->isEmpty())
                                        title="Pilih Semua">
                                </th>
                                <th>Buku</th>
                                <th>Harga Satuan</th>
                                <th style="width: 150px;" class="text-center">Jumlah</th>
                                <th>Subtotal</th>
                                <th class="text-end">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cartItems as $item)
                                @php
                                    $maxStok = max(1, (int) ($item->book->stok ?? 1));
                                    $hargaSatuan = (int) ($item->book->harga ?? 0);
                                @endphp
                                <tr id="cart-row-{{ $item->id }}"
                                    data-cart-id="{{ $item->id }}"
                                    data-price="{{ $hargaSatuan }}"
                                    data-qty="{{ $item->jumlah }}"
                                    data-max="{{ $maxStok }}"
                                    data-update-url="{{ route('cart.update', $item) }}">
                                    <td class="text-center">
                                        <input type="checkbox"
                                            class="form-check-input cart-item-check"
                                            name="cart_ids[]"
                                            form="checkoutForm"
                                            value="{{ $item->id }}"
                                            style="width: 1.15rem; height: 1.15rem; cursor: pointer;"
                                            checked>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-slate-900">{{ $item->book->judul ?? '-' }}</div>
                                        <div class="small text-slate-600">
                                            {{ $item->book->penulis ?? '-' }} &bull;
                                            <span class="text-success fw-semibold">Stok: {{ $item->book->stok ?? 0 }}</span>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-slate-700">
                                        Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center border rounded-3 bg-white p-1">
                                            <button type="button"
                                                class="btn btn-sm btn-light border-0 px-2 py-1 fw-bold btn-qty-minus"
                                                onclick="changeCartQty({{ $item->id }}, -1)"
                                                @disabled($item->jumlah <= 1)>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                            <span class="px-3 fw-bold text-slate-900 cart-qty-display" style="min-width: 36px; text-align: center;">
                                                {{ $item->jumlah }}
                                            </span>
                                            <button type="button"
                                                class="btn btn-sm btn-light border-0 px-2 py-1 fw-bold btn-qty-plus"
                                                onclick="changeCartQty({{ $item->id }}, 1)"
                                                @disabled($item->jumlah >= $maxStok)>
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="fw-extrabold text-primary cart-subtotal-display">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('cart.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('Hapus buku ini dari keranjang?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-soft-danger btn-sm">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-slate-600">
                                        <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Keranjang belanja Anda masih kosong.
                                        <div class="mt-3">
                                            <a href="{{ route('books.index') }}" class="btn btn-primary btn-sm px-3">
                                                Belanja Buku Sekarang
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel Checkout & Ringkasan Pembayaran --}}
        <div class="col-lg-4">
            <div class="card p-4">
                <h5 class="fw-bold text-slate-900 border-bottom pb-3 mb-3">
                    Ringkasan Checkout
                </h5>

                <div class="d-flex justify-content-between small text-slate-600 mb-2">
                    <span>Produk Dipilih</span>
                    <span class="fw-bold text-slate-900"><span id="summaryTotalItem">{{ $totalItem }}</span> Eksemplar</span>
                </div>

                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <span class="fw-semibold text-slate-700">Total Pembayaran</span>
                    <span class="fs-4 fw-extrabold text-primary" id="summaryTotalHarga">
                        Rp {{ number_format($totalHarga, 0, ',', '.') }}
                    </span>
                </div>

                <form id="checkoutForm" action="{{ route('transactions.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cart_ids_submitted" value="1">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-slate-700">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="metode_pembayaran" class="form-select" required>
                            @foreach(\App\Models\Transaction::PAYMENT_METHODS as $method)
                                <option value="{{ $method }}" @selected(old('metode_pembayaran') === $method)>{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-slate-700 mb-0">
                                Pilih Alamat Pengiriman <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none"
                                data-bs-toggle="modal" data-bs-target="#addressModal">
                                <i class="bi bi-plus-circle-fill me-1"></i>+ Tambah Alamat
                            </button>
                        </div>

                        <div class="input-group">
                            <select name="address_id" class="form-select @error('address_id') is-invalid @enderror" required>
                                @forelse($addresses as $addr)
                                    <option value="{{ $addr->id }}" @selected(old('address_id') == $addr->id || $loop->first)>
                                        [{{ $addr->label }}] {{ $addr->penerima }} — {{ \Illuminate\Support\Str::limit($addr->alamat_lengkap, 38) }}
                                    </option>
                                @empty
                                    <option value="" disabled selected>-- Belum ada alamat, klik + Alamat --</option>
                                @endforelse
                            </select>
                            <button type="button" class="btn btn-soft-primary"
                                data-bs-toggle="modal" data-bs-target="#addressModal"
                                title="Kelola Alamat Pengiriman">
                                <i class="bi bi-geo-alt-fill"></i>
                            </button>
                        </div>
                        @error('address_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-slate-700">Catatan Pesanan (Opsional)</label>
                        <input type="text" name="catatan" class="form-control"
                            placeholder="Contoh: Bungkus rapi / hadiah..." value="{{ old('catatan') }}">
                    </div>

                    <button type="submit" id="btnCheckoutSubmit" class="btn btn-primary w-100 py-2 fw-bold" @disabled($cartItems->isEmpty())>
                        <i class="bi bi-bag-check-fill me-1"></i> Checkout Produk Dipilih
                    </button>
                </form>
            </div>
        </div>
    </div>

    @include('user.cart.partials.address-modal')

    <script>
        function formatRupiah(angka) {
            return 'Rp ' + Number(angka).toLocaleString('id-ID');
        }

        function recalculateCartSummary() {
            const rows = document.querySelectorAll('tr[data-cart-id]');
            let totalItem = 0;
            let totalHarga = 0;
            let checkedCount = 0;

            rows.forEach((row) => {
                const checkbox = row.querySelector('.cart-item-check');
                const qty = parseInt(row.getAttribute('data-qty'), 10) || 1;
                const price = parseInt(row.getAttribute('data-price'), 10) || 0;

                if (checkbox && checkbox.checked) {
                    checkedCount++;
                    totalItem += qty;
                    totalHarga += qty * price;
                }
            });

            const checkAll = document.getElementById('checkAllCart');
            if (checkAll && rows.length > 0) {
                checkAll.checked = checkedCount === rows.length;
                checkAll.indeterminate = checkedCount > 0 && checkedCount < rows.length;
            }

            document.getElementById('summaryTotalItem').textContent = totalItem;
            document.getElementById('summaryTotalHarga').textContent = formatRupiah(totalHarga);

            const btnCheckout = document.getElementById('btnCheckoutSubmit');
            if (btnCheckout) {
                btnCheckout.disabled = checkedCount === 0;
            }
        }

        async function changeCartQty(cartId, delta) {
            const row = document.getElementById('cart-row-' + cartId);
            if (!row) return;

            const currentQty = parseInt(row.getAttribute('data-qty'), 10) || 1;
            const maxStok = parseInt(row.getAttribute('data-max'), 10) || 1;
            const price = parseInt(row.getAttribute('data-price'), 10) || 0;
            const updateUrl = row.getAttribute('data-update-url');

            const newQty = currentQty + delta;
            if (newQty < 1 || newQty > maxStok) {
                return;
            }

            row.setAttribute('data-qty', newQty);
            row.querySelector('.cart-qty-display').textContent = newQty;
            row.querySelector('.cart-subtotal-display').textContent = formatRupiah(newQty * price);
            row.querySelector('.btn-qty-minus').disabled = newQty <= 1;
            row.querySelector('.btn-qty-plus').disabled = newQty >= maxStok;
            recalculateCartSummary();

            try {
                const response = await fetch(updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ jumlah: newQty })
                });

                if (!response.ok) {
                    const err = await response.json();
                    if (err.message) alert(err.message);
                    window.location.reload();
                }
            } catch (e) {
                window.location.reload();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const checkAll = document.getElementById('checkAllCart');
            const itemChecks = document.querySelectorAll('.cart-item-check');

            if (checkAll) {
                checkAll.addEventListener('change', () => {
                    itemChecks.forEach((chk) => {
                        chk.checked = checkAll.checked;
                    });
                    recalculateCartSummary();
                });
            }

            itemChecks.forEach((chk) => {
                chk.addEventListener('change', recalculateCartSummary);
            });
        });
    </script>
@endsection

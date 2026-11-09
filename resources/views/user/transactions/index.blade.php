@extends('layouts.app')

@section('title', 'Transaksi Pembelian Saya — BookStore')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-extrabold text-slate-900 mb-1">
                Pesanan &amp; Riwayat Transaksi
            </h2>
            <p class="text-slate-600 mb-0 small">
                Pantau pesanan buku yang sedang diproses serta arsip riwayat transaksi pembelian Anda.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('cart.index') }}" class="btn btn-soft-primary bg-white">
                <i class="bi bi-cart3"></i> Keranjang Saya
            </a>
            <a href="{{ route('books.index') }}" class="btn btn-primary">
                <i class="bi bi-shop"></i> Belanja Buku Baru
            </a>
        </div>
    </div>

    {{-- BAGIAN 1: BARANG YANG SEDANG DIPESAN (MENUNGGU KONFIRMASI) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle p-2 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-hourglass-split fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-slate-900 mb-0">Sedang Dipesan (Menunggu Konfirmasi)</h5>
                    <span class="small text-muted">Pesanan yang sedang diproses admin atau menunggu verifikasi</span>
                </div>
            </div>
            <span class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning border-opacity-50 px-3 py-2 rounded-pill fw-bold">
                {{ $activeOrders->count() }} Pesanan Aktif
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Daftar Buku Dipesan</th>
                        <th>Tanggal Pesan</th>
                        <th>Total Tagihan</th>
                        <th>Metode &amp; Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($activeOrders as $trx)
                        <tr class="align-middle">
                            <td style="width: 180px;">
                                <span class="rack-badge font-monospace text-primary fw-bold">{{ $trx->kode_transaksi }}</span>
                                <div class="small text-slate-600 mt-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-box-seam me-1"></i>{{ $trx->total_item }} item buku
                                </div>
                            </td>

                            <td>
                                @foreach($trx->items as $item)
                                    <div class="mb-1">
                                        <span class="fw-bold text-slate-900">{{ $item->book->judul ?? 'Buku' }}</span>
                                        <span class="badge bg-secondary bg-opacity-10 text-slate-700 ms-1 font-monospace">
                                            {{ $item->jumlah }}x @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($trx->alamat_pengiriman)
                                    <div class="small text-slate-600 mt-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $trx->alamat_pengiriman }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-slate-700 fw-medium" style="white-space: nowrap;">
                                {{ $trx->tanggal_transaksi?->format('d-m-Y') }}
                            </td>

                            <td>
                                <div class="fw-extrabold text-primary fs-6">
                                    Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                                </div>
                            </td>

                            <td>
                                <div class="small fw-semibold text-slate-800 mb-1">{{ $trx->metode_pembayaran }}</div>
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 px-2 py-1">
                                    <i class="bi bi-clock me-1"></i>MENUNGGU
                                </span>
                            </td>

                            <td class="text-end" style="white-space: nowrap;">
                                <div class="d-flex flex-wrap justify-content-end gap-1">
                                    <a href="{{ route('transactions.receipt', $trx) }}" class="btn btn-soft-secondary btn-sm" title="Lihat Struk">
                                        <i class="bi bi-receipt"></i> Struk
                                    </a>

                                    <form action="{{ route('transactions.cancel', $trx) }}" method="POST"
                                        onsubmit="return confirm('Batalkan pesanan ini? Stok buku akan dikembalikan.')">
                                        @csrf
                                        <button class="btn btn-soft-danger btn-sm" title="Batalkan Pesanan">
                                            <i class="bi bi-x-circle"></i> Batal
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-slate-600">
                                <i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>
                                Tidak ada pesanan yang sedang menunggu. Semua pesanan telah selesai atau belum ada pesanan baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BAGIAN 2: RIWAYAT PESANAN (SELESAI / DIBATALKAN) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle p-2 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-slate-900 mb-0">Riwayat Transaksi</h5>
                    <span class="small text-muted">Arsip transaksi yang telah lunas/selesai atau dibatalkan</span>
                </div>
            </div>
            <span class="badge bg-secondary bg-opacity-10 text-slate-700 px-3 py-2 rounded-pill fw-semibold">
                {{ $historyTransactions->total() }} Riwayat
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Daftar Buku Dibeli</th>
                        <th>Tanggal</th>
                        <th>Total Pembayaran</th>
                        <th>Metode &amp; Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($historyTransactions as $trx)
                        <tr class="align-middle">
                            <td style="width: 180px;">
                                <span class="rack-badge font-monospace text-slate-700">{{ $trx->kode_transaksi }}</span>
                                <div class="small text-slate-600 mt-1" style="font-size: 0.75rem;">
                                    <i class="bi bi-box-seam me-1"></i>{{ $trx->total_item }} item buku
                                </div>
                            </td>

                            <td>
                                @foreach($trx->items as $item)
                                    <div class="mb-1">
                                        <span class="fw-bold text-slate-900">{{ $item->book->judul ?? 'Buku' }}</span>
                                        <span class="badge bg-secondary bg-opacity-10 text-slate-700 ms-1 font-monospace">
                                            {{ $item->jumlah }}x @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($trx->alamat_pengiriman)
                                    <div class="small text-slate-600 mt-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-geo-alt-fill text-muted me-1"></i> {{ $trx->alamat_pengiriman }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-slate-700 fw-medium" style="white-space: nowrap;">
                                {{ $trx->tanggal_transaksi?->format('d-m-Y') }}
                            </td>

                            <td>
                                <div class="fw-extrabold {{ $trx->status === 'selesai' ? 'text-success' : 'text-slate-500' }}">
                                    Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                                </div>
                            </td>

                            <td>
                                <div class="small fw-semibold text-slate-800 mb-1">{{ $trx->metode_pembayaran }}</div>
                                @php
                                    $badgeClass = match($trx->status) {
                                        'selesai' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                        'dibatalkan' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                        default => 'bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50',
                                    };
                                    $iconClass = match($trx->status) {
                                        'selesai' => 'bi-check-circle-fill',
                                        'dibatalkan' => 'bi-x-circle-fill',
                                        default => 'bi-clock',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} px-2 py-1">
                                    <i class="bi {{ $iconClass }} me-1"></i>{{ strtoupper($trx->status) }}
                                </span>
                            </td>

                            <td class="text-end" style="white-space: nowrap;">
                                <a href="{{ route('transactions.receipt', $trx) }}" class="btn btn-soft-secondary btn-sm" title="Lihat Struk">
                                    <i class="bi bi-receipt"></i> Struk
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-slate-600">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada riwayat transaksi selesai atau dibatalkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($historyTransactions->hasPages())
        <div class="mt-4">
            {{ $historyTransactions->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection

@extends('layouts.app')

@section('title', 'Manajemen Transaksi Penjualan — BookStore')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-extrabold text-slate-900 mb-1">
                Manajemen Transaksi Penjualan
            </h2>
            <p class="text-slate-600 mb-0 small">
                Kelola pesanan pelanggan yang menunggu konfirmasi, pantau riwayat penjualan, dan catat transaksi kasir langsung.
            </p>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminTransactionModal">
            <i class="bi bi-plus-lg"></i> Input Penjualan Kasir
        </button>
    </div>

    {{-- Filter & Pencarian Transaksi Admin --}}
    <form method="GET" action="{{ route('transactions.index') }}" class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-slate-600">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                        placeholder="Cari kode transaksi, metode bayar, nama pembeli, nomor member, atau judul buku..."
                        value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="history_status" class="form-select text-slate-700">
                    <option value="">Semua Status Riwayat</option>
                    <option value="selesai" @selected(request('history_status') === 'selesai')>Selesai / Lunas</option>
                    <option value="dibatalkan" @selected(request('history_status') === 'dibatalkan')>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100">Filter</button>
                @if(request()->hasAny(['search', 'history_status']))
                    <a href="{{ route('transactions.index') }}" class="btn btn-soft-secondary" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- BAGIAN 1: PESANAN YANG SEDANG MENUNGGU KONFIRMASI (AKTIF) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle p-2 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-hourglass-split fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-slate-900 mb-0">Pesanan Masuk (Menunggu Konfirmasi)</h5>
                    <span class="small text-muted">Pesanan dari pembeli yang memerlukan verifikasi pembayaran &amp; persetujuan admin</span>
                </div>
            </div>
            <span class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning border-opacity-50 px-3 py-2 rounded-pill fw-bold">
                {{ $pendingTransactions->count() }} Menunggu Konfirmasi
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode &amp; Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Detail Buku Dibeli</th>
                        <th>Total &amp; Metode</th>
                        <th>Status</th>
                        <th class="text-end">Aksi Konfirmasi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pendingTransactions as $trx)
                        <tr>
                            <td style="width: 170px;">
                                <span class="rack-badge font-monospace text-primary fw-bold">{{ $trx->kode_transaksi }}</span>
                                <div class="small text-slate-600 mt-1">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $trx->tanggal_transaksi?->format('d-m-Y') }}
                                </div>
                            </td>

                            <td style="min-width: 180px;">
                                <div class="fw-bold text-slate-900">{{ $trx->user->name ?? '-' }}</div>
                                <div class="small text-slate-600 font-monospace" style="font-size: 0.75rem;">
                                    {{ $trx->user->nomor_anggota ?? '-' }}
                                </div>
                            </td>

                            <td>
                                @foreach($trx->items as $item)
                                    <div class="small mb-1">
                                        <strong class="text-slate-900">{{ $item->book->judul ?? 'Buku' }}</strong>
                                        <span class="text-slate-600">({{ $item->jumlah }}x @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }})</span>
                                    </div>
                                @endforeach
                                @if($trx->alamat_pengiriman)
                                    <div class="small text-slate-600 mt-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $trx->alamat_pengiriman }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="fw-extrabold text-primary fs-6">
                                    Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace">
                                        {{ $trx->metode_pembayaran }}
                                    </span>
                                    <span class="small text-slate-600 ms-1">({{ $trx->total_item }} eks)</span>
                                </div>
                            </td>

                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 px-2 py-1">
                                    <i class="bi bi-clock me-1"></i>MENUNGGU
                                </span>
                            </td>

                            <td class="text-end" style="white-space: nowrap;">
                                <div class="d-flex flex-wrap justify-content-end gap-1">
                                    <a href="{{ route('transactions.receipt', $trx) }}" class="btn btn-soft-secondary btn-sm" title="Lihat Struk">
                                        <i class="bi bi-receipt"></i> Struk
                                    </a>

                                    <form action="{{ route('transactions.approve', $trx) }}" method="POST"
                                        onsubmit="return confirm('Konfirmasi pembayaran transaksi {{ $trx->kode_transaksi }} ({{ $trx->metode_pembayaran }}) sebagai SELESAI / LUNAS?')">
                                        @csrf
                                        <button class="btn btn-soft-success btn-sm" title="Selesaikan Transaksi">
                                            <i class="bi bi-check2-circle"></i> Selesaikan
                                        </button>
                                    </form>

                                    <form action="{{ route('transactions.cancel', $trx) }}" method="POST"
                                        onsubmit="return confirm('Batalkan transaksi ini dan kembalikan stok buku?')">
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
                                <i class="bi bi-check2-all fs-3 text-success d-block mb-1"></i>
                                Tidak ada pesanan yang menunggu konfirmasi saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- BAGIAN 2: RIWAYAT TRANSAKSI PENJUALAN (SELESAI / DIBATALKAN) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle p-2 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-clock-history fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-slate-900 mb-0">Riwayat Transaksi Penjualan</h5>
                    <span class="small text-muted">Arsip seluruh transaksi yang telah lunas/selesai atau dibatalkan</span>
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
                        <th>Kode &amp; Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Detail Buku Dibeli</th>
                        <th>Total &amp; Metode</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($historyTransactions as $trx)
                        <tr>
                            <td style="width: 170px;">
                                <span class="rack-badge font-monospace text-slate-700">{{ $trx->kode_transaksi }}</span>
                                <div class="small text-slate-600 mt-1">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $trx->tanggal_transaksi?->format('d-m-Y') }}
                                </div>
                            </td>

                            <td style="min-width: 180px;">
                                <div class="fw-bold text-slate-900">{{ $trx->user->name ?? '-' }}</div>
                                <div class="small text-slate-600 font-monospace" style="font-size: 0.75rem;">
                                    {{ $trx->user->nomor_anggota ?? '-' }}
                                </div>
                            </td>

                            <td>
                                @foreach($trx->items as $item)
                                    <div class="small mb-1">
                                        <strong class="text-slate-900">{{ $item->book->judul ?? 'Buku' }}</strong>
                                        <span class="text-slate-600">({{ $item->jumlah }}x @ Rp {{ number_format($item->harga_satuan, 0, ',', '.') }})</span>
                                    </div>
                                @endforeach
                                @if($trx->alamat_pengiriman)
                                    <div class="small text-slate-600 mt-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-geo-alt-fill text-muted me-1"></i> {{ $trx->alamat_pengiriman }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="fw-extrabold {{ $trx->status === 'selesai' ? 'text-success' : 'text-slate-500' }}">
                                    Rp {{ number_format($trx->total_harga, 0, ',', '.') }}
                                </div>
                                <div class="mt-1">
                                    <span class="badge bg-secondary bg-opacity-10 text-slate-700 font-monospace">
                                        {{ $trx->metode_pembayaran }}
                                    </span>
                                    <span class="small text-slate-600 ms-1">({{ $trx->total_item }} eks)</span>
                                </div>
                            </td>

                            <td>
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
                                Belum ada riwayat transaksi penjualan.
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

    @include('admin.transactions.partials.create-modal')
@endsection

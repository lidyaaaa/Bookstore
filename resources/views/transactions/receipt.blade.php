@extends('layouts.app')

@section('title', 'Struk Pembelian - ' . $transaction->kode_transaksi)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Transaksi
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-printer-fill me-1"></i> Cetak Struk / Simpan PDF
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="text-center border-bottom pb-3 mb-3">
                <h4 class="fw-bold mb-0">BookStore</h4>
                <div class="small text-secondary">Struk Resmi Pembelian Buku</div>
                <div class="badge bg-dark mt-2 font-monospace">{{ $transaction->kode_transaksi }}</div>
            </div>

            <div class="row g-2 small mb-3">
                <div class="col-5 text-secondary">Nomor Member</div>
                <div class="col-7 fw-semibold text-end font-monospace">{{ $transaction->user->nomor_anggota ?? '-' }}</div>

                <div class="col-5 text-secondary">Nama Pembeli</div>
                <div class="col-7 fw-semibold text-end">{{ $transaction->user->name ?? '-' }}</div>

                <div class="col-5 text-secondary">Tanggal Transaksi</div>
                <div class="col-7 text-end">{{ $transaction->tanggal_transaksi?->format('d F Y') }}</div>

                <div class="col-5 text-secondary">Metode Pembayaran</div>
                <div class="col-7 fw-semibold text-end">{{ $transaction->metode_pembayaran }}</div>

                @if($transaction->alamat_pengiriman)
                    <div class="col-5 text-secondary">Alamat Pengiriman</div>
                    <div class="col-7 text-end">{{ $transaction->alamat_pengiriman }}</div>
                @endif

                <div class="col-5 text-secondary">Status Pesanan</div>
                <div class="col-7 text-end">
                    <span class="badge bg-{{ $transaction->status === 'selesai' ? 'success' : ($transaction->status === 'dibatalkan' ? 'danger' : 'warning text-dark') }}">
                        {{ strtoupper($transaction->status) }}
                    </span>
                </div>
            </div>

            <div class="p-3 bg-light rounded-3 mb-3">
                <div class="small fw-bold text-secondary mb-2">Rincian Buku yang Dibeli</div>
                @foreach($transaction->items as $item)
                    <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                        <div>
                            <div class="fw-bold text-slate-900">{{ $item->book->judul ?? 'Buku' }}</div>
                            <div class="small text-secondary">
                                {{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="fw-bold text-slate-900">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between align-items-center pt-2 mt-1">
                    <span class="fw-bold text-slate-900">Total Pembayaran ({{ $transaction->total_item }} item)</span>
                    <span class="fs-5 fw-extrabold text-primary">
                        Rp {{ number_format($transaction->total_harga, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            @if($transaction->catatan)
                <div class="small text-secondary mb-3">
                    <strong>Catatan:</strong> {{ $transaction->catatan }}
                </div>
            @endif

            <div class="small text-secondary text-center border-top pt-3">
                <p class="mb-1">
                    Terima kasih telah berbelanja di <strong>BookStore</strong>. Simpan struk ini sebagai bukti transaksi pembayaran yang sah.
                </p>
                <div class="text-muted" style="font-size: 0.75rem;">
                    Dicetak pada: {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

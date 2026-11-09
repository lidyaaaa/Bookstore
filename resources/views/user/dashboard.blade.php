@extends('layouts.app')

@section('title', 'Dashboard Pelanggan — BookStore')

@section('content')
    <div class="opac-header-surface p-4 p-lg-5 mb-4">
        <div class="row align-items-center justify-content-between g-3">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2"
                    style="background: #dbeafe; color: #1e40af; font-size: 0.76rem; font-weight: 700;">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>PORTAL PELANGGAN BOOKSTORE</span>
                </div>
                <h2 class="fw-extrabold text-slate-900 mb-1">
                    Halo, <span class="text-gradient-brand">{{ $user->name }}!</span>
                </h2>
                <p class="text-slate-600 mb-0 small">
                    Nomor Member: <strong class="font-monospace text-slate-900">{{ $user->nomor_anggota }}</strong>
                </p>
            </div>

            <div class="col-lg-5 d-flex flex-wrap justify-content-lg-end gap-2">
                <a href="{{ route('books.index') }}" class="btn btn-primary">
                    <i class="bi bi-shop"></i> Belanja Buku
                </a>
                <a href="{{ route('cart.index') }}" class="btn btn-soft-primary bg-white">
                    <i class="bi bi-cart3"></i> Keranjang ({{ $cartItems->sum('jumlah') }})
                </a>
            </div>
        </div>

        <div class="row g-3 mt-3 pt-3 border-top border-primary border-opacity-10">
            <div class="col-sm-4">
                <div class="p-3 rounded-4 bg-white bg-opacity-75 border border-white shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-slate-600 fw-medium">Isi Keranjang Saat Ini</div>
                        <div class="fw-extrabold text-primary fs-5">
                            {{ $cartItems->sum('jumlah') }} <span class="fs-6 fw-normal text-slate-600">Buku</span>
                        </div>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi bi-cart3 fs-5"></i>
                    </span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-4 bg-white bg-opacity-75 border border-white shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-slate-600 fw-medium">Total Riwayat Pesanan</div>
                        <div class="fw-extrabold text-slate-900 fs-5">
                            {{ $transactionCount }} <span class="fs-6 fw-normal text-slate-600">Transaksi</span>
                        </div>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-slate-700 p-2 rounded-3">
                        <i class="bi bi-receipt fs-5"></i>
                    </span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-4 bg-white bg-opacity-75 border border-white shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-slate-600 fw-medium">Total Belanja Selesai</div>
                        <div class="fw-extrabold text-success fs-5">
                            Rp {{ number_format($totalBelanja, 0, ',', '.') }}
                        </div>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-3">
                        <i class="bi bi-bag-check-fill fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-slate-900 mb-0">Pesanan Terbaru Saya</h6>
                    <a href="{{ route('transactions.index') }}" class="btn btn-soft-primary btn-sm">Semua Transaksi</a>
                </div>
                <div class="card-body p-4">
                    @forelse($recentTransactions as $trx)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-3 mb-2 rounded-4 border bg-light">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="rack-badge font-monospace">{{ $trx->kode_transaksi }}</span>
                                    <strong class="text-primary">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</strong>
                                    <span class="badge bg-secondary">{{ strtoupper($trx->status) }}</span>
                                </div>
                                <div class="small text-slate-600">
                                    Tanggal: {{ $trx->tanggal_transaksi?->format('d-m-Y') }} &bull;
                                    {{ $trx->total_item }} buku ({{ $trx->metode_pembayaran }})
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a href="{{ route('transactions.receipt', $trx) }}" class="btn btn-soft-secondary btn-sm">
                                    <i class="bi bi-receipt"></i> Struk Pembelian
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-600">
                            <i class="bi bi-bag fs-2 d-block mb-1 text-primary opacity-50"></i>
                            Anda belum memiliki transaksi pembelian.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Preview Kartu Member Digital --}}
        <div class="col-lg-4">
            <div class="card overflow-hidden">
                <div class="p-4 text-white" style="background: linear-gradient(135deg, #2563eb 0%, #4f46e5 55%, #7c3aed 100%);">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="small text-white text-opacity-75 fw-semibold" style="font-size: 0.72rem;">BOOKSTORE MEMBER CARD</div>
                            <h5 class="fw-extrabold mb-0">BookStore</h5>
                        </div>
                        <i class="bi bi-person-vcard fs-3 opacity-75"></i>
                    </div>

                    <div class="my-3">
                        <div class="small text-white text-opacity-75" style="font-size: 0.75rem;">Nomor Member</div>
                        <div class="fs-5 fw-bold font-monospace">{{ $user->nomor_anggota }}</div>
                    </div>

                    <div class="row g-2 small">
                        <div class="col-7">
                            <div class="text-white text-opacity-75" style="font-size: 0.72rem;">Nama Pelanggan</div>
                            <div class="fw-bold">{{ $user->name }}</div>
                        </div>
                        <div class="col-5">
                            <div class="text-white text-opacity-75" style="font-size: 0.72rem;">Status</div>
                            <div class="fw-bold">Member Umum</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="small text-slate-600 mb-3">{{ $accountInfo['message'] }}</p>
                    <a href="{{ route('member.card') }}" class="btn btn-soft-primary w-100">
                        <i class="bi bi-printer"></i> Buka &amp; Cetak Kartu Member
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

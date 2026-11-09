@extends('layouts.app')

@section('title', 'Kartu Member Digital - ' . $member->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-printer-fill me-1"></i> Cetak Kartu Member
            </button>
        </div>

        <div class="card border-0 shadow rounded-4 overflow-hidden">
            <div class="p-4 text-white" style="background: linear-gradient(135deg, #0f3460 0%, #174a84 60%, #2563eb 100%);">
                <div class="d-flex justify-content-between align-items-center border-bottom border-light border-opacity-25 pb-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-bag-check-fill fs-3"></i>
                        <div>
                            <h5 class="fw-bold mb-0">KARTU MEMBER BOOKSTORE</h5>
                            <div class="small text-white-50">BookStore &mdash; Digital Customer Card</div>
                        </div>
                    </div>
                    <span class="badge bg-{{ $accountInfo['color'] }} px-3 py-2">
                        {{ $accountInfo['status'] }}
                    </span>
                </div>

                <div class="row align-items-center g-3">
                    <div class="col-sm-8">
                        <div class="mb-2">
                            <div class="small text-white-50">Nomor Induk Member</div>
                            <div class="fs-4 fw-bold font-monospace">{{ $member->nomor_anggota }}</div>
                        </div>
                        <div class="mb-2">
                            <div class="small text-white-50">Nama Lengkap</div>
                            <div class="fs-5 fw-semibold">{{ $member->name }}</div>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="text-white-50">Tipe Akun</div>
                                <div class="fw-semibold">Pelanggan Umum</div>
                            </div>
                            <div class="col-6">
                                <div class="text-white-50">Terdaftar Sejak</div>
                                <div class="fw-semibold">{{ $member->created_at?->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-center">
                        <div class="bg-white text-dark rounded-3 p-3 shadow-sm">
                            <i class="bi bi-qr-code-scan display-4 d-block mb-1 text-primary"></i>
                            <div class="font-monospace fw-bold" style="font-size: 0.72rem;">{{ $member->nomor_anggota }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 bg-white">
                <h6 class="fw-bold mb-2">Informasi Penggunaan Kartu Member:</h6>
                <ol class="small text-secondary ps-3 mb-0">
                    <li>Kartu ini merupakan identitas resmi pelanggan terdaftar di BookStore.</li>
                    <li>Gunakan akun Anda untuk menambahkan buku ke keranjang dan melakukan transaksi pembelian online maupun di kasir toko.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

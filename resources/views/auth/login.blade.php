@extends('layouts.app')

@section('title', 'Masuk Akun — BookStore')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 72vh;">
    <div class="col-md-6 col-lg-4">
        <div class="card p-4 p-sm-5">
            <div class="text-center mb-4">
                <span class="brand-logo-box mb-3" style="width: 48px; height: 48px; border-radius: 14px;">
                    <i class="bi bi-bag-check-fill fs-4"></i>
                </span>
                <h4 class="fw-extrabold text-slate-900 mb-1">Selamat Datang Kembali</h4>
                <p class="text-slate-600 small mb-0">
                    Masuk untuk berbelanja buku dan mengelola pesanan Anda.
                </p>
            </div>

            <form action="{{ route('login.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-bold text-slate-700">Alamat Email</label>
                    <input type="email" name="email" class="form-control"
                        placeholder="nama@email.com" value="{{ old('email') }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-slate-700">Password</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Masukkan password Anda" required>
                </div>

                <button class="btn btn-primary w-100 py-2">
                    Masuk ke BookStore <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center small text-slate-600">
                Belum punya akun pelanggan?
                <a href="{{ route('register') }}" class="fw-bold text-primary text-decoration-none">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>
@endsection

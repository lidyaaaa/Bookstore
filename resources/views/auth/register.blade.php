@extends('layouts.app')

@section('title', 'Daftar Akun Pelanggan — BookStore')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">Daftar Akun Baru</h3>
                <p class="text-secondary small mb-4">Buat akun pelanggan untuk mulai berbelanja buku secara online.</p>

                <form action="{{ route('register.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-medium">Nama Lengkap</label>
                        <input type="text" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Contoh: Budi Santoso"
                            value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">No. WhatsApp / Telepon</label>
                        <input type="text" name="telepon"
                            class="form-control @error('telepon') is-invalid @enderror"
                            placeholder="0812xxxxxxxx"
                            inputmode="numeric" pattern="\d{10,13}" minlength="10" maxlength="13"
                            oninput="this.value = this.value.replace(/\D/g, '')"
                            title="Nomor telepon harus angka 10–13 digit"
                            value="{{ old('telepon') }}" required>
                        @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="nama@email.com"
                            value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Password</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Min. 8 karakter (huruf, angka & simbol)"
                            minlength="8"
                            pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d\s]).{8,}"
                            title="Password minimal 8 karakter dan wajib mengandung kombinasi huruf, angka, serta simbol"
                            required>
                        <div class="form-text small">Wajib kombinasi minimal 8 karakter: huruf, angka, dan simbol (misal: <code>@</code>, <code>#</code>, <code>!</code>).</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="form-control"
                            placeholder="Ulangi password" required>
                    </div>

                    <button class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-person-plus-fill me-1"></i> Daftar Sekarang
                    </button>
                </form>

                <p class="mt-3 mb-0 text-center small">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="fw-semibold">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

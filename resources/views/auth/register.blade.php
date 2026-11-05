@extends('layouts.app')

@section('title', 'Daftar Keanggotaan Perpustakaan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">Daftar Anggota Baru</h3>
                <p class="text-secondary small mb-4">Nomor anggota dan kartu perpustakaan digital dibuat otomatis.</p>

                <form action="{{ route('register.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-medium">Nama Lengkap</label>
                        <input type="text" name="name"
                            class="form-control"
                            placeholder="Contoh: Budi Santoso"
                            value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">NISN</label>
                        <input type="text" name="nisn"
                            class="form-control @error('nisn') is-invalid @enderror"
                            placeholder="10 digit angka"
                            inputmode="numeric" pattern="\d{10}" minlength="10" maxlength="10"
                            oninput="this.value = this.value.replace(/\D/g, '')"
                            title="NISN harus 10 digit angka"
                            value="{{ old('nisn') }}" required>
                        @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">Kelas / Jurusan</label>
                            <select name="kelas_jabatan" class="form-select @error('kelas_jabatan') is-invalid @enderror" required>
                                <option value="" disabled @selected(! old('kelas_jabatan'))>-- Pilih Kelas --</option>
                                @foreach(\App\Models\User::KELAS_OPTIONS as $kelas)
                                    <option value="{{ $kelas }}" @selected(old('kelas_jabatan') === $kelas)>{{ $kelas }}</option>
                                @endforeach
                            </select>
                            @error('kelas_jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-medium">No. WhatsApp / Telepon</label>
                            <input type="text" name="telepon"
                                class="form-control @error('telepon') is-invalid @enderror"
                                placeholder="0812xxxx"
                                inputmode="numeric" pattern="\d{10,13}" minlength="10" maxlength="13"
                                oninput="this.value = this.value.replace(/\D/g, '')"
                                title="Nomor telepon harus angka 10–13 digit"
                                value="{{ old('telepon') }}" required>
                            @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="email"
                            class="form-control"
                            placeholder="nama@sekolah.sch.id"
                            value="{{ old('email') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Password</label>
                        <input type="password" name="password"
                            class="form-control" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="form-control" required>
                    </div>

                    <button class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-person-plus-fill me-1"></i> Daftar Keanggotaan
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
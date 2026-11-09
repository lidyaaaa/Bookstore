@extends('layouts.app')

@section('title', 'Daftar Pengguna Terdaftar — BookStore')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-extrabold text-slate-900 mb-1">
                Daftar Pengguna &amp; Pelanggan Terdaftar
            </h2>
            <p class="text-slate-600 mb-0 small">
                Data seluruh akun pengguna yang telah terdaftar di BookStore beserta rincian aktivitas belanjanya.
            </p>
        </div>

        <div class="d-flex gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-semibold">
                <i class="bi bi-people-fill me-1"></i> {{ $totalMembers }} Pelanggan
            </span>
            <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 px-3 py-2 rounded-pill fw-semibold">
                <i class="bi bi-shield-lock-fill me-1"></i> {{ $totalAdmins }} Admin
            </span>
        </div>
    </div>

    {{-- Filter & Pencarian User --}}
    <form method="GET" action="{{ route('users.index') }}" class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-slate-600">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                        placeholder="Cari berdasarkan nama, email, nomor member, atau no. telepon..."
                        value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select text-slate-700">
                    <option value="">Semua Peran (Role)</option>
                    <option value="user" @selected(request('role') === 'user')>Pelanggan (User)</option>
                    <option value="admin" @selected(request('role') === 'admin')>Administrator</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100">Filter</button>
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('users.index') }}" class="btn btn-soft-secondary" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabel Pengguna --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Identitas Pengguna</th>
                        <th>Kontak</th>
                        <th>Peran (Role)</th>
                        <th>Aktivitas Belanja</th>
                        <th>Tanggal Terdaftar</th>
                        <th class="text-end">Kartu Member</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="text-slate-500 font-monospace small" style="width: 50px;">
                                {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                            </td>

                            <td style="min-width: 200px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm"
                                        style="width: 40px; height: 40px; background: {{ $user->role === 'admin' ? '#1e293b' : '#3b82f6' }}; flex-shrink: 0;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-slate-900">{{ $user->name }}</div>
                                        <span class="rack-badge font-monospace text-slate-600 mt-1" style="font-size: 0.72rem;">
                                            {{ $user->nomor_anggota }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="text-slate-800 small fw-medium">
                                    <i class="bi bi-envelope text-muted me-1"></i> {{ $user->email }}
                                </div>
                                @if($user->telepon)
                                    <div class="text-slate-600 small mt-1">
                                        <i class="bi bi-telephone text-muted me-1"></i> {{ $user->telepon }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-dark px-2 py-1">
                                        <i class="bi bi-shield-lock me-1"></i>Administrator
                                    </span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                        <i class="bi bi-person me-1"></i>Pelanggan
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="small fw-semibold text-slate-900">
                                    {{ $user->transactions_count }} Transaksi
                                </div>
                                <div class="small text-muted mt-1">
                                    Total: <strong>Rp {{ number_format((float) ($user->total_belanja ?? 0), 0, ',', '.') }}</strong>
                                </div>
                            </td>

                            <td class="text-slate-700 small" style="white-space: nowrap;">
                                {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}
                            </td>

                            <td class="text-end" style="white-space: nowrap;">
                                <a href="{{ route('member.card', $user) }}" class="btn btn-soft-secondary btn-sm" title="Lihat Kartu Member">
                                    <i class="bi bi-person-vcard me-1"></i> Kartu Member
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-slate-600">
                                <i class="bi bi-people fs-2 d-block mb-2 text-muted"></i>
                                Tidak ditemukan data pengguna yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection

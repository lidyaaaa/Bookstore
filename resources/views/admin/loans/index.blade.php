@extends('layouts.app')

@section('title', 'Manajemen Transaksi Sirkulasi â€” SIMPerpus')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-extrabold text-slate-900 mb-1">
                Transaksi Sirkulasi & Sanksi
            </h2>
            <p class="text-slate-600 mb-0 small">
                Kelola penyerahan buku, pengembalian, perhitungan sanksi keterlambatan otomatis, dan laporan buku hilang.
            </p>
        </div>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adminLoanModal">
            <i class="bi bi-plus-lg"></i> Input Peminjaman Loket
        </button>
    </div>

    {{-- Filter & Pencarian Sirkulasi Admin --}}
    <form method="GET" action="{{ route('loans.index') }}" class="card p-3 mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-slate-600">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                        placeholder="Cari kode transaksi, nama siswa, nomor anggota, atau judul buku..."
                        value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select text-slate-700">
                    <option value="">Semua Status Sirkulasi</option>
                    <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu Persetujuan</option>
                    <option value="dipinjam" @selected(request('status') === 'dipinjam')>Sedang Dipinjam</option>
                    <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat / Menunggak (Overdue)</option>
                    <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Sudah Dikembalikan</option>
                    <option value="hilang" @selected(request('status') === 'hilang')>Buku Hilang (Lost)</option>
                    <option value="dibatalkan" @selected(request('status') === 'dibatalkan')>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100">Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('loans.index') }}" class="btn btn-soft-secondary" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>


    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 22%;">Peminjam</th>
                        <th>Daftar Buku Dipinjam</th>
                        <th class="text-end" style="width: 16%;">Aksi Transaksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($borrowers as $borrower)
                        @php
                            $pendingCount = $borrower->loans->where('status', 'menunggu')->count();
                            $visibleLoans = $borrower->loans->take(5);
                            $hiddenLoans = $borrower->loans->slice(5);
                        @endphp
                        <tr>
                            <td class="align-top">
                                <div class="fw-bold text-slate-900">{{ $borrower->name }}</div>
                                <div class="small text-slate-600 font-monospace" style="font-size: 0.75rem;">
                                    {{ $borrower->nomor_anggota ?? '-' }}
                                </div>
                                <div class="small text-slate-600" style="font-size: 0.75rem;">
                                    {{ $borrower->kelas_jabatan ?? '-' }} &bull; {{ $borrower->loans->count() }} buku
                                </div>
                            </td>

                            <td class="align-top">
                                @foreach($visibleLoans as $loan)
                                    @include('admin.loans.partials.loan-item', ['loan' => $loan])
                                @endforeach

                                @if($hiddenLoans->isNotEmpty())
                                    <div class="collapse" id="moreLoans-{{ $borrower->id }}">
                                        @foreach($hiddenLoans as $loan)
                                            @include('admin.loans.partials.loan-item', ['loan' => $loan])
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-link btn-sm p-0"
                                        data-bs-toggle="collapse" data-bs-target="#moreLoans-{{ $borrower->id }}"
                                        onclick="this.textContent = this.textContent.includes('Selengkapnya') ? 'Sembunyikan' : 'Selengkapnya (+{{ $hiddenLoans->count() }} buku)'">
                                        Selengkapnya (+{{ $hiddenLoans->count() }} buku)
                                    </button>
                                @endif
                            </td>

                            <td class="text-end align-top">
                                @if($pendingCount > 0)
                                    <form action="{{ route('loans.approve-all', $borrower) }}" method="POST"
                                        onsubmit="return confirm('Setujui semua ({{ $pendingCount }}) pengajuan milik {{ $borrower->name }}? Masa pinjam 3 minggu dimulai hari ini.')">
                                        @csrf
                                        <button class="btn btn-soft-success btn-sm">
                                            <i class="bi bi-check2-all"></i> Setujui Semua ({{ $pendingCount }})
                                        </button>
                                    </form>
                                @else
                                    <span class="small text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-slate-600">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Belum ada data transaksi sirkulasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $borrowers->links('pagination::bootstrap-5') }}
    </div>

    @include('admin.loans.partials.create-modal')
@endsection

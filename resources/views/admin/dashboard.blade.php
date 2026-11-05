@extends('layouts.app')

@section('title', 'Dashboard Pustakawan — SIMPerpus')

@section('content')
    {{-- Header Dashboard Admin / Pustakawan (Airy Aurora Surface) --}}
    <div class="opac-header-surface p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2"
                    style="background: #dbeafe; color: #1e40af; font-size: 0.76rem; font-weight: 700;">
                    <i class="bi bi-shield-check"></i>
                    <span>PANEL KENDALI PUSTAKAWAN</span>
                </div>
                <h2 class="fw-extrabold text-slate-900 mb-1">
                    Monitoring <span class="text-gradient-brand">Sirkulasi & Koleksi</span>
                </h2>
                <p class="text-slate-600 mb-0 small">
                    Pantau ketersediaan buku di rak, persetujuan pinjaman, keterlambatan (overdue lock), dan sanksi akun siswa.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('books.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Tambah Koleksi
                </a>
                <a href="{{ route('loans.index') }}" class="btn btn-soft-primary bg-white">
                    <i class="bi bi-arrow-left-right"></i> Transaksi Sirkulasi
                </a>
                <a href="{{ route('reports.index') }}" class="btn btn-soft-secondary bg-white">
                    <i class="bi bi-printer"></i> Laporan USK
                </a>
            </div>
        </div>
    </div>

    {{-- Kartu Statistik Utama --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 p-3">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-600 small fw-bold">KOLEKSI & KETERSEDIAAN</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                            <i class="bi bi-bookshelf fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-extrabold text-slate-900 mb-1">{{ $stats['total_judul'] }} Judul</h3>
                    <div class="small text-slate-600">
                        Ketersediaan: <strong class="text-success">{{ $stats['stok_tersedia'] }}</strong> dari {{ $stats['total_eksemplar'] }} fisik
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 p-3">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-600 small fw-bold">SIRKULASI AKTIF</span>
                        <span class="badge bg-info bg-opacity-10 text-primary p-2 rounded-3">
                            <i class="bi bi-journal-arrow-up fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-extrabold text-slate-900 mb-1">{{ $stats['sedang_dipinjam'] }} Dipinjam</h3>
                    <div class="small text-slate-600">
                        Menunggu Persetujuan: <strong class="text-warning">{{ $stats['menunggu_persetujuan'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 p-3">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-600 small fw-bold">OVERDUE LOCK</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger p-2 rounded-3">
                            <i class="bi bi-exclamation-octagon fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-extrabold text-danger mb-1">{{ $stats['terlambat_aktif'] }} Pinjaman</h3>
                    <div class="small text-slate-600">
                        Lewat jatuh tempo &amp; mengunci akun
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 p-3">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-slate-600 small fw-bold">SANKSI & BUKU HILANG</span>
                        <span class="badge bg-warning bg-opacity-10 text-dark p-2 rounded-3">
                            <i class="bi bi-slash-circle fs-5"></i>
                        </span>
                    </div>
                    <h3 class="fw-extrabold text-slate-900 mb-1">{{ $stats['siswa_disanksi'] }} Siswa</h3>
                    <div class="small text-slate-600">
                        Koleksi Hilang: <strong class="text-danger">{{ $stats['buku_hilang'] }} eks</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Monitoring Sanksi & Overdue Lock Siswa --}}
        <div class="col-lg-7">
            <div class="card h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold text-slate-900 mb-0">
                        <i class="bi bi-shield-lock text-danger me-1"></i> Monitoring Sanksi & Tunggakan Siswa
                    </h6>
                    <small class="text-slate-600">Daftar anggota yang sedang terkena Overdue Lock atau Skorsing Suspend</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Anggota</th>
                                <th>Status Akun</th>
                                <th>Keterangan / Berakhir</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sanctionedMembers as $member)
                                @php
                                    $info = $member->accountStatusInfo();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-slate-900">{{ $member->name }}</div>
                                        <div class="small text-slate-600 font-monospace" style="font-size: 0.74rem;">
                                            {{ $member->nomor_anggota }} &bull; {{ $member->kelas_jabatan ?? 'Siswa' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $info['color'] }} bg-opacity-10 text-{{ $info['color'] === 'warning' ? 'dark' : $info['color'] }} border border-{{ $info['color'] }} border-opacity-25">
                                            {{ $info['status'] }}
                                        </span>
                                    </td>
                                    <td class="small">
                                        @if($member->hasOverdueLock())
                                            <span class="text-danger fw-semibold">Terkunci sampai buku menunggak dikembalikan</span>
                                        @elseif($member->isSuspended())
                                            <div class="fw-semibold text-slate-900">s/d {{ $member->suspended_until->format('d-m-Y') }} ({{ $member->remainingSuspendDays() }} hr)</div>
                                            <div class="text-slate-600">{{ $member->alasan_suspend }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($member->isSuspended())
                                            <form action="{{ route('users.clear-sanction', $member) }}" method="POST"
                                                onsubmit="return confirm('Cabut sanksi pembekuan akun untuk {{ addslashes($member->name) }}?')">
                                                @csrf
                                                <button class="btn btn-soft-success btn-sm">
                                                    Cabut Sanksi
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('loans.index', ['status' => 'terlambat']) }}" class="btn btn-soft-danger btn-sm">
                                                Tagih Buku
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-600 py-5">
                                        <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                                        Tidak ada siswa yang sedang terkena sanksi atau menunggak buku.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Transaksi Sirkulasi Terbaru --}}
        <div class="col-lg-5">
            <div class="card h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-slate-900 mb-0">Sirkulasi Terbaru</h6>
                    <a href="{{ route('loans.index') }}" class="btn btn-soft-primary btn-sm">Lihat Semua</a>
                </div>
                <div class="card-body px-4 py-2">
                    <ul class="list-group list-group-flush">
                        @forelse($recentLoans as $loan)
                            <li class="list-group-item px-0 py-3 d-flex justify-content-between align-items-start border-bottom-0 border-top">
                                <div>
                                    <div class="fw-bold text-slate-900">{{ $loan->book->judul ?? '-' }}</div>
                                    <div class="small text-slate-600">
                                        {{ $loan->user->name ?? '-' }} &bull; <code class="text-primary">{{ $loan->kode_transaksi }}</code>
                                    </div>
                                </div>
                                <span class="badge bg-secondary bg-opacity-10 text-slate-700 border">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item px-0 text-center text-slate-600 py-4 border-0">
                                Belum ada transaksi sirkulasi.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

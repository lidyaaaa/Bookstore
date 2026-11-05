@extends('layouts.app')

@section('title', 'Dashboard Anggota — SIMPerpus')

@section('content')
    {{-- Alert / Banner Peringatan Sanksi Suspend atau Overdue Lock --}}
    @if($user->hasOverdueLock())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 mb-4 d-flex align-items-start gap-3">
            <i class="bi bi-lock-fill fs-2 text-danger"></i>
            <div>
                <h5 class="fw-bold mb-1">AKUN TERKUNCI OTOMATIS (OVERDUE LOCK)</h5>
                <p class="mb-2 small">
                    Anda memiliki pinjaman buku yang telah melewati tanggal jatuh tempo pengembalian.
                    Sesuai peraturan perpustakaan, akun Anda <strong>dikunci dari peminjaman baru tanpa batas waktu</strong> sampai buku yang menunggak dikembalikan ke loket sirkulasi.
                </p>
                <a href="{{ route('loans.index') }}" class="btn btn-danger btn-sm">
                    Lihat Buku yang Menunggak
                </a>
            </div>
        </div>
    @elseif($user->isSuspended())
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-4 d-flex align-items-start gap-3">
            <i class="bi bi-slash-circle-fill fs-2 text-danger"></i>
            <div>
                <h5 class="fw-bold mb-1 text-slate-900">AKUN SEDANG DIBEKUKAN SANKSI (SUSPEND)</h5>
                <p class="mb-1 text-slate-700 small">
                    Hak peminjaman buku Anda dibekukan hingga <strong>{{ $user->suspended_until->format('d F Y') }}</strong>
                    (sisa <strong>{{ $user->remainingSuspendDays() }} hari</strong>).
                </p>
                <div class="small text-slate-600">
                    <strong>Catatan Sanksi:</strong> {{ $user->alasan_suspend ?? 'Keterlambatan pengembalian / Buku hilang' }}
                </div>
            </div>
        </div>
    @endif

    {{-- Header Fleksibel Siswa (Aurora Mesh Surface) --}}
    <div class="opac-header-surface p-4 p-lg-5 mb-4">
        <div class="row align-items-center justify-content-between g-3">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2"
                    style="background: #dbeafe; color: #1e40af; font-size: 0.76rem; font-weight: 700;">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>PORTAL ANGGOTA PERPUSTAKAAN</span>
                </div>
                <h2 class="fw-extrabold text-slate-900 mb-1">
                    Halo, <span class="text-gradient-brand">{{ $user->name }}!</span>
                </h2>
                <p class="text-slate-600 mb-0 small">
                    Nomor Anggota: <strong class="font-monospace text-slate-900">{{ $user->nomor_anggota }}</strong>
                    &bull; {{ $user->kelas_jabatan ?? 'Anggota Aktif' }}
                </p>
            </div>

            <div class="col-lg-5 d-flex flex-wrap justify-content-lg-end gap-2">
                <a href="{{ route('books.index') }}" class="btn btn-primary">
                    <i class="bi bi-compass"></i> Eksplorasi Katalog OPAC
                </a>
                <a href="{{ route('member.card') }}" class="btn btn-soft-secondary bg-white">
                    <i class="bi bi-person-vcard text-primary"></i> Cetak Kartu Anggota
                </a>
            </div>
        </div>

        <div class="row g-3 mt-3 pt-3 border-top border-primary border-opacity-10">
            <div class="col-sm-4">
                <div class="p-3 rounded-4 bg-white bg-opacity-75 border border-white shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-slate-600 fw-medium">Status Akun</div>
                        <div class="fw-bold text-slate-900">{{ $accountInfo['status'] }}</div>
                    </div>
                    <span class="badge bg-{{ $accountInfo['color'] }} bg-opacity-10 text-{{ $accountInfo['color'] === 'warning' ? 'dark' : $accountInfo['color'] }} p-2 rounded-3">
                        <i class="bi {{ $accountInfo['icon'] }} fs-5"></i>
                    </span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-4 bg-white bg-opacity-75 border border-white shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-slate-600 fw-medium">Kuota Pinjaman Aktif</div>
                        <div class="fw-extrabold text-primary fs-5">
                            {{ $activeLoans->count() }} / {{ \App\Models\User::MAX_ACTIVE_LOANS }} <span class="fs-6 fw-normal text-slate-600">Buku</span>
                        </div>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-3">
                        <i class="bi bi-journal-bookmark fs-5"></i>
                    </span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 rounded-4 bg-white bg-opacity-75 border border-white shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-slate-600 fw-medium">Total Riwayat Pinjam</div>
                        <div class="fw-extrabold text-slate-900 fs-5">
                            {{ $loanHistoryCount }} <span class="fs-6 fw-normal text-slate-600">Transaksi</span>
                        </div>
                    </div>
                    <span class="badge bg-secondary bg-opacity-10 text-slate-700 p-2 rounded-3">
                        <i class="bi bi-clock-history fs-5"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Daftar Pinjaman Aktif Siswa --}}
            <div class="card h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-slate-900 mb-0">Pinjaman Aktif Saat Ini</h6>
                    <a href="{{ route('loans.index') }}" class="btn btn-soft-primary btn-sm">Riwayat Lengkap</a>
                </div>
                <div class="card-body p-4">
                    @forelse($activeLoans as $loan)
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center p-3 mb-2 rounded-4 border {{ $loan->isOverdue() ? 'border-danger bg-danger bg-opacity-10' : 'bg-light' }}">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="rack-badge font-monospace">{{ $loan->kode_transaksi }}</span>
                                    <strong class="text-slate-900">{{ $loan->book->judul }}</strong>
                                </div>
                                <div class="small text-slate-600">
                                    Pinjam: {{ $loan->tanggal_pinjam->format('d-m-Y') }} &bull;
                                    Jatuh Tempo: <strong class="{{ $loan->isOverdue() ? 'text-danger' : 'text-slate-900' }}">{{ $loan->tanggal_kembali->format('d-m-Y') }}</strong>
                                    @if($loan->isOverdue())
                                        <span class="badge bg-danger ms-1">Terlambat {{ $loan->late_days }} hari!</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <a href="{{ route('loans.receipt', $loan) }}" class="btn btn-soft-secondary btn-sm">
                                    <i class="bi bi-info-circle"></i> Detail
                                </a>
                                @if($loan->canBeExtended())
                                    <form action="{{ route('loans.extend', $loan) }}" method="POST"
                                        onsubmit="return confirm('Perpanjang masa pinjam buku ini selama 7 hari (kesempatan maksimal 1x)?')">
                                        @csrf
                                        <button class="btn btn-soft-primary btn-sm">
                                            <i class="bi bi-calendar-plus"></i> Perpanjang +7 Hari
                                        </button>
                                    </form>
                                @elseif($loan->status === 'dipinjam' && $loan->perpanjangan_ke >= \App\Models\Loan::MAX_EXTENSIONS)
                                    <button class="btn btn-soft-secondary btn-sm opacity-75" disabled>
                                        <i class="bi bi-check2-circle text-success"></i> Sudah Diperpanjang (1x)
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-600">
                            <i class="bi bi-journal-check fs-2 d-block mb-1 text-primary opacity-50"></i>
                            Anda tidak memiliki pinjaman buku aktif saat ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Preview Kartu Anggota Digital Modern --}}
        <div class="col-lg-4">
            <div class="card overflow-hidden">
                <div class="p-4 text-white" style="background: linear-gradient(135deg, #2563eb 0%, #4f46e5 55%, #7c3aed 100%);">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="small text-white text-opacity-75 fw-semibold" style="font-size: 0.72rem;">DIGITAL MEMBER CARD</div>
                            <h5 class="fw-extrabold mb-0">SIMPerpus</h5>
                        </div>
                        <i class="bi bi-person-vcard fs-3 opacity-75"></i>
                    </div>

                    <div class="my-3">
                        <div class="small text-white text-opacity-75" style="font-size: 0.75rem;">Nomor Induk Anggota</div>
                        <div class="fs-5 fw-bold font-monospace">{{ $user->nomor_anggota }}</div>
                    </div>

                    <div class="row g-2 small">
                        <div class="col-7">
                            <div class="text-white text-opacity-75" style="font-size: 0.72rem;">Nama Anggota</div>
                            <div class="fw-bold">{{ $user->name }}</div>
                        </div>
                        <div class="col-5">
                            <div class="text-white text-opacity-75" style="font-size: 0.72rem;">Kelas / Unit</div>
                            <div class="fw-bold">{{ $user->kelas_jabatan ?? 'Siswa' }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="small text-slate-600 mb-3">{{ $accountInfo['message'] }}</p>
                    <a href="{{ route('member.card') }}" class="btn btn-soft-primary w-100">
                        <i class="bi bi-printer"></i> Buka & Cetak Kartu Anggota
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

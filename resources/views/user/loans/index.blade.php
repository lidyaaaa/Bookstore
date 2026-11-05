@extends('layouts.app')

@section('title', 'Peminjaman Saya — SIMPerpus')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-extrabold text-slate-900 mb-1">
                Riwayat & Status Peminjaman
            </h2>
            <p class="text-slate-600 mb-0 small">
                Pantau jatuh tempo (3 minggu), perpanjangan mandiri (+7 hari maks. 1x), dan cetak struk peminjaman.
            </p>
        </div>

        <a href="{{ route('books.index') }}" class="btn btn-primary">
            <i class="bi bi-compass"></i> Cari & Pinjam Buku Baru
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kode Transaksi</th>
                        <th>Koleksi Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status & Sanksi</th>
                        <th class="text-end">Aksi Peminjaman</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>
                                <span class="rack-badge font-monospace">{{ $loan->kode_transaksi }}</span>
                                @if($loan->perpanjangan_ke > 0)
                                    <div class="mt-1">
                                        <span class="badge bg-info bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.7rem;">
                                            Diperpanjang {{ $loan->perpanjangan_ke }}x
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="fw-bold text-slate-900">{{ $loan->book->judul ?? '-' }}</div>
                                <div class="small text-slate-600" style="font-size: 0.78rem;">
                                    Rak: <strong class="text-slate-700">{{ $loan->book->call_number ?? 'Umum' }}</strong>
                                    &bull; ISBN: {{ $loan->book->isbn ?? '-' }}
                                </div>
                            </td>

                            <td class="text-slate-700 fw-medium">{{ $loan->tanggal_pinjam?->format('d-m-Y') }}</td>

                            <td>
                                <div class="fw-bold {{ $loan->isOverdue() ? 'text-danger' : 'text-slate-900' }}">
                                    {{ $loan->tanggal_kembali?->format('d-m-Y') ?? '-' }}
                                </div>
                                @if($loan->tanggal_dikembalikan)
                                    <div class="small text-slate-600" style="font-size: 0.75rem;">
                                        Kembali: {{ $loan->tanggal_dikembalikan->format('d-m-Y') }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                @include('loans.partials.status-badge', ['loan' => $loan])
                            </td>

                            <td class="text-end">
                                <div class="d-flex flex-wrap justify-content-end gap-1">
                                    <a href="{{ route('loans.receipt', $loan) }}" class="btn btn-soft-secondary btn-sm" title="Info / Detail Peminjaman">
                                        <i class="bi bi-info-circle"></i> Detail
                                    </a>

                                    {{-- Aksi Perpanjangan Mandiri (Khusus Siswa, Maksimal 1x +7 Hari sebelum jatuh tempo) --}}
                                    @if($loan->status === 'dipinjam')
                                        @if($loan->canBeExtended())
                                            <form action="{{ route('loans.extend', $loan) }}" method="POST"
                                                onsubmit="return confirm('Perpanjang masa pinjam 7 hari? Perpanjangan hanya dapat dilakukan 1 kali.')">
                                                @csrf
                                                <button class="btn btn-soft-primary btn-sm">
                                                    <i class="bi bi-calendar-plus"></i> Perpanjang +7 Hari
                                                </button>
                                            </form>
                                        @elseif($loan->perpanjangan_ke >= \App\Models\Loan::MAX_EXTENSIONS)
                                            <button class="btn btn-soft-secondary btn-sm opacity-75" disabled title="Batas maksimal 1 kali perpanjangan telah digunakan">
                                                <i class="bi bi-check2-circle text-success"></i> Sudah Diperpanjang (1x)
                                            </button>
                                        @endif

                                        <form action="{{ route('loans.lost', $loan) }}" method="POST"
                                            onsubmit="return confirm('PERINGATAN: Melaporkan buku HILANG akan membekukan akun Anda selama 30 hari. Lanjutkan?')">
                                            @csrf
                                            <button class="btn btn-soft-danger btn-sm">
                                                <i class="bi bi-exclamation-octagon"></i> Lapor Hilang
                                            </button>
                                        </form>
                                    @endif

                                    @if($loan->status === 'menunggu')
                                        <form action="{{ route('loans.cancel', $loan) }}" method="POST"
                                            onsubmit="return confirm('Batalkan pengajuan peminjaman ini?')">
                                            @csrf
                                            <button class="btn btn-soft-danger btn-sm">
                                                <i class="bi bi-x-circle"></i> Batal
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-slate-600">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Anda belum memiliki riwayat transaksi peminjaman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $loans->links('pagination::bootstrap-5') }}
    </div>
@endsection

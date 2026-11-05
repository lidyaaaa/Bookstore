<div class="d-flex flex-column flex-lg-row justify-content-between gap-2 p-2 mb-2 rounded-3 border {{ $loan->isOverdue() ? 'border-danger bg-danger bg-opacity-10' : 'bg-light' }}">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="rack-badge font-monospace">{{ $loan->kode_transaksi }}</span>
            <strong class="text-slate-900">{{ $loan->book->judul ?? '-' }}</strong>
            @include('loans.partials.status-badge', ['loan' => $loan])
        </div>
        <div class="small text-slate-600" style="font-size: 0.75rem;">
            Rak: {{ $loan->book->call_number ?? 'Umum' }} &bull;
            Pinjam: {{ $loan->tanggal_pinjam?->format('d-m-Y') }} &bull;
            Jatuh Tempo: <strong class="{{ $loan->isOverdue() ? 'text-danger' : 'text-slate-900' }}">{{ $loan->tanggal_kembali?->format('d-m-Y') ?? '-' }}</strong>
            @if($loan->tanggal_dikembalikan)
                &bull; Kembali: {{ $loan->tanggal_dikembalikan->format('d-m-Y') }}
            @endif
            @if($loan->perpanjangan_ke > 0)
                &bull; Diperpanjang {{ $loan->perpanjangan_ke }}x
            @endif
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-start justify-content-lg-end gap-1">
        <a href="{{ route('loans.receipt', $loan) }}" class="btn btn-soft-secondary btn-sm" title="Info / Detail Peminjaman">
            <i class="bi bi-info-circle"></i> Detail
        </a>

        @if($loan->status === 'menunggu')
            <form action="{{ route('loans.cancel', $loan) }}" method="POST"
                onsubmit="return confirm('Batalkan pengajuan peminjaman ini?')">
                @csrf
                <button class="btn btn-soft-danger btn-sm"><i class="bi bi-x-circle"></i> Batal</button>
            </form>
        @endif

        @if($loan->status === 'dipinjam')
            <form action="{{ route('loans.return', $loan) }}" method="POST"
                onsubmit="return confirm('Proses pengembalian buku ini? Sistem akan otomatis menghitung keterlambatan dan sanksi bila melewati jatuh tempo.')">
                @csrf
                <button class="btn btn-soft-success btn-sm"><i class="bi bi-box-arrow-in-down-left"></i> Kembalikan</button>
            </form>

            <form action="{{ route('loans.lost', $loan) }}" method="POST"
                onsubmit="return confirm('PERINGATAN: Menandai buku HILANG akan mengurangi total fisik buku dan membekukan akun peminjam selama 30 hari. Lanjutkan?')">
                @csrf
                <button class="btn btn-soft-danger btn-sm"><i class="bi bi-exclamation-octagon"></i> Hilang</button>
            </form>
        @endif
    </div>
</div>

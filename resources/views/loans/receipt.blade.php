@extends('layouts.app')

@section('title', 'Detail Peminjaman - ' . $loan->kode_transaksi)

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Sirkulasi
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-printer-fill me-1"></i> Cetak Detail / Simpan PDF
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="text-center border-bottom pb-3 mb-3">
                <h4 class="fw-bold mb-0">SIMPerpus</h4>
                <div class="small text-secondary">Sistem Informasi Manajemen Perpustakaan</div>
                <div class="badge bg-dark mt-2 font-monospace">{{ $loan->kode_transaksi }}</div>
            </div>

            <div class="row g-2 small mb-3">
                <div class="col-5 text-secondary">Nomor Anggota</div>
                <div class="col-7 fw-semibold text-end font-monospace">{{ $loan->user->nomor_anggota ?? '-' }}</div>

                <div class="col-5 text-secondary">Nama Peminjam</div>
                <div class="col-7 fw-semibold text-end">{{ $loan->user->name ?? '-' }}</div>

                <div class="col-5 text-secondary">Kelas / Unit</div>
                <div class="col-7 text-end">{{ $loan->user->kelas_jabatan ?? 'Anggota' }}</div>

                <div class="col-5 text-secondary">Status Transaksi</div>
                <div class="col-7 text-end">
                    <span class="badge bg-primary">{{ strtoupper($loan->status) }}</span>
                </div>
            </div>

            <div class="p-3 bg-light rounded-3 mb-3">
                <div class="small text-secondary mb-1">Detail Koleksi Buku</div>
                <div class="fw-bold">{{ $loan->book->judul ?? '-' }}</div>
                <div class="small text-secondary">
                    Penulis: {{ $loan->book->penulis ?? '-' }}<br>
                    ISBN: {{ $loan->book->isbn ?? '-' }} &bull; Rak: <strong>{{ $loan->book->call_number ?? '-' }}</strong>
                </div>
            </div>

            <div class="row g-2 small border-bottom pb-3 mb-3">
                <div class="col-6 text-secondary">Tanggal Pinjam</div>
                <div class="col-6 fw-semibold text-end">{{ $loan->tanggal_pinjam?->format('d F Y') }}</div>

                <div class="col-6 text-secondary">Jatuh Tempo (Due Date)</div>
                <div class="col-6 fw-bold text-danger text-end">{{ $loan->tanggal_kembali?->format('d F Y') }}</div>

                <div class="col-6 text-secondary">Perpanjangan Mandiri</div>
                <div class="col-6 text-end">{{ $loan->perpanjangan_ke }} / 1 Kali</div>

                @if($loan->tanggal_dikembalikan)
                    <div class="col-6 text-secondary">Tanggal Selesai</div>
                    <div class="col-6 fw-semibold text-end">{{ $loan->tanggal_dikembalikan->format('d F Y') }}</div>
                @endif

                @if($loan->hari_sanksi > 0)
                    <div class="col-6 text-secondary">Sanksi Skorsing Akun</div>
                    <div class="col-6 fw-bold text-danger text-end">{{ $loan->hari_sanksi }} Hari</div>
                @endif
            </div>

            <div class="small text-secondary text-center">
                <p class="mb-1">
                    <strong>Perhatian:</strong> Harap kembalikan buku tepat waktu. Keterlambatan 1–7 hari dikenakan sanksi pembekuan akun <code>hari telat &times; 2</code>, sedangkan &gt;7 hari atau buku hilang dikenakan pembekuan akun <strong>30 hari</strong>.
                </p>
                <div class="text-muted" style="font-size: 0.75rem;">
                    Dicetak pada: {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

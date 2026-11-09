@extends('layouts.app')

@section('title', 'Laporan Penjualan & Inventaris Stok — BookStore')

@section('content')
    @php
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    @endphp

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Laporan Penjualan &amp; Audit Stok Buku</h2>
            <p class="text-secondary mb-0">
                Periode Laporan: <strong>{{ $namaBulan[$month] ?? $month }} {{ $year }}</strong>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 no-print">
            <button onclick="window.print()" class="btn btn-dark">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ route('reports.export-csv', ['month' => $month, 'year' => $year]) }}"
                class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel / CSV Penjualan
            </a>
        </div>
    </div>

    {{-- Filter Periode Bulan & Tahun --}}
    <form method="GET" action="{{ route('reports.index') }}" class="card border-0 shadow-sm rounded-4 p-3 mb-4 no-print">
        <div class="row g-2 align-items-end">
            <div class="col-sm-5 col-md-4">
                <label class="form-label small fw-semibold mb-1">Bulan Transaksi</label>
                <select name="month" class="form-select">
                    @foreach($namaBulan as $num => $label)
                        <option value="{{ $num }}" @selected($month === $num)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label small fw-semibold mb-1">Tahun</label>
                <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2035">
            </div>
            <div class="col-sm-3 col-md-2">
                <button class="btn btn-primary w-100">Tampilkan</button>
            </div>
        </div>
    </form>

    {{-- Ringkasan Statistik Laporan --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="small text-secondary">Total Transaksi ({{ $namaBulan[$month] ?? $month }})</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['total_transaksi_bulan_ini'] }}</div>
                <div class="small text-secondary">Lunas/Selesai: {{ $summary['transaksi_selesai_bulan_ini'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="small text-secondary">Total Buku Terjual</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['buku_terjual_bulan_ini'] }} Eks</div>
                <div class="small text-secondary">Stok langsung terpotong saat checkout</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="small text-secondary">Pendapatan Bulan Ini</div>
                <div class="fs-4 fw-bold text-dark">Rp {{ number_format($summary['pendapatan_bulan_ini'], 0, ',', '.') }}</div>
                <div class="small text-secondary">Omzet penjualan bersih</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="small text-secondary">Kategori Buku Aktif</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['total_kategori'] }}</div>
                <div class="small text-secondary">Kategori katalog toko</div>
            </div>
        </div>
    </div>

    {{-- 1. Tabel Laporan Transaksi Penjualan Bulanan --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h5 class="fw-bold mb-0">
                1. Laporan Transaksi Penjualan Bulanan ({{ $namaBulan[$month] ?? $month }} {{ $year }})
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Pembeli</th>
                        <th>Rincian Buku</th>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th>Total Harga</th>
                        <th class="pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyTransactions as $trx)
                        <tr>
                            <td class="ps-4 font-monospace small">{{ $trx->kode_transaksi }}</td>
                            <td>
                                <div class="fw-semibold">{{ $trx->user->name ?? '-' }}</div>
                                <div class="small text-secondary">{{ $trx->user->nomor_anggota ?? '-' }}</div>
                            </td>
                            <td>
                                @foreach($trx->items as $item)
                                    <div class="small">{{ $item->book->judul ?? '-' }} ({{ $item->jumlah }}x)</div>
                                @endforeach
                            </td>
                            <td>{{ $trx->tanggal_transaksi?->format('d/m/Y') }}</td>
                            <td>{{ $trx->metode_pembayaran }}</td>
                            <td class="fw-bold">Rp {{ number_format($trx->total_harga, 0, ',', '.') }}</td>
                            <td class="pe-4">
                                <span class="badge bg-secondary">{{ strtoupper($trx->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Tidak ada transaksi pada periode bulan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Tabel Inventaris Stok & Harga Buku --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h5 class="fw-bold mb-0">
                2. Laporan Inventaris Stok &amp; Penjualan per Judul Buku
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Judul Buku</th>
                        <th>Kategori</th>
                        <th>Harga Satuan</th>
                        <th>Stok Tersedia</th>
                        <th>Di Keranjang User</th>
                        <th class="pe-4">Total Terjual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($booksInventory as $book)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold">{{ $book->judul }}</div>
                                <div class="small text-secondary">ISBN: {{ $book->isbn ?? '-' }}</div>
                            </td>
                            <td>{{ $book->category->nama ?? 'Umum' }}</td>
                            <td>{{ $book->formatted_harga }}</td>
                            <td>
                                <span class="badge {{ $book->stok > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $book->stok }} eks
                                </span>
                            </td>
                            <td>{{ $book->carts_count }} keranjang</td>
                            <td class="pe-4 fw-bold">{{ (int) ($book->terjual_count ?? 0) }} eks</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Belum ada data buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Laporan Sirkulasi, Keterlambatan & Buku Hilang')

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
            <h2 class="fw-bold mb-1">Laporan Sirkulasi & Audit Koleksi (Standar USK)</h2>
            <p class="text-secondary mb-0">
                Periode Laporan: <strong>{{ $namaBulan[$month] ?? $month }} {{ $year }}</strong>
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 no-print">
            <button onclick="window.print()" class="btn btn-dark">
                <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ route('reports.export-csv', ['month' => $month, 'year' => $year, 'type' => 'monthly']) }}"
                class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel (Sirkulasi Bulanan)
            </a>
            <a href="{{ route('reports.export-csv', ['type' => 'overdue']) }}"
                class="btn btn-outline-danger">
                <i class="bi bi-download me-1"></i> CSV Keterlambatan
            </a>
            <a href="{{ route('reports.export-csv', ['type' => 'lost']) }}"
                class="btn btn-outline-secondary">
                <i class="bi bi-download me-1"></i> CSV Buku Hilang
            </a>
        </div>
    </div>

    {{-- Filter Periode Bulan & Tahun --}}
    <form method="GET" action="{{ route('reports.index') }}" class="card border-0 shadow-sm rounded-4 p-3 mb-4 no-print">
        <div class="row g-2 align-items-end">
            <div class="col-sm-5 col-md-4">
                <label class="form-label small fw-semibold mb-1">Bulan Sirkulasi</label>
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
                <div class="small text-secondary">Total Sirkulasi ({{ $namaBulan[$month] ?? $month }})</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['total_sirkulasi_bulan_ini'] }}</div>
                <div class="small text-secondary">Selesai: {{ $summary['dikembalikan_bulan_ini'] }} | Aktif: {{ $summary['masih_dipinjam_bulan_ini'] }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="small text-secondary">Total Kasus Keterlambatan</div>
                <div class="fs-3 fw-bold text-danger">{{ $summary['total_kasus_terlambat'] }}</div>
                <div class="small text-secondary">Termasuk pinjaman menunggak aktif</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="small text-secondary">Daftar Buku Hilang (Lost)</div>
                <div class="fs-3 fw-bold text-dark">{{ $summary['total_buku_hilang'] }}</div>
                <div class="small text-secondary">Stok tidak dikembalikan &amp; sanksi 30 hr</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <div class="small text-secondary">Kategori Koleksi Terdaftar</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['total_kategori'] }}</div>
                <div class="small text-secondary">Klasifikasi DDC aktif</div>
            </div>
        </div>
    </div>

    {{-- 1. Tabel Laporan Sirkulasi Bulanan --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h5 class="fw-bold mb-0">
                1. Laporan Transaksi Sirkulasi Bulanan ({{ $namaBulan[$month] ?? $month }} {{ $year }})
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Anggota</th>
                        <th>Judul Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th class="pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthlyLoans as $loan)
                        <tr>
                            <td class="ps-4 font-monospace small">{{ $loan->kode_transaksi }}</td>
                            <td>
                                <div class="fw-semibold">{{ $loan->user->name ?? '-' }}</div>
                                <div class="small text-secondary">{{ $loan->user->nomor_anggota ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $loan->book->judul ?? '-' }}</div>
                                <div class="small text-secondary">Rak: {{ $loan->book->call_number ?? '-' }}</div>
                            </td>
                            <td>{{ $loan->tanggal_pinjam?->format('d/m/Y') }}</td>
                            <td>{{ $loan->tanggal_kembali?->format('d/m/Y') }}</td>
                            <td>{{ $loan->tanggal_dikembalikan?->format('d/m/Y') ?? '-' }}</td>
                            <td class="pe-4">
                                <span class="badge bg-secondary">{{ strtoupper($loan->status) }}</span>
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

    {{-- 2. Tabel Statistik Keterlambatan & Sanksi --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h5 class="fw-bold mb-0 text-danger">
                2. Laporan Statistik Keterlambatan & Sanksi Skorsing
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Nama Siswa</th>
                        <th>Buku</th>
                        <th>Jatuh Tempo</th>
                        <th>Keterlambatan</th>
                        <th>Sanksi Skorsing</th>
                        <th class="pe-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueLoans as $loan)
                        <tr>
                            <td class="ps-4 font-monospace small">{{ $loan->kode_transaksi }}</td>
                            <td>{{ $loan->user->name ?? '-' }} ({{ $loan->user->nomor_anggota ?? '-' }})</td>
                            <td>{{ $loan->book->judul ?? '-' }}</td>
                            <td>{{ $loan->tanggal_kembali?->format('d/m/Y') }}</td>
                            <td class="text-danger fw-bold">{{ $loan->late_days }} hari</td>
                            <td>
                                @php
                                    $sanksi = $loan->hari_sanksi > 0 ? $loan->hari_sanksi : \App\Models\Loan::calculateSanctionDays($loan->late_days);
                                @endphp
                                <span class="badge bg-warning text-dark">Suspend {{ $sanksi }} Hari</span>
                            </td>
                            <td class="pe-4">
                                <span class="badge {{ $loan->status === 'dipinjam' ? 'bg-danger' : 'bg-secondary' }}">
                                    {{ $loan->status === 'dipinjam' ? 'MASIH MENUNGGAK (OVERDUE LOCK)' : strtoupper($loan->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">Belum ada catatan keterlambatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Daftar Buku Hilang (status: hilang) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h5 class="fw-bold mb-0">
                3. Daftar Koleksi Buku Hilang (Penghapusan Stok & Sanksi 30 Hari)
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Kode Transaksi</th>
                        <th>Judul Buku & ISBN</th>
                        <th>Peminjam Terakhir</th>
                        <th>Tanggal Dilaporkan</th>
                        <th>Sanksi</th>
                        <th class="pe-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lostBooks as $loan)
                        <tr>
                            <td class="ps-4 font-monospace small">{{ $loan->kode_transaksi }}</td>
                            <td>
                                <div class="fw-semibold">{{ $loan->book->judul ?? '-' }}</div>
                                <div class="small text-secondary">ISBN: {{ $loan->book->isbn ?? '-' }}</div>
                            </td>
                            <td>{{ $loan->user->name ?? '-' }} ({{ $loan->user->nomor_anggota ?? '-' }})</td>
                            <td>{{ $loan->tanggal_dikembalikan?->format('d/m/Y') ?? '-' }}</td>
                            <td><span class="badge bg-danger">Suspend 30 Hari</span></td>
                            <td class="pe-4 small text-secondary">{{ $loan->catatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">Tidak ada koleksi buku yang tercatat hilang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

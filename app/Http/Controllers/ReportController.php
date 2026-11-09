<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Halaman Cetak Kartu Member Digital Bookstore.
     */
    public function memberCard(?User $user = null)
    {
        $authUser = auth()->user();

        if ($authUser->role === 'admin' && $user === null) {
            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'role' => 'Akses dibatasi: Halaman Kartu Member hanya diperuntukkan bagi Pelanggan.',
                ]);
        }

        if ($authUser->role !== 'admin' && $user !== null && $user->id !== $authUser->id) {
            abort(403, 'Anda tidak memiliki akses ke kartu member pelanggan lain.');
        }

        $targetUser = ($authUser->role === 'admin' && $user !== null) ? $user : $authUser;
        $accountInfo = $targetUser->accountStatusInfo();

        return view('user.member-card', [
            'member' => $targetUser,
            'accountInfo' => $accountInfo,
        ]);
    }

    /**
     * Halaman Laporan Penjualan Bulanan & Stok Buku (Admin).
     */
    public function index(Request $request)
    {
        $month = (int) $request->input('month', (int) date('m'));
        $year = (int) $request->input('year', (int) date('Y'));

        $monthlyTransactions = Transaction::with(['user', 'items.book.category'])
            ->whereYear('tanggal_transaksi', $year)
            ->whereMonth('tanggal_transaksi', $month)
            ->latest('tanggal_transaksi')
            ->get();

        $completedMonthly = $monthlyTransactions->where('status', Transaction::STATUS_SELESAI);
        $validSoldMonthly = $monthlyTransactions->where('status', '!=', Transaction::STATUS_DIBATALKAN);

        $booksInventory = Book::with('category')
            ->withCount('carts')
            ->withSum(['transactionItems as terjual_count' => function ($q): void {
                $q->whereHas('transaction', fn ($tq) => $tq->where('status', '!=', Transaction::STATUS_DIBATALKAN));
            }], 'jumlah')
            ->orderBy('stok')
            ->get();

        $summary = [
            'total_transaksi_bulan_ini' => $monthlyTransactions->count(),
            'transaksi_selesai_bulan_ini' => $completedMonthly->count(),
            'buku_terjual_bulan_ini' => (int) $validSoldMonthly->sum('total_item'),
            'pendapatan_bulan_ini' => (int) $validSoldMonthly->sum('total_harga'),
            'total_kategori' => Category::count(),
        ];

        return view('admin.reports.index', compact(
            'month',
            'year',
            'monthlyTransactions',
            'booksInventory',
            'summary'
        ));
    }

    /**
     * Export Laporan Penjualan Bulanan ke format CSV (Excel-compatible dengan UTF-8 BOM).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $month = (int) $request->input('month', (int) date('m'));
        $year = (int) $request->input('year', (int) date('Y'));

        $filename = sprintf('laporan-penjualan-bookstore-%04d-%02d.csv', $year, $month);

        return response()->streamDownload(function () use ($month, $year): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Transaksi',
                'Nomor Member',
                'Nama Pembeli',
                'Daftar Buku Dibeli',
                'Tanggal Transaksi',
                'Total Item',
                'Total Harga (Rp)',
                'Metode Pembayaran',
                'Alamat Pengiriman',
                'Status',
                'Catatan',
            ]);

            $query = Transaction::with(['user', 'items.book'])
                ->whereYear('tanggal_transaksi', $year)
                ->whereMonth('tanggal_transaksi', $month)
                ->latest('tanggal_transaksi');

            foreach ($query->cursor() as $trx) {
                $bookList = $trx->items->map(function ($item): string {
                    return sprintf('%s (%dx @ Rp%s)', $item->book->judul ?? '-', $item->jumlah, number_format($item->harga_satuan, 0, ',', '.'));
                })->implode('; ');

                fputcsv($handle, [
                    $trx->kode_transaksi,
                    $trx->user->nomor_anggota ?? '-',
                    $trx->user->name ?? '-',
                    $bookList,
                    $trx->tanggal_transaksi?->format('Y-m-d'),
                    $trx->total_item,
                    $trx->total_harga,
                    $trx->metode_pembayaran,
                    $trx->alamat_pengiriman ?? '-',
                    strtoupper($trx->status),
                    $trx->catatan ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

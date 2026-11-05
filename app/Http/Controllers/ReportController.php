<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Halaman Cetak Kartu Anggota Digital Perpustakaan.
     */
    public function memberCard(?User $user = null)
    {
        $authUser = auth()->user();
        $targetUser = ($authUser->role === 'admin' && $user !== null) ? $user : $authUser;
        $accountInfo = $targetUser->accountStatusInfo();

        return view('user.member-card', [
            'member' => $targetUser,
            'accountInfo' => $accountInfo,
        ]);
    }

    /**
     * Halaman Laporan Sirkulasi Bulanan, Statistik Keterlambatan, dan Daftar Buku Hilang (Admin).
     */
    public function index(Request $request)
    {
        $month = (int) $request->input('month', (int) date('m'));
        $year = (int) $request->input('year', (int) date('Y'));

        $monthlyLoans = Loan::with(['user', 'book.category'])
            ->whereYear('tanggal_pinjam', $year)
            ->whereMonth('tanggal_pinjam', $month)
            ->latest('tanggal_pinjam')
            ->get();

        $overdueLoans = Loan::with(['user', 'book'])
            ->where(function ($q): void {
                $q->where('hari_terlambat', '>', 0)
                    ->orWhere(function ($sub): void {
                        $sub->where('status', Loan::STATUS_DIPINJAM)
                            ->whereDate('tanggal_kembali', '<', today());
                    });
            })
            ->latest()
            ->get();

        $lostBooks = Loan::with(['user', 'book.category'])
            ->where('status', Loan::STATUS_HILANG)
            ->latest('tanggal_dikembalikan')
            ->get();

        $summary = [
            'total_sirkulasi_bulan_ini' => $monthlyLoans->count(),
            'dikembalikan_bulan_ini' => $monthlyLoans->where('status', Loan::STATUS_DIKEMBALIKAN)->count(),
            'masih_dipinjam_bulan_ini' => $monthlyLoans->where('status', Loan::STATUS_DIPINJAM)->count(),
            'total_kasus_terlambat' => $overdueLoans->count(),
            'total_buku_hilang' => $lostBooks->count(),
            'total_kategori' => Category::count(),
        ];

        return view('admin.reports.index', compact(
            'month',
            'year',
            'monthlyLoans',
            'overdueLoans',
            'lostBooks',
            'summary'
        ));
    }

    /**
     * Export Laporan Sirkulasi Bulanan ke format CSV (Excel-compatible dengan UTF-8 BOM).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $month = (int) $request->input('month', (int) date('m'));
        $year = (int) $request->input('year', (int) date('Y'));
        $type = (string) $request->input('type', 'monthly');

        $filename = sprintf('laporan-perpustakaan-%s-%04d-%02d.csv', $type, $year, $month);

        return response()->streamDownload(function () use ($month, $year, $type): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar Microsoft Excel langsung mengenali kolom & karakter dengan benar
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Transaksi',
                'Nomor Anggota',
                'Nama Peminjam',
                'Kelas / Jabatan',
                'Judul Buku',
                'ISBN',
                'Call Number',
                'Tanggal Pinjam',
                'Jatuh Tempo (Due Date)',
                'Tanggal Dikembalikan',
                'Status',
                'Perpanjangan',
                'Hari Terlambat',
                'Sanksi Suspend (Hari)',
                'Catatan',
            ]);

            $query = Loan::with(['user', 'book']);

            if ($type === 'lost') {
                $query->where('status', Loan::STATUS_HILANG);
            } elseif ($type === 'overdue') {
                $query->where(function ($q): void {
                    $q->where('hari_terlambat', '>', 0)
                        ->orWhere(function ($sub): void {
                            $sub->where('status', Loan::STATUS_DIPINJAM)
                                ->whereDate('tanggal_kembali', '<', today());
                        });
                });
            } else {
                $query->whereYear('tanggal_pinjam', $year)
                    ->whereMonth('tanggal_pinjam', $month);
            }

            foreach ($query->latest('tanggal_pinjam')->cursor() as $loan) {
                fputcsv($handle, [
                    $loan->kode_transaksi,
                    $loan->user->nomor_anggota ?? '-',
                    $loan->user->name ?? '-',
                    $loan->user->kelas_jabatan ?? '-',
                    $loan->book->judul ?? '-',
                    $loan->book->isbn ?? '-',
                    $loan->book->call_number ?? '-',
                    $loan->tanggal_pinjam?->format('Y-m-d'),
                    $loan->tanggal_kembali?->format('Y-m-d'),
                    $loan->tanggal_dikembalikan?->format('Y-m-d') ?? '-',
                    strtoupper($loan->status),
                    $loan->perpanjangan_ke.'x',
                    $loan->late_days,
                    $loan->hari_sanksi,
                    $loan->catatan ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

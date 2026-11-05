<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Mengarahkan tampilan Dashboard ke file Blade terpisah sesuai Role:
     * - Admin -> resources/views/admin/dashboard.blade.php
     * - User  -> resources/views/user/dashboard.blade.php
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->userDashboard($user);
    }

    /**
     * Data & View Dashboard khusus Admin / Pustakawan.
     */
    protected function adminDashboard()
    {
        $stats = [
            'total_judul' => Book::count(),
            'total_eksemplar' => (int) Book::sum('total_koleksi'),
            'stok_tersedia' => (int) Book::sum('stok'),
            'sedang_dipinjam' => Loan::where('status', Loan::STATUS_DIPINJAM)->count(),
            'menunggu_persetujuan' => Loan::where('status', Loan::STATUS_MENUNGGU)->count(),
            'terlambat_aktif' => Loan::where('status', Loan::STATUS_DIPINJAM)
                ->whereDate('tanggal_kembali', '<', today())
                ->count(),
            'buku_hilang' => Loan::where('status', Loan::STATUS_HILANG)->count(),
            'siswa_disanksi' => User::where('role', 'user')
                ->whereDate('suspended_until', '>=', today())
                ->count(),
        ];

        $sanctionedMembers = User::where('role', 'user')
            ->where(function ($q): void {
                $q->whereDate('suspended_until', '>=', today())
                    ->orWhereHas('loans', function ($lq): void {
                        $lq->where('status', Loan::STATUS_DIPINJAM)
                            ->whereDate('tanggal_kembali', '<', today());
                    });
            })
            ->with(['loans' => function ($q): void {
                $q->with('book')
                    ->whereIn('status', [Loan::STATUS_DIPINJAM, Loan::STATUS_HILANG])
                    ->latest();
            }])
            ->get();

        $recentLoans = Loan::with(['user', 'book'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'sanctionedMembers', 'recentLoans'));
    }

    /**
     * Data & View Dashboard khusus Siswa / Anggota.
     */
    protected function userDashboard(User $user)
    {
        $activeLoans = $user->activeLoans()->with('book.category')->latest()->get();
        $loanHistoryCount = $user->loans()->count();
        $accountInfo = $user->accountStatusInfo();
        $eligibility = $user->evaluateBorrowEligibility();

        return view('user.dashboard', compact(
            'user',
            'activeLoans',
            'loanHistoryCount',
            'accountInfo',
            'eligibility'
        ));
    }
}

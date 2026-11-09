<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        Transaction::syncPendingTransactions();

        $user = auth()->user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->userDashboard($user);
    }

    protected function adminDashboard()
    {
        $stats = [
            'total_judul' => Book::count(),
            'stok_tersedia' => (int) Book::sum('stok'),
            'buku_terjual' => (int) TransactionItem::whereHas('transaction', function ($q): void {
                $q->where('status', '!=', Transaction::STATUS_DIBATALKAN);
            })->sum('jumlah'),
            'total_pendapatan' => (int) Transaction::where('status', '!=', Transaction::STATUS_DIBATALKAN)->sum('total_harga'),
            'menunggu_konfirmasi' => Transaction::where('status', Transaction::STATUS_MENUNGGU)->count(),
            'transaksi_selesai' => Transaction::where('status', Transaction::STATUS_SELESAI)->count(),
            'keranjang_aktif' => Cart::count(),
            'total_pelanggan' => User::where('role', 'user')->count(),
        ];

        $lowStockBooks = Book::with('category')
            ->where('stok', '<=', 5)
            ->orderBy('stok')
            ->take(6)
            ->get();

        $recentTransactions = Transaction::with(['user', 'items.book'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'lowStockBooks', 'recentTransactions'));
    }

    protected function userDashboard(User $user)
    {
        $cartItems = $user->carts()->with('book.category')->latest()->get();
        $recentTransactions = $user->transactions()->with('items.book')->latest()->take(5)->get();
        $transactionCount = $user->transactions()->count();
        $totalBelanja = (int) $user->transactions()
            ->where('status', '!=', Transaction::STATUS_DIBATALKAN)
            ->sum('total_harga');
        $accountInfo = $user->accountStatusInfo();

        return view('user.dashboard', compact(
            'user',
            'cartItems',
            'recentTransactions',
            'transactionCount',
            'totalBelanja',
            'accountInfo'
        ));
    }
}

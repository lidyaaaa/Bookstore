<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Beranda OPAC Publik (Search-First Design) - dapat diakses pengunjung maupun anggota
Route::get('/', [BookController::class, 'index'])->name('home');
Route::get('/books', [BookController::class, 'index'])->name('books.index');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Kartu Anggota Digital & Cetak
    Route::get('/kartu-anggota/{user?}', [ReportController::class, 'memberCard'])
        ->name('member.card');

    // Modul Transaksi Sirkulasi & Bisnis Logika Sanksi
    Route::get('/peminjaman', [LoanController::class, 'index'])
        ->name('loans.index');

    Route::post('/books/{book}/pinjam', [LoanController::class, 'store'])
        ->name('loans.store');

    Route::post('/peminjaman/{loan}/batal', [LoanController::class, 'cancel'])
        ->name('loans.cancel');

    Route::post('/peminjaman/{loan}/perpanjang', [LoanController::class, 'extend'])
        ->name('loans.extend');

    Route::post('/peminjaman/{loan}/hilang', [LoanController::class, 'markLost'])
        ->name('loans.lost');

    Route::get('/peminjaman/{loan}/struk', [LoanController::class, 'receipt'])
        ->name('loans.receipt');

    // Modul Khusus Admin / Pustakawan
    Route::middleware('role:admin')->group(function () {
        // Manajemen Koleksi Buku
        Route::get('/books/create', [BookController::class, 'create'])
            ->name('books.create');

        Route::post('/books', [BookController::class, 'store'])
            ->name('books.store');

        Route::get('/books/{book}/edit', [BookController::class, 'edit'])
            ->name('books.edit');

        Route::put('/books/{book}', [BookController::class, 'update'])
            ->name('books.update');

        Route::delete('/books/{book}', [BookController::class, 'destroy'])
            ->name('books.destroy');

        // Manajemen Kategori / Subjek Koleksi
        Route::post('/categories', [CategoryController::class, 'store'])
            ->name('categories.store');

        Route::put('/categories/{category}', [CategoryController::class, 'update'])
            ->name('categories.update');

        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
            ->name('categories.destroy');

        // Aksi Sirkulasi Admin (Approve, Kembalikan, Loket Langsung, & Cabut Sanksi)
        Route::post('/peminjaman/loket', [LoanController::class, 'storeByAdmin'])
            ->name('loans.store-admin');

        Route::post('/peminjaman/{loan}/setujui', [LoanController::class, 'approve'])
            ->name('loans.approve');

        Route::post('/users/{user}/setujui-semua', [LoanController::class, 'approveAll'])
            ->name('loans.approve-all');

        Route::post('/peminjaman/{loan}/kembalikan', [LoanController::class, 'returnBook'])
            ->name('loans.return');

        Route::post('/users/{user}/cabut-sanksi', [LoanController::class, 'clearSanction'])
            ->name('users.clear-sanction');

        // Laporan & Export Data Portofolio USK
        Route::get('/laporan', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/laporan/export-csv', [ReportController::class, 'exportCsv'])
            ->name('reports.export-csv');
    });
});

<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Menampilkan daftar transaksi sirkulasi dengan halaman terpisah sesuai role:
     * - Admin -> resources/views/admin/loans/index.blade.php
     * - User  -> resources/views/user/loans/index.blade.php
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return $this->adminIndex($request);
        }

        return $this->userIndex($user);
    }

    /**
     * Halaman manajemen sirkulasi & sanksi khusus Admin.
     * Transaksi dikelompokkan per peminjam (user), bukan per buku.
     */
    protected function adminIndex(Request $request)
    {
        $filterLoans = function ($query) use ($request): void {
            if ($request->filled('search')) {
                $search = trim((string) $request->search);
                $query->where(function ($q) use ($search): void {
                    $q->where('kode_transaksi', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search): void {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('nomor_anggota', 'like', "%{$search}%");
                        })
                        ->orWhereHas('book', function ($bq) use ($search): void {
                            $bq->where('judul', 'like', "%{$search}%")
                                ->orWhere('isbn', 'like', "%{$search}%");
                        });
                });
            }

            if ($request->filled('status')) {
                if ($request->status === 'terlambat') {
                    $query->where('status', Loan::STATUS_DIPINJAM)
                        ->whereDate('tanggal_kembali', '<', today());
                } else {
                    $query->where('status', $request->status);
                }
            }
        };

        $borrowers = User::query()
            ->whereHas('loans', $filterLoans)
            ->with(['loans' => function ($query) use ($filterLoans): void {
                $filterLoans($query);
                $query->with('book.category')->latest();
            }])
            ->withMax(['loans' => $filterLoans], 'created_at')
            ->orderByDesc('loans_max_created_at')
            ->paginate(10)
            ->withQueryString();

        $members = User::where('role', 'user')->orderBy('name')->get();
        $availableBooks = Book::where('stok', '>', 0)->orderBy('judul')->get();

        return view('admin.loans.index', compact('borrowers', 'members', 'availableBooks'));
    }

    /**
     * Halaman riwayat & status peminjaman khusus Siswa / Anggota.
     */
    protected function userIndex(User $user)
    {
        $loans = $user->loans()
            ->with('book.category')
            ->latest()
            ->paginate(12);

        return view('user.loans.index', compact('loans'));
    }

    /**
     * Proses pengajuan peminjaman buku dengan validasi aturan bisnis USK:
     * 1. Cek Overdue Lock (buku menunggak belum dikembalikan)
     * 2. Cek Sanksi Pembekuan Akun (suspended_until)
     * 3. Cek Kuota Aktif (maksimal 2 buku per siswa)
     * 4. Cek ketersediaan stok dengan row-level locking (lockForUpdate)
     */
    public function store(Request $request, Book $book)
    {
        $maxDueDate = today()->addDays(Loan::DEFAULT_LOAN_DAYS)->toDateString();

        $request->validate([
            'tanggal_kembali' => [
                'nullable',
                'date',
                'after:today',
                'before_or_equal:'.$maxDueDate,
            ],
            'user_id' => 'nullable|exists:users,id',
        ], [
            'tanggal_kembali.after' => 'Tanggal rencana kembali harus minimal besok.',
            'tanggal_kembali.before_or_equal' => 'Durasi peminjaman maksimal 3 minggu (21 hari).',
        ]);

        /** @var User $borrower */
        $borrower = (auth()->user()->role === 'admin' && $request->filled('user_id'))
            ? User::findOrFail($request->user_id)
            : auth()->user();

        // 1. Evaluasi kelayakan peminjam (Overdue Lock, Suspend, & Kuota 2 buku)
        $eligibility = $borrower->evaluateBorrowEligibility();
        if (! $eligibility['allowed']) {
            return back()->withErrors([
                'loan' => $eligibility['reason'],
            ]);
        }

        // 2. Pastikan tidak sedang meminjam judul buku yang sama pada waktu bersamaan
        $alreadyBorrowingSameBook = $borrower->activeLoans()
            ->where('book_id', $book->id)
            ->exists();

        if ($alreadyBorrowingSameBook) {
            return back()->withErrors([
                'loan' => 'Anda sedang meminjam atau mengajukan buku yang sama.',
            ]);
        }

        $initialStatus = auth()->user()->role === 'admin'
            ? Loan::STATUS_DIPINJAM
            : Loan::STATUS_MENUNGGU;

        // Default jatuh tempo adalah 3 minggu (21 hari) dari hari peminjaman
        $dueDate = $request->filled('tanggal_kembali')
            ? $request->tanggal_kembali
            : today()->addDays(Loan::DEFAULT_LOAN_DAYS)->toDateString();

        // 3. Eksekusi transaksi dengan pessimistic locking agar bebas race-condition
        // Catatan: Jika status masih 'menunggu' (pengajuan siswa), stok fisik buku BELUM dikurangi
        // dan belum dihitung sebagai dipinjam sampai di-ACC oleh Admin.
        $loan = DB::transaction(function () use ($book, $borrower, $initialStatus, $dueDate) {
            $lockedBook = Book::whereKey($book->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBook->stok < 1) {
                return null;
            }

            if ($initialStatus === Loan::STATUS_DIPINJAM) {
                $lockedBook->decrement('stok');
            }

            return Loan::create([
                'user_id' => $borrower->id,
                'book_id' => $lockedBook->id,
                'tanggal_pinjam' => today(),
                'tanggal_kembali' => $dueDate,
                'status' => $initialStatus,
            ]);
        });

        if (! $loan) {
            return back()->withErrors([
                'book' => 'Maaf, stok fisik buku di rak sedang habis.',
            ]);
        }

        $successText = $initialStatus === Loan::STATUS_MENUNGGU
            ? sprintf('Pengajuan peminjaman [%s] berhasil dikirim dan sedang menunggu persetujuan Admin.', $loan->kode_transaksi)
            : sprintf('Transaksi peminjaman [%s] berhasil dicatat dengan masa pinjam hingga %s.', $loan->kode_transaksi, $loan->tanggal_kembali->format('d-m-Y'));

        return redirect()
            ->route('loans.index')
            ->with('success', $successText);
    }

    /**
     * Peminjaman langsung dari loket sirkulasi oleh Admin untuk Anggota.
     */
    public function storeByAdmin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'tanggal_kembali' => 'nullable|date|after:today|before_or_equal:'.today()->addDays(Loan::DEFAULT_LOAN_DAYS)->toDateString(),
        ]);

        $book = Book::findOrFail($request->book_id);

        return $this->store($request, $book);
    }

    /**
     * Admin menyetujui pengajuan pinjaman ('menunggu' -> 'dipinjam').
     * Saat di-ACC barulah stok fisik buku di rak dikurangi 1 dan masa pinjam 3 minggu dimulai.
     */
    public function approve(Loan $loan)
    {
        if ($loan->status !== Loan::STATUS_MENUNGGU) {
            return back()->withErrors([
                'loan' => 'Hanya pengajuan berstatus menunggu yang dapat disetujui.',
            ]);
        }

        $newDueDate = today()->addDays(Loan::DEFAULT_LOAN_DAYS);

        if (! $this->approveLoan($loan, $newDueDate)) {
            return back()->withErrors([
                'loan' => 'Gagal menyetujui peminjaman karena stok fisik buku di rak sedang habis.',
            ]);
        }

        return back()->with('success', sprintf(
            'Peminjaman [%s] disetujui. Stok buku dikurangi 1 dan masa pinjam berlaku 3 minggu hingga %s.',
            $loan->kode_transaksi,
            $newDueDate->format('d-m-Y')
        ));
    }

    /**
     * Admin menyetujui seluruh pengajuan 'menunggu' milik satu peminjam sekaligus.
     */
    public function approveAll(User $user)
    {
        $pendingLoans = $user->loans()->where('status', Loan::STATUS_MENUNGGU)->get();

        if ($pendingLoans->isEmpty()) {
            return back()->withErrors([
                'loan' => 'Tidak ada pengajuan berstatus menunggu untuk anggota ini.',
            ]);
        }

        $newDueDate = today()->addDays(Loan::DEFAULT_LOAN_DAYS);
        $approvedCount = $pendingLoans->filter(fn (Loan $loan): bool => $this->approveLoan($loan, $newDueDate))->count();
        $failedCount = $pendingLoans->count() - $approvedCount;

        $message = sprintf('%d pengajuan milik %s disetujui hingga %s.', $approvedCount, $user->name, $newDueDate->format('d-m-Y'));

        if ($failedCount > 0) {
            return back()
                ->with('success', $message)
                ->withErrors(['loan' => "{$failedCount} pengajuan gagal disetujui karena stok buku habis."]);
        }

        return back()->with('success', $message);
    }

    /**
     * Menyetujui satu pengajuan dengan row-level locking dan mengurangi stok buku.
     */
    protected function approveLoan(Loan $loan, Carbon $newDueDate): bool
    {
        return DB::transaction(function () use ($loan, $newDueDate): bool {
            $lockedLoan = Loan::whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedLoan->status !== Loan::STATUS_MENUNGGU) {
                return false;
            }

            $lockedBook = Book::whereKey($lockedLoan->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBook->stok < 1) {
                return false;
            }

            $lockedBook->decrement('stok');

            $lockedLoan->update([
                'tanggal_pinjam' => today(),
                'tanggal_kembali' => $newDueDate,
                'status' => Loan::STATUS_DIPINJAM,
            ]);

            return true;
        });
    }

    /**
     * Proses Pengembalian Buku & Perhitungan Sanksi Non-Tunai (Skorsing Akun):
     * - Telat 1–7 hari : Akun disuspend selama (hari keterlambatan x 2)
     * - Telat > 7 hari : Akun disuspend flat 30 hari
     */
    public function returnBook(Request $request, Loan $loan)
    {
        if ($loan->status !== Loan::STATUS_DIPINJAM) {
            return back()->withErrors([
                'loan' => 'Hanya buku yang sedang aktif dipinjam yang dapat dikembalikan.',
            ]);
        }

        $sanctionMessage = null;

        DB::transaction(function () use ($loan, $request, &$sanctionMessage): void {
            $lockedLoan = Loan::with(['user', 'book'])
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedLoan->status !== Loan::STATUS_DIPINJAM) {
                abort(400, 'Status peminjaman sudah berubah.');
            }

            // Hitung hari keterlambatan dari due_date (tanggal_kembali) terhadap hari ini
            $lateDays = 0;
            if ($lockedLoan->tanggal_kembali && $lockedLoan->tanggal_kembali->lt(today())) {
                $lateDays = (int) $lockedLoan->tanggal_kembali->diffInDays(today());
            }

            $sanctionDays = Loan::calculateSanctionDays($lateDays);

            // Kembalikan stok buku ke rak katalog
            Book::whereKey($lockedLoan->book_id)
                ->lockForUpdate()
                ->firstOrFail()
                ->increment('stok');

            $lockedLoan->update([
                'status' => Loan::STATUS_DIKEMBALIKAN,
                'tanggal_dikembalikan' => today(),
                'hari_terlambat' => $lateDays,
                'hari_sanksi' => $sanctionDays,
                'catatan' => $request->input('catatan') ?: ($lateDays > 0
                    ? "Dikembalikan terlambat {$lateDays} hari (Sanksi skorsing {$sanctionDays} hari)"
                    : 'Dikembalikan tepat waktu'),
            ]);

            // Terapkan sanksi non-tunai (suspend akun) bila terlambat
            if ($sanctionDays > 0 && $lockedLoan->user) {
                $reason = sprintf(
                    'Terlambat mengembalikan buku "%s" selama %d hari (Sanksi %d hari)',
                    $lockedLoan->book->judul,
                    $lateDays,
                    $sanctionDays
                );
                $lockedLoan->user->applySanction($sanctionDays, $reason);
                $sanctionMessage = sprintf(
                    'Buku dikembalikan terlambat %d hari. Akun %s otomatis dibekukan selama %d hari (s/d %s).',
                    $lateDays,
                    $lockedLoan->user->name,
                    $sanctionDays,
                    $lockedLoan->user->fresh()->suspended_until->format('d-m-Y')
                );
            }
        });

        return back()->with(
            'success',
            $sanctionMessage ?? 'Buku berhasil dikembalikan tepat waktu dan stok telah ditambahkan kembali ke katalog.'
        );
    }

    /**
     * Mekanisme Buku Hilang (status: hilang):
     * - Dapat ditandai oleh Admin atau dilaporkan mandiri oleh Siswa peminjam.
     * - Stok buku TIDAK dikembalikan ke katalog, total koleksi fisik dikurangi 1.
     * - Akun peminjam otomatis disuspend selama 30 hari.
     */
    public function markLost(Request $request, Loan $loan)
    {
        $user = auth()->user();
        abort_unless($user->role === 'admin' || $loan->user_id === $user->id, 403);

        if ($loan->status !== Loan::STATUS_DIPINJAM) {
            return back()->withErrors([
                'loan' => 'Hanya buku yang sedang berstatus dipinjam yang dapat ditandai hilang.',
            ]);
        }

        DB::transaction(function () use ($loan, $request, $user): void {
            $lockedLoan = Loan::with(['user', 'book'])
                ->whereKey($loan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedLoan->status !== Loan::STATUS_DIPINJAM) {
                abort(400, 'Status peminjaman sudah berubah.');
            }

            $lockedBook = Book::whereKey($lockedLoan->book_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Kurangi total koleksi fisik karena buku hilang (stok tersedia tidak di-increment)
            if ($lockedBook->total_koleksi > 0) {
                $lockedBook->decrement('total_koleksi');
            }

            $lateDays = ($lockedLoan->tanggal_kembali && $lockedLoan->tanggal_kembali->lt(today()))
                ? (int) $lockedLoan->tanggal_kembali->diffInDays(today())
                : 0;

            $reporter = $user->role === 'admin' ? 'Petugas Admin' : 'Laporan Mandiri Siswa';
            $catatanInput = trim((string) $request->input('catatan', ''));
            $catatanFinal = $catatanInput !== ''
                ? "{$reporter}: {$catatanInput}"
                : "Buku dinyatakan hilang ({$reporter}). Stok tidak dikembalikan & sanksi 30 hari.";

            $lockedLoan->update([
                'status' => Loan::STATUS_HILANG,
                'tanggal_dikembalikan' => today(),
                'hari_terlambat' => $lateDays,
                'hari_sanksi' => 30,
                'catatan' => $catatanFinal,
            ]);

            // Jatuhkan sanksi maksimal 30 hari pembekuan akun
            if ($lockedLoan->user) {
                $lockedLoan->user->applySanction(
                    30,
                    sprintf('Buku hilang: "%s" (Sanksi pembekuan 30 hari)', $lockedBook->judul)
                );
            }
        });

        return back()->with(
            'success',
            'Buku telah ditandai HILANG. Stok tidak dikembalikan ke katalog dan akun peminjam dibekukan selama 30 hari.'
        );
    }

    /**
     * Perpanjangan Masa Pinjam Mandiri (+7 hari):
     * Hanya untuk pengguna biasa (siswa/anggota) pemilik pinjaman,
     * maksimal 1 kali perpanjangan (+7 hari) dan belum melewati jatuh tempo.
     */
    public function extend(Loan $loan)
    {
        $user = auth()->user();
        abort_unless(
            $user->role === 'user' && $loan->user_id === $user->id,
            403,
            'Fitur perpanjangan mandiri hanya dapat dilakukan oleh anggota peminjam.'
        );

        if ($loan->status !== Loan::STATUS_DIPINJAM) {
            return back()->withErrors([
                'loan' => 'Hanya buku yang sedang aktif dipinjam yang dapat diperpanjang.',
            ]);
        }

        if ($loan->perpanjangan_ke >= Loan::MAX_EXTENSIONS) {
            return back()->withErrors([
                'loan' => 'Batas perpanjangan mandiri sudah tercapai (maksimal 1 kali per peminjaman).',
            ]);
        }

        if ($loan->isOverdue()) {
            return back()->withErrors([
                'loan' => 'Peminjaman yang sudah melewati batas waktu (jatuh tempo) tidak dapat diperpanjang.',
            ]);
        }

        if ($loan->user && $loan->user->isSuspended()) {
            return back()->withErrors([
                'loan' => 'Perpanjangan ditolak karena akun sedang dalam masa pembekuan sanksi.',
            ]);
        }

        $newDueDate = $loan->tanggal_kembali->copy()->addDays(7);

        $loan->update([
            'tanggal_kembali' => $newDueDate,
            'perpanjangan_ke' => $loan->perpanjangan_ke + 1,
            'catatan' => sprintf('Diperpanjang 1x (+7 hari) hingga %s', $newDueDate->format('d-m-Y')),
        ]);

        return back()->with('success', sprintf(
            'Masa pinjam berhasil diperpanjang 7 hari hingga %s.',
            $newDueDate->format('d-m-Y')
        ));
    }

    /**
     * Pembatalan pengajuan peminjaman yang masih berstatus 'menunggu'.
     * Karena stok belum dikurangi saat status 'menunggu', stok buku tetap utuh.
     */
    public function cancel(Loan $loan)
    {
        $user = auth()->user();
        abort_unless($user->role === 'admin' || $loan->user_id === $user->id, 403);

        if ($loan->status !== Loan::STATUS_MENUNGGU) {
            return back()->withErrors([
                'loan' => 'Hanya pengajuan yang masih menunggu yang dapat dibatalkan.',
            ]);
        }

        $loan->update([
            'status' => Loan::STATUS_DIBATALKAN,
            'catatan' => 'Pengajuan dibatalkan sebelum disetujui',
        ]);

        return redirect()
            ->route('loans.index')
            ->with('success', 'Pengajuan peminjaman berhasil dibatalkan.');
    }

    /**
     * Cetak struk / bukti transaksi peminjaman.
     */
    public function receipt(Loan $loan)
    {
        $user = auth()->user();
        abort_unless($user->role === 'admin' || $loan->user_id === $user->id, 403);

        $loan->load(['book.category', 'user']);

        return view('loans.receipt', compact('loan'));
    }

    /**
     * Admin mencabut sanksi pembekuan akun anggota.
     */
    public function clearSanction(User $user)
    {
        $user->update([
            'suspended_until' => null,
            'alasan_suspend' => null,
        ]);

        return back()->with('success', sprintf(
            'Sanksi pembekuan akun untuk anggota %s berhasil dicabut.',
            $user->name
        ));
    }
}

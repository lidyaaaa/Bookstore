<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        Transaction::syncPendingTransactions();

        $user = auth()->user();

        if ($user->role === 'admin') {
            return $this->adminIndex($request);
        }

        return $this->userIndex($user);
    }

    protected function adminIndex(Request $request)
    {
        $search = trim((string) $request->search);

        // Pesanan Menunggu Konfirmasi (Pending/Aktif)
        $pendingQuery = Transaction::query()
            ->with(['user', 'items.book.category'])
            ->where('status', Transaction::STATUS_MENUNGGU);

        if ($request->filled('search')) {
            $pendingQuery->where(function ($q) use ($search): void {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('metode_pembayaran', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('nomor_anggota', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.book', function ($bq) use ($search): void {
                        $bq->where('judul', 'like', "%{$search}%")
                            ->orWhere('isbn', 'like', "%{$search}%");
                    });
            });
        }

        $pendingTransactions = $pendingQuery->latest()->get();

        // Riwayat Transaksi Selesai & Dibatalkan
        $historyQuery = Transaction::query()
            ->with(['user', 'items.book.category'])
            ->where('status', '!=', Transaction::STATUS_MENUNGGU);

        if ($request->filled('search')) {
            $historyQuery->where(function ($q) use ($search): void {
                $q->where('kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('metode_pembayaran', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('nomor_anggota', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.book', function ($bq) use ($search): void {
                        $bq->where('judul', 'like', "%{$search}%")
                            ->orWhere('isbn', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('history_status')) {
            $historyQuery->where('status', $request->history_status);
        }

        $historyTransactions = $historyQuery->latest()
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        $members = User::where('role', 'user')->orderBy('name')->get();
        $availableBooks = Book::where('stok', '>', 0)->orderBy('judul')->get();

        return view('admin.transactions.index', compact('pendingTransactions', 'historyTransactions', 'members', 'availableBooks'));
    }

    protected function userIndex(User $user)
    {
        $activeOrders = $user->transactions()
            ->with('items.book.category')
            ->where('status', Transaction::STATUS_MENUNGGU)
            ->latest()
            ->get();

        $historyTransactions = $user->transactions()
            ->with('items.book.category')
            ->where('status', '!=', Transaction::STATUS_MENUNGGU)
            ->latest()
            ->paginate(10, ['*'], 'history_page');

        return view('user.transactions.index', compact('activeOrders', 'historyTransactions'));
    }

    /**
     * Checkout item keranjang belanja yang dipilih (dicentang) milik user menjadi Transaksi.
     * Begitu user checkout, stok fisik buku LANGSUNG dikurangi dan terhitung terjual,
     * sementara status pesanan tetap 'menunggu' untuk konfirmasi/approval Admin.
     */
    public function checkout(Request $request)
    {
        UserAddress::ensureTableExists();

        $data = $request->validate([
            'cart_ids' => 'nullable|array',
            'cart_ids.*' => 'integer',
            'metode_pembayaran' => ['required', Rule::in(Transaction::PAYMENT_METHODS)],
            'address_id' => 'nullable|integer',
            'alamat_pengiriman' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($request->has('cart_ids_submitted') && empty($data['cart_ids'])) {
            return back()->withErrors([
                'cart' => 'Pilih (centang) minimal 1 buku di keranjang untuk di-checkout.',
            ])->withInput();
        }

        $user = auth()->user();

        // Tentukan alamat pengiriman dari daftar alamat tersimpan (address_id) atau input teks
        $alamatFinal = null;
        if (! empty($data['address_id'])) {
            $savedAddress = UserAddress::where('user_id', $user->id)
                ->whereKey($data['address_id'])
                ->first();

            if ($savedAddress) {
                $alamatFinal = $savedAddress->formatted_address;
            }
        }

        if (! $alamatFinal && ! empty($data['alamat_pengiriman'])) {
            $alamatFinal = trim($data['alamat_pengiriman']);
        }

        if (! $alamatFinal) {
            return back()->withErrors([
                'address_id' => 'Pilih alamat pengiriman terlebih dahulu atau tambahkan alamat baru.',
            ])->withInput();
        }

        $transaction = DB::transaction(function () use ($user, $data, $alamatFinal): Transaction {
            $query = Cart::with('book')
                ->where('user_id', $user->id)
                ->lockForUpdate();

            if (! empty($data['cart_ids'])) {
                $query->whereIn('id', $data['cart_ids']);
            }

            $cartItems = $query->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Pilih minimal 1 buku di keranjang belanja Anda untuk di-checkout.',
                ]);
            }

            $totalItem = 0;
            $totalHarga = 0;
            $preparedItems = [];

            foreach ($cartItems as $item) {
                $lockedBook = Book::whereKey($item->book_id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedBook) {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu buku di keranjang tidak lagi tersedia.',
                    ]);
                }

                if ($lockedBook->stok < $item->jumlah) {
                    throw ValidationException::withMessages([
                        'cart' => sprintf(
                            'Stok buku "%s" tidak mencukupi (Tersedia: %d, di keranjang: %d). Stok tidak boleh minus.',
                            $lockedBook->judul,
                            $lockedBook->stok,
                            $item->jumlah
                        ),
                    ]);
                }

                // Begitu user checkout, stok fisik buku LANGSUNG dikurangi
                $lockedBook->decrement('stok', $item->jumlah);

                $hargaSatuan = (int) $lockedBook->harga;
                $subtotal = $hargaSatuan * $item->jumlah;

                $totalItem += $item->jumlah;
                $totalHarga += $subtotal;

                $preparedItems[] = [
                    'book_id' => $lockedBook->id,
                    'jumlah' => $item->jumlah,
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                ];
            }

            $trx = Transaction::create([
                'user_id' => $user->id,
                'tanggal_transaksi' => today(),
                'total_item' => $totalItem,
                'total_harga' => $totalHarga,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'alamat_pengiriman' => $alamatFinal,
                'status' => Transaction::STATUS_MENUNGGU,
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($preparedItems as $prepared) {
                $trx->items()->create($prepared);
            }

            Cart::where('user_id', $user->id)
                ->whereIn('id', $cartItems->pluck('id'))
                ->delete();

            return $trx;
        });

        Transaction::syncPendingTransactions();

        $finalTransaction = Transaction::whereKey($transaction->id)->first()
            ?? Transaction::where('user_id', $user->id)
                ->where('metode_pembayaran', $data['metode_pembayaran'])
                ->where('status', Transaction::STATUS_MENUNGGU)
                ->latest('id')
                ->firstOrFail();

        return redirect()
            ->route('transactions.receipt', $finalTransaction)
            ->with('success', sprintf(
                'Checkout berhasil! Stok buku telah dikurangi dan pesanan [%s] sedang menunggu konfirmasi Admin.',
                $finalTransaction->kode_transaksi
            ));
    }

    /**
     * Pembelian langsung 1 buku oleh User atau Kasir Admin.
     */
    public function store(Request $request, Book $book)
    {
        $data = $request->validate([
            'jumlah' => 'required|integer|min:1',
            'metode_pembayaran' => ['nullable', Rule::in(Transaction::PAYMENT_METHODS)],
            'alamat_pengiriman' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        /** @var User $buyer */
        $buyer = (auth()->user()->role === 'admin' && $request->filled('user_id'))
            ? User::findOrFail($request->user_id)
            : auth()->user();

        $jumlah = (int) $data['jumlah'];
        $initialStatus = auth()->user()->role === 'admin'
            ? Transaction::STATUS_SELESAI
            : Transaction::STATUS_MENUNGGU;

        $transaction = DB::transaction(function () use ($book, $buyer, $jumlah, $initialStatus, $data): Transaction {
            $lockedBook = Book::whereKey($book->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBook->stok < $jumlah) {
                throw ValidationException::withMessages([
                    'book' => sprintf(
                        'Maaf, stok buku "%s" tidak mencukupi (Tersedia: %d). Stok tidak boleh minus.',
                        $lockedBook->judul,
                        $lockedBook->stok
                    ),
                ]);
            }

            // Stok buku langsung dikurangi begitu user membeli / kasir mencatat transaksi
            $lockedBook->decrement('stok', $jumlah);

            $hargaSatuan = (int) $lockedBook->harga;
            $subtotal = $hargaSatuan * $jumlah;

            $trx = Transaction::create([
                'user_id' => $buyer->id,
                'tanggal_transaksi' => today(),
                'total_item' => $jumlah,
                'total_harga' => $subtotal,
                'metode_pembayaran' => $data['metode_pembayaran'] ?? 'Tunai Kasir',
                'alamat_pengiriman' => $data['alamat_pengiriman'] ?? 'Pembelian Langsung di Toko',
                'status' => $initialStatus,
                'catatan' => $data['catatan'] ?? null,
            ]);

            TransactionItem::create([
                'transaction_id' => $trx->id,
                'book_id' => $lockedBook->id,
                'jumlah' => $jumlah,
                'harga_satuan' => $hargaSatuan,
                'subtotal' => $subtotal,
            ]);

            return $trx;
        });

        Transaction::syncPendingTransactions();

        return redirect()
            ->route('transactions.index')
            ->with('success', sprintf('Transaksi pembelian [%s] berhasil dicatat.', $transaction->kode_transaksi));
    }

    public function storeByAdmin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'jumlah' => 'required|integer|min:1',
            'metode_pembayaran' => ['required', Rule::in(Transaction::PAYMENT_METHODS)],
        ]);

        $book = Book::findOrFail($request->book_id);

        return $this->store($request, $book);
    }

    /**
     * Admin menyetujui transaksi ('menunggu' -> 'selesai').
     * Stok buku sudah dikurangi saat user checkout, sehingga approval hanya memvalidasi & menyelesaikan status.
     */
    public function approve(Transaction $transaction)
    {
        if ($transaction->status !== Transaction::STATUS_MENUNGGU) {
            return back()->withErrors([
                'transaction' => 'Hanya transaksi berstatus menunggu yang dapat disetujui.',
            ]);
        }

        $this->approveTransaction($transaction);

        return back()->with('success', sprintf(
            'Transaksi [%s] berhasil disetujui dan diselesaikan (SELESAI).',
            $transaction->kode_transaksi
        ));
    }

    public function approveAll(User $user)
    {
        $pendingList = $user->transactions()
            ->where('status', Transaction::STATUS_MENUNGGU)
            ->get();

        if ($pendingList->isEmpty()) {
            return back()->withErrors([
                'transaction' => 'Tidak ada pesanan menunggu untuk pelanggan ini.',
            ]);
        }

        $approved = 0;

        foreach ($pendingList as $trx) {
            $this->approveTransaction($trx);
            $approved++;
        }

        return back()->with('success', sprintf(
            '%d pesanan milik %s berhasil disetujui (SELESAI).',
            $approved,
            $user->name
        ));
    }

    /**
     * Eksekusi persetujuan transaksi dengan row-level locking.
     */
    protected function approveTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $lockedTrx = Transaction::whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTrx->status !== Transaction::STATUS_MENUNGGU) {
                return;
            }

            $lockedTrx->update([
                'status' => Transaction::STATUS_SELESAI,
            ]);
        });
    }

    /**
     * Membatalkan transaksi yang masih berstatus 'menunggu' dan mengembalikan stok buku.
     */
    public function cancel(Transaction $transaction)
    {
        $user = auth()->user();
        abort_unless($user->role === 'admin' || $transaction->user_id === $user->id, 403);

        if ($transaction->status !== Transaction::STATUS_MENUNGGU) {
            return back()->withErrors([
                'transaction' => 'Hanya pesanan yang masih berstatus menunggu yang dapat dibatalkan.',
            ]);
        }

        DB::transaction(function () use ($transaction): void {
            $lockedTrx = Transaction::with('items')
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedTrx->status !== Transaction::STATUS_MENUNGGU) {
                return;
            }

            foreach ($lockedTrx->items as $item) {
                Book::whereKey($item->book_id)
                    ->lockForUpdate()
                    ->increment('stok', $item->jumlah);
            }

            $lockedTrx->update([
                'status' => Transaction::STATUS_DIBATALKAN,
                'catatan' => 'Pesanan dibatalkan dan stok buku telah dikembalikan.',
            ]);
        });

        return redirect()
            ->route('transactions.index')
            ->with('success', sprintf('Transaksi [%s] berhasil dibatalkan dan stok buku telah dikembalikan.', $transaction->kode_transaksi));
    }

    public function receipt(Transaction $transaction)
    {
        $user = auth()->user();
        abort_unless($user->role === 'admin' || $transaction->user_id === $user->id, 403);

        $transaction->load(['items.book.category', 'user']);

        return view('transactions.receipt', ['transaction' => $transaction]);
    }
}

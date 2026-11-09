<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja user beserta daftar alamat pengiriman tersimpan.
     */
    public function index()
    {
        UserAddress::ensureTableExists();

        $cartItems = Cart::with('book.category')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $addresses = UserAddress::where('user_id', auth()->id())
            ->orderByDesc('is_utama')
            ->latest()
            ->get();

        $totalItem = $cartItems->sum('jumlah');
        $totalHarga = $cartItems->sum(fn (Cart $item): int => $item->subtotal);

        return view('user.cart.index', compact('cartItems', 'addresses', 'totalItem', 'totalHarga'));
    }

    /**
     * Menambahkan buku ke dalam keranjang belanja user.
     * Stok buku TIDAK dikurangi saat masuk keranjang (hanya dicek batas stoknya).
     */
    public function store(Request $request, Book $book)
    {
        $data = $request->validate([
            'jumlah' => 'nullable|integer|min:1',
        ], [
            'jumlah.min' => 'Jumlah pembelian minimal 1 eksemplar.',
        ]);

        $qtyToAdd = (int) ($data['jumlah'] ?? 1);

        if ($book->stok < 1) {
            return back()->withErrors([
                'cart' => sprintf('Maaf, stok buku "%s" sedang habis.', $book->judul),
            ]);
        }

        $existingCart = Cart::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->first();

        $newQty = ($existingCart ? $existingCart->jumlah : 0) + $qtyToAdd;

        if ($newQty > $book->stok) {
            return back()->withErrors([
                'cart' => sprintf(
                    'Tidak dapat menambahkan ke keranjang. Stok buku "%s" hanya tersisa %d eksemplar (di keranjang Anda: %d).',
                    $book->judul,
                    $book->stok,
                    $existingCart ? $existingCart->jumlah : 0
                ),
            ]);
        }

        Cart::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'book_id' => $book->id,
            ],
            [
                'jumlah' => $newQty,
            ]
        );

        if ($request->boolean('redirect_cart')) {
            return redirect()
                ->route('cart.index')
                ->with('success', sprintf('Buku "%s" berhasil ditambahkan ke keranjang belanja.', $book->judul));
        }

        return back()->with('success', sprintf('Buku "%s" berhasil ditambahkan ke keranjang belanja.', $book->judul));
    }

    /**
     * Mengubah jumlah (kuantitas) buku di dalam keranjang.
     */
    public function update(Request $request, Cart $cart)
    {
        abort_unless($cart->user_id === auth()->id(), 403);

        $data = $request->validate([
            'jumlah' => 'required|integer|min:1',
        ], [
            'jumlah.min' => 'Jumlah buku di keranjang minimal 1.',
        ]);

        $book = $cart->book;
        if (! $book || $book->stok < 1) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Stok buku ini sedang habis.'], 422);
            }

            return back()->withErrors([
                'cart' => 'Stok buku ini sedang habis.',
            ]);
        }

        $newQty = (int) $data['jumlah'];
        if ($newQty > $book->stok) {
            $msg = sprintf(
                'Jumlah melebihi stok tersedia. Stok buku "%s" saat ini hanya %d eksemplar.',
                $book->judul,
                $book->stok
            );

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg, 'max_stok' => $book->stok], 422);
            }

            return back()->withErrors([
                'cart' => $msg,
            ]);
        }

        $cart->update([
            'jumlah' => $newQty,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'jumlah' => $cart->jumlah,
                'subtotal' => $cart->subtotal,
                'max_stok' => $book->stok,
            ]);
        }

        return back()->with('success', sprintf('Jumlah buku "%s" di keranjang diperbarui.', $book->judul));
    }

    /**
     * Menghapus satu item buku dari keranjang belanja.
     */
    public function destroy(Cart $cart)
    {
        abort_unless($cart->user_id === auth()->id(), 403);

        $judul = $cart->book->judul ?? 'Buku';
        $cart->delete();

        return back()->with('success', sprintf('"%s" telah dihapus dari keranjang belanja.', $judul));
    }
}

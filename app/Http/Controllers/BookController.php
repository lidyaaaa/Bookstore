<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Halaman Katalog Bookstore
     * Mendukung pencarian cepat (Judul, Penulis, Kategori, ISBN, Kode Rak)
     * serta filter ketersediaan stok.
     */
    public function index(Request $request)
    {
        $categories = Category::withCount('books')->orderBy('nama')->get();

        $books = Book::query()
            ->with('category')
            ->withCount('carts')
            ->withSum(['transactionItems as terjual_count' => function ($q): void {
                $q->whereHas('transaction', fn ($tq) => $tq->where('status', '!=', Transaction::STATUS_DIBATALKAN));
            }], 'jumlah')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->search);
                $query->where(function ($q) use ($search): void {
                    $q->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('penerbit', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%")
                        ->orWhere('call_number', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($catQuery) use ($search): void {
                            $catQuery->where('nama', 'like', "%{$search}%")
                                ->orWhere('kode_ddc', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request): void {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->availability === 'available', function ($query): void {
                $query->where('stok', '>', 0);
            })
            ->when($request->availability === 'out_of_stock', function ($query): void {
                $query->where('stok', '<=', 0);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        $categories = Category::withCount('books')->orderBy('nama')->get();

        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'kategori_baru' => 'nullable|string|max:100',
            'judul' => 'required|string|max:255|unique:books,judul',
            'isbn' => 'nullable|string|max:30|unique:books,isbn',
            'call_number' => 'nullable|string|max:50',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun_terbit' => [
                'nullable',
                'integer',
                'min:1000',
                'max:'.date('Y'),
            ],
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string|max:2000',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'judul.unique' => 'Buku dengan judul ini sudah ada di dalam katalog/database.',
            'isbn.unique' => 'Buku dengan nomor ISBN ini sudah terdaftar di database.',
            'stok.min' => 'Stok buku tidak boleh bernilai minus (minimal 0).',
            'harga.min' => 'Harga buku tidak boleh bernilai minus.',
        ]);

        if ($request->filled('kategori_baru')) {
            $newCat = Category::firstOrCreate([
                'nama' => trim((string) $request->kategori_baru),
            ]);
            $data['category_id'] = $newCat->id;
        }
        unset($data['kategori_baru']);

        $data['stok'] = max(0, (int) $data['stok']);
        $data['total_koleksi'] = $data['stok'];
        $data['penerbit'] = $data['penerbit'] ?? '-';
        $data['tahun_terbit'] = $data['tahun_terbit'] ?? date('Y');

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        Book::create($data);

        return redirect()
            ->route('books.index')
            ->with('success', 'Buku baru berhasil ditambahkan ke katalog toko.');
    }

    public function edit(Book $book)
    {
        $categories = Category::withCount('books')->orderBy('nama')->get();

        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'kategori_baru' => 'nullable|string|max:100',
            'judul' => 'required|string|max:255|unique:books,judul,'.$book->id,
            'isbn' => 'nullable|string|max:30|unique:books,isbn,'.$book->id,
            'call_number' => 'nullable|string|max:50',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun_terbit' => [
                'nullable',
                'integer',
                'min:1000',
                'max:'.date('Y'),
            ],
            'harga' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string|max:2000',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'judul.unique' => 'Buku dengan judul ini sudah ada di dalam katalog/database.',
            'isbn.unique' => 'Buku dengan nomor ISBN ini sudah terdaftar di database.',
            'stok.min' => 'Stok buku tidak boleh bernilai minus (minimal 0).',
            'harga.min' => 'Harga buku tidak boleh bernilai minus.',
        ]);

        if ($request->filled('kategori_baru')) {
            $newCat = Category::firstOrCreate([
                'nama' => trim((string) $request->kategori_baru),
            ]);
            $data['category_id'] = $newCat->id;
        }
        unset($data['kategori_baru']);

        $data['stok'] = max(0, (int) $data['stok']);
        $soldCount = (int) $book->transactionItems()
            ->whereHas('transaction', fn ($q) => $q->where('status', '!=', Transaction::STATUS_DIBATALKAN))
            ->sum('jumlah');
        $data['total_koleksi'] = $data['stok'] + $soldCount;
        $data['penerbit'] = $data['penerbit'] ?? '-';
        $data['tahun_terbit'] = $data['tahun_terbit'] ?? date('Y');

        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }

            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);

        return redirect()
            ->route('books.index')
            ->with('success', 'Data buku berhasil diperbarui.');
    }

    /**
     * Hapus buku (Admin).
     * Aturan Bisnis: Buku TIDAK BISA dihapus apabila sedang berada di keranjang belanja user.
     */
    public function destroy(Book $book)
    {
        if ($book->isInAnyCart()) {
            return back()->withErrors([
                'book' => sprintf(
                    'Buku "%s" tidak dapat dihapus karena sedang berada di dalam keranjang belanja pengguna.',
                    $book->judul
                ),
            ]);
        }

        $judul = $book->judul;

        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }

        $book->transactionItems()->delete();
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', "Buku \"{$judul}\" berhasil dihapus dari katalog.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Halaman OPAC (Online Public Access Catalog) & Katalog Buku
     * Mendukung pencarian cepat (Judul, Penulis, Kategori/Subjek, ISBN, Call Number)
     * serta filter ketersediaan stok fisik vs dipinjam.
     */
    public function index(Request $request)
    {
        $categories = Category::withCount('books')->orderBy('nama')->get();

        $books = Book::query()
            ->with('category')
            ->withCount('activeLoans')
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
            ->when($request->availability === 'borrowed', function ($query): void {
                $query->where(function ($q): void {
                    $q->where('stok', 0)
                        ->orWhereHas('activeLoans');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('nama')->get();

        return view('admin.books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'kategori_baru' => 'nullable|string|max:100',
            'judul' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:30',
            'call_number' => 'nullable|string|max:50',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun_terbit' => [
                'nullable',
                'integer',
                'min:1000',
                'max:'.date('Y'),
            ],
            'total_koleksi' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string|max:2000',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Jika admin mengetik nama kategori/subjek baru langsung di form buku
        if ($request->filled('kategori_baru')) {
            $newCat = Category::firstOrCreate([
                'nama' => trim((string) $request->kategori_baru),
            ]);
            $data['category_id'] = $newCat->id;
        }
        unset($data['kategori_baru']);

        // Ketersediaan awal otomatis sama dengan total buku fisik
        $data['stok'] = (int) $data['total_koleksi'];

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        Book::create($data);

        return redirect()
            ->route('books.index')
            ->with('success', 'Koleksi buku berhasil ditambahkan ke katalog OPAC.');
    }

    public function edit(Book $book)
    {
        $categories = Category::orderBy('nama')->get();

        return view('admin.books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'kategori_baru' => 'nullable|string|max:100',
            'judul' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:30',
            'call_number' => 'nullable|string|max:50',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'nullable|string|max:255',
            'tahun_terbit' => [
                'nullable',
                'integer',
                'min:1000',
                'max:'.date('Y'),
            ],
            'total_koleksi' => 'required|integer|min:'.$book->activeLoans()->count(),
            'sinopsis' => 'nullable|string|max:2000',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'total_koleksi.min' => 'Total fisik tidak boleh kurang dari jumlah buku yang sedang dipinjam (:min).',
        ]);

        if ($request->filled('kategori_baru')) {
            $newCat = Category::firstOrCreate([
                'nama' => trim((string) $request->kategori_baru),
            ]);
            $data['category_id'] = $newCat->id;
        }
        unset($data['kategori_baru']);

        // Ketersediaan otomatis = total fisik - buku yang sedang dipinjam
        $data['stok'] = (int) $data['total_koleksi'] - $book->activeLoans()->count();

        if ($request->hasFile('cover')) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }

            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $book->update($data);

        return redirect()
            ->route('books.index')
            ->with('success', 'Data koleksi buku berhasil diperbarui.');
    }

    public function destroy(Book $book)
    {
        $judul = $book->judul;

        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }

        // Hapus juga data pinjaman terkait agar langsung hilang dari sisi user & admin
        $book->loans()->delete();
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', "Koleksi buku \"{$judul}\" beserta riwayat sirkulasinya berhasil dihapus.");
    }
}

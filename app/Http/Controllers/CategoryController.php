<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menambahkan kategori buku baru (Khusus Admin).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:categories,nama',
            'kode_ddc' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        Category::create($data);

        return back()->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    /**
     * Mengubah nama/data kategori buku (Khusus Admin).
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:categories,nama,'.$category->id,
            'kode_ddc' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $category->update($data);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus kategori buku (Khusus Admin).
     * Aturan Bisnis: Kategori TIDAK BISA dihapus apabila masih memiliki buku yang terhubung,
     * atau ada buku di kategori ini yang sedang berada di keranjang user.
     */
    public function destroy(Category $category)
    {
        if ($category->books()->exists()) {
            return back()->withErrors([
                'category' => sprintf(
                    'Kategori "%s" tidak dapat dihapus karena masih memiliki %d buku yang terhubung.',
                    $category->nama,
                    $category->books()->count()
                ),
            ]);
        }

        if ($category->hasBooksInCart()) {
            return back()->withErrors([
                'category' => sprintf(
                    'Kategori "%s" tidak dapat dihapus karena terdapat buku dalam kategori ini yang sedang berada di keranjang belanja pengguna.',
                    $category->nama
                ),
            ]);
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}

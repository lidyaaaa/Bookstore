<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Menambahkan subjek/kategori koleksi baru (Khusus Admin).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:categories,nama',
            'kode_ddc' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        Category::create($data);

        return back()->with('success', 'Subjek / Kategori baru berhasil ditambahkan.');
    }

    /**
     * Mengubah nama/data subjek koleksi (Khusus Admin).
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:categories,nama,'.$category->id,
            'kode_ddc' => 'nullable|string|max:20',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        $category->update($data);

        return back()->with('success', 'Subjek / Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus subjek koleksi (Khusus Admin).
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Subjek / Kategori berhasil dihapus.');
    }
}

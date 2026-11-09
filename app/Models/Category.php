<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'nama',
        'kode_ddc',
        'deskripsi',
    ];

    /**
     * Relasi satu kategori memiliki banyak buku.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Mengecek apakah ada buku dalam kategori ini yang sedang berada di keranjang user.
     */
    public function hasBooksInCart(): bool
    {
        return Cart::whereHas('book', function ($query): void {
            $query->where('category_id', $this->id);
        })->exists();
    }
}

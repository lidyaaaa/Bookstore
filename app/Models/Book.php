<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected $fillable = [
        'category_id',
        'judul',
        'isbn',
        'call_number',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'harga',
        'stok',
        'total_koleksi',
        'cover',
        'sinopsis',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
            'stok' => 'integer',
            'total_koleksi' => 'integer',
        ];
    }

    /**
     * Relasi ke kategori buku.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke item keranjang belanja yang memuat buku ini.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Relasi ke item transaksi penjualan buku ini.
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Mengecek apakah buku ini sedang berada di keranjang belanja user manapun.
     */
    public function isInAnyCart(): bool
    {
        return $this->carts()->exists();
    }

    /**
     * Format harga Rupiah.
     */
    public function getFormattedHargaAttribute(): string
    {
        return 'Rp '.number_format((float) $this->harga, 0, ',', '.');
    }
}

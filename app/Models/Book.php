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
        'stok',
        'total_koleksi',
        'cover',
        'sinopsis',
    ];

    /**
     * Relasi ke kategori/subjek buku.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke seluruh transaksi peminjaman buku ini.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Relasi ke transaksi peminjaman yang sudah disetujui dan sedang aktif memegang fisik buku.
     * Pengajuan yang masih berstatus 'menunggu' belum dianggap dipinjam.
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->where('status', Loan::STATUS_DIPINJAM);
    }
}

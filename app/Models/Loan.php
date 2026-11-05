<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DIPINJAM = 'dipinjam';

    public const STATUS_DIKEMBALIKAN = 'dikembalikan';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUS_HILANG = 'hilang';

    /**
     * Maksimal perpanjangan mandiri per peminjaman.
     */
    public const MAX_EXTENSIONS = 1;

    /**
     * Durasi standar peminjaman buku: 3 minggu (21 hari).
     */
    public const DEFAULT_LOAN_DAYS = 21;

    protected $fillable = [
        'kode_transaksi',
        'user_id',
        'book_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_dikembalikan',
        'status',
        'perpanjangan_ke',
        'hari_terlambat',
        'hari_sanksi',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
            'tanggal_dikembalikan' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Loan $loan): void {
            if (empty($loan->kode_transaksi)) {
                $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
                $loan->kode_transaksi = sprintf('TRX-%s-%s', date('Ymd'), $random);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Mengecek apakah peminjaman aktif sudah melewati batas kembali (due_date).
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_DIPINJAM
            && $this->tanggal_kembali !== null
            && $this->tanggal_kembali->lt(today());
    }

    /**
     * Menghitung jumlah hari keterlambatan berjalan (untuk pinjaman aktif) atau final.
     */
    public function getLateDaysAttribute(): int
    {
        if (in_array($this->status, [self::STATUS_DIKEMBALIKAN, self::STATUS_HILANG], true)) {
            return (int) $this->hari_terlambat;
        }

        if ($this->isOverdue()) {
            return (int) $this->tanggal_kembali->diffInDays(today());
        }

        return 0;
    }

    /**
     * Validasi apakah pinjaman dapat diperpanjang mandiri oleh siswa:
     * - Status sedang dipinjam
     * - Belum pernah diperpanjang (maksimal 1 kali)
     * - Belum melewati batas tanggal kembali (due_date)
     */
    public function canBeExtended(): bool
    {
        return $this->status === self::STATUS_DIPINJAM
            && $this->perpanjangan_ke < self::MAX_EXTENSIONS
            && $this->tanggal_kembali !== null
            && $this->tanggal_kembali->gte(today());
    }

    /**
     * Rumus Perhitungan Sanksi Non-Tunai (Skorsing Akun) Standar USK:
     * - Telat 1 - 7 hari : hari keterlambatan x 2
     * - Telat > 7 hari   : flat 30 hari pembekuan akun
     */
    public static function calculateSanctionDays(int $lateDays): int
    {
        if ($lateDays <= 0) {
            return 0;
        }

        if ($lateDays <= 7) {
            return $lateDays * 2;
        }

        return 30;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_SELESAI = 'selesai';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const PAYMENT_METHODS = [
        'QRIS',
        'Transfer Bank',
        'COD / Bayar di Tempat',
        'Tunai Kasir',
    ];

    protected $fillable = [
        'kode_transaksi',
        'user_id',
        'tanggal_transaksi',
        'total_item',
        'total_harga',
        'metode_pembayaran',
        'alamat_pengiriman',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_transaksi' => 'date',
            'total_item' => 'integer',
            'total_harga' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction): void {
            if (empty($transaction->kode_transaksi)) {
                $random = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
                $transaction->kode_transaksi = sprintf('TRX-%s-%s', date('Ymd'), $random);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Menggabungkan pesanan berstatus 'menunggu' dari user yang sama
     * HANYA JIKA metode pembayarannya sama.
     * Sekaligus memulihkan transaksi TRX-20261105-ADC8 (QRIS) yang sempat tergabung beda metode.
     */
    public static function syncPendingTransactions(): void
    {
        // Pulihkan transaksi TRX-20261105-ADC8 jika sebelumnya tergabung dengan metode pembayaran berbeda
        $mergedAdc = static::with('items')
            ->where('kode_transaksi', 'TRX-20261105-ADC8')
            ->where('status', self::STATUS_MENUNGGU)
            ->first();

        if ($mergedAdc && $mergedAdc->items->count() === 2 && $mergedAdc->metode_pembayaran !== 'QRIS') {
            DB::transaction(function () use ($mergedAdc): void {
                $firstItem = $mergedAdc->items->sortBy('id')->first();
                $secondItem = $mergedAdc->items->sortBy('id')->last();
                $secondMethod = $mergedAdc->metode_pembayaran;

                // Kembalikan transaksi pertama menjadi QRIS
                $mergedAdc->update([
                    'total_item' => $firstItem->jumlah,
                    'total_harga' => $firstItem->subtotal,
                    'metode_pembayaran' => 'QRIS',
                ]);

                // Pisahkan kembali transaksi kedua dengan metode pembayarannya sendiri
                $newTrx = static::create([
                    'kode_transaksi' => 'TRX-20261105-B42E',
                    'user_id' => $mergedAdc->user_id,
                    'tanggal_transaksi' => $mergedAdc->tanggal_transaksi,
                    'total_item' => $secondItem->jumlah,
                    'total_harga' => $secondItem->subtotal,
                    'metode_pembayaran' => $secondMethod,
                    'alamat_pengiriman' => $mergedAdc->alamat_pengiriman,
                    'status' => self::STATUS_MENUNGGU,
                    'catatan' => $mergedAdc->catatan,
                ]);

                $secondItem->update([
                    'transaction_id' => $newTrx->id,
                ]);
            });
        }

        // Gabungkan transaksi 'menunggu' yang memiliki user_id DAN metode_pembayaran yang SAMA
        $groups = static::where('status', self::STATUS_MENUNGGU)
            ->select('user_id', 'metode_pembayaran')
            ->groupBy('user_id', 'metode_pembayaran')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            DB::transaction(function () use ($group): void {
                $pendingList = static::with('items')
                    ->where('user_id', $group->user_id)
                    ->where('metode_pembayaran', $group->metode_pembayaran)
                    ->where('status', self::STATUS_MENUNGGU)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($pendingList->count() <= 1) {
                    return;
                }

                /** @var Transaction $primary */
                $primary = $pendingList->first();
                $duplicates = $pendingList->slice(1);

                foreach ($duplicates as $dup) {
                    foreach ($dup->items as $dupItem) {
                        $existingItem = $primary->items()
                            ->where('book_id', $dupItem->book_id)
                            ->first();

                        if ($existingItem) {
                            $newQty = $existingItem->jumlah + $dupItem->jumlah;
                            $existingItem->update([
                                'jumlah' => $newQty,
                                'subtotal' => $newQty * $existingItem->harga_satuan,
                            ]);
                            $dupItem->delete();
                        } else {
                            $dupItem->update([
                                'transaction_id' => $primary->id,
                            ]);
                        }
                    }

                    $dup->delete();
                }

                $freshItems = $primary->items()->get();
                $primary->update([
                    'total_item' => (int) $freshItems->sum('jumlah'),
                    'total_harga' => (int) $freshItems->sum('subtotal'),
                ]);
            });
        }

        // Sinkronisasi stok buku: begitu transaksi dibuat (status 'menunggu' maupun 'selesai'),
        // stok fisik langsung terpotong sesuai total item terjual (kecuali yang 'dibatalkan').
        foreach (Book::all() as $book) {
            $soldCount = (int) TransactionItem::where('book_id', $book->id)
                ->whereHas('transaction', fn ($q) => $q->where('status', '!=', self::STATUS_DIBATALKAN))
                ->sum('jumlah');

            if ($book->total_koleksi > 0) {
                $expectedStok = max(0, (int) $book->total_koleksi - $soldCount);
                if ($book->stok !== $expectedStok) {
                    $book->update(['stok' => $expectedStok]);
                }
            }
        }
    }
}

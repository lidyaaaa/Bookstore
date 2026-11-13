<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'nomor_anggota',
    'nisn',
    'name',
    'email',
    'password',
    'role',
    'kelas_jabatan',
    'telepon',
    'suspended_until',
    'alasan_suspend',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_until' => 'date',
        ];
    }

    /**
     * Booted hook untuk membuat Nomor Member otomatis saat registrasi.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->nomor_anggota)) {
                $prefix = $user->role === 'admin' ? 'ADM' : 'MBR';
                $random = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
                $user->nomor_anggota = sprintf('%s-%s-%s', $prefix, date('Y'), $random);
            }
        });
    }

    /**
     * Relasi ke item keranjang belanja milik user.
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Relasi ke daftar alamat pengiriman tersimpan milik user.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Relasi pesan terkirim.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Relasi pesan diterima.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Relasi ke seluruh riwayat transaksi pembelian.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Informasi badge status akun pelanggan untuk ditampilkan di Dashboard & Kartu Member.
     *
     * @return array{status: string, color: string, icon: string, message: string}
     */
    public function accountStatusInfo(): array
    {
        return [
            'status' => 'Member Aktif',
            'color' => 'success',
            'icon' => 'bi-check-circle-fill',
            'message' => 'Akun pelanggan aktif dan dapat melakukan pembelian buku di Bookstore.',
        ];
    }
}

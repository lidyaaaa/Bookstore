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
     * Batas maksimal peminjaman buku aktif per siswa sesuai aturan USK.
     */
    public const MAX_ACTIVE_LOANS = 2;

    /**
     * Daftar kelas yang tersedia di sekolah (dipakai pada dropdown registrasi).
     *
     * @var list<string>
     */
    public const KELAS_OPTIONS = [
        'X RPL 1', 'X RPL 2', 'X TKJ 1', 'X TKJ 2',
        'XI RPL 1', 'XI RPL 2', 'XI TKJ 1', 'XI TKJ 2',
        'XII RPL 1', 'XII RPL 2', 'XII TKJ 1', 'XII TKJ 2',
    ];

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
     * Booted hook untuk membuat Nomor Anggota otomatis saat registrasi.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (empty($user->nomor_anggota)) {
                $prefix = $user->role === 'admin' ? 'LIB' : 'MBR';
                $random = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
                $user->nomor_anggota = sprintf('%s-%s-%s', $prefix, date('Y'), $random);
            }
        });
    }

    /**
     * Relasi ke seluruh riwayat transaksi peminjaman.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Relasi ke pinjaman yang sedang berjalan (menunggu persetujuan atau sedang dipinjam).
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->whereIn('status', [
            Loan::STATUS_MENUNGGU,
            Loan::STATUS_DIPINJAM,
        ]);
    }

    /**
     * Mengecek apakah akun memiliki pinjaman yang melewati batas kembali (Overdue Lock).
     * Selama buku menunggak belum dikembalikan, akun otomatis terkunci tanpa batas waktu.
     */
    public function hasOverdueLock(): bool
    {
        return $this->loans()
            ->where('status', Loan::STATUS_DIPINJAM)
            ->whereDate('tanggal_kembali', '<', today())
            ->exists();
    }

    /**
     * Mengecek apakah akun sedang menjalani masa sanksi pembekuan (Suspend).
     */
    public function isSuspended(): bool
    {
        return $this->suspended_until !== null && $this->suspended_until->gte(today());
    }

    /**
     * Menghitung sisa hari pembekuan akun.
     */
    public function remainingSuspendDays(): int
    {
        if (! $this->isSuspended()) {
            return 0;
        }

        return (int) today()->diffInDays($this->suspended_until) + 1;
    }

    /**
     * Evaluasi kelayakan peminjaman buku baru beserta alasan penolakannya.
     *
     * @return array{allowed: bool, reason: ?string}
     */
    public function evaluateBorrowEligibility(): array
    {
        if ($this->hasOverdueLock()) {
            return [
                'allowed' => false,
                'reason' => 'Akun Anda TERKUNCI OTOMATIS (Overdue Lock) karena terdapat buku yang melewati batas waktu pengembalian. Harap kembalikan buku tersebut terlebih dahulu.',
            ];
        }

        if ($this->isSuspended()) {
            return [
                'allowed' => false,
                'reason' => sprintf(
                    'Akun Anda sedang DIBEKUKAN (Suspend) hingga %s (%d hari lagi). Alasan: %s',
                    $this->suspended_until->format('d-m-Y'),
                    $this->remainingSuspendDays(),
                    $this->alasan_suspend ?? 'Sanksi keterlambatan/buku hilang'
                ),
            ];
        }

        $activeCount = $this->activeLoans()->count();
        if ($activeCount >= self::MAX_ACTIVE_LOANS) {
            return [
                'allowed' => false,
                'reason' => sprintf(
                    'Kuota peminjaman penuh (%d/%d buku aktif). Selesaikan pinjaman sebelumnya untuk meminjam buku baru.',
                    $activeCount,
                    self::MAX_ACTIVE_LOANS
                ),
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
        ];
    }

    /**
     * Menjatuhkan sanksi pembekuan akun non-tunai (skorsing peminjaman).
     */
    public function applySanction(int $days, string $reason): void
    {
        if ($days <= 0) {
            return;
        }

        $baseDate = ($this->suspended_until !== null && $this->suspended_until->gt(today()))
            ? $this->suspended_until
            : today();

        $this->update([
            'suspended_until' => $baseDate->copy()->addDays($days),
            'alasan_suspend' => $reason,
        ]);
    }

    /**
     * Informasi badge status akun untuk ditampilkan di Dashboard & Kartu Anggota.
     *
     * @return array{status: string, color: string, icon: string, message: string}
     */
    public function accountStatusInfo(): array
    {
        if ($this->hasOverdueLock()) {
            return [
                'status' => 'Terkunci Menunggak',
                'color' => 'danger',
                'icon' => 'bi-lock-fill',
                'message' => 'Terdapat buku yang belum dikembalikan melewati jatuh tempo. Akun dikunci sampai buku diselesaikan.',
            ];
        }

        if ($this->isSuspended()) {
            return [
                'status' => 'Dibekukan Sanksi',
                'color' => 'warning',
                'icon' => 'bi-slash-circle-fill',
                'message' => sprintf(
                    'Dibekukan s/d %s (%s).',
                    $this->suspended_until->format('d M Y'),
                    $this->alasan_suspend ?? 'Sanksi perpustakaan'
                ),
            ];
        }

        return [
            'status' => 'Aktif',
            'color' => 'success',
            'icon' => 'bi-check-circle-fill',
            'message' => 'Akun aktif dan dapat melakukan peminjaman koleksi perpustakaan.',
        ];
    }
}

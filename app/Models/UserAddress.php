<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'penerima',
        'telepon',
        'alamat_lengkap',
        'is_utama',
    ];

    protected function casts(): array
    {
        return [
            'is_utama' => 'boolean',
        ];
    }

    public static function ensureTableExists(): void
    {
        if (! Schema::hasTable('user_addresses')) {
            Schema::create('user_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('label', 50)->default('Rumah');
                $table->string('penerima', 100);
                $table->string('telepon', 30);
                $table->text('alamat_lengkap');
                $table->boolean('is_utama')->default(false);
                $table->timestamps();
            });
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        return sprintf(
            '[%s] %s (%s) — %s',
            $this->label,
            $this->penerima,
            $this->telepon,
            $this->alamat_lengkap
        );
    }
}

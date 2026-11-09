<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Hanya membuat seeder akun tanpa data dummy buku, kategori, atau transaksi.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nomor_anggota' => 'ADM-2026-0001',
                'name' => 'Admin Toko Buku',
                'password' => Hash::make('Admin12345'),
                'role' => 'admin',
                'kelas_jabatan' => 'Kepala Toko / Admin',
                'telepon' => '081200001111',
            ]
        );

        User::updateOrCreate(
            ['email' => 'siswa@gmail.com'],
            [
                'nomor_anggota' => 'MBR-2026-0001',
                'name' => 'daniel',
                'password' => Hash::make('Siswa12345'),
                'role' => 'user',
                'kelas_jabatan' => 'Pelanggan Umum',
                'telepon' => '081234567890',
            ]
        );
    }
}

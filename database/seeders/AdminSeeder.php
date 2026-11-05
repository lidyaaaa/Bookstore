<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@perpus.test'],
            [
                'nomor_anggota' => 'LIB-2026-0001',
                'name' => 'Admin Pustakawan',
                'password' => Hash::make('Admin12345'),
                'role' => 'admin',
                'kelas_jabatan' => 'Kepala Pustakawan',
                'telepon' => '081200001111',
            ]
        );

        User::updateOrCreate(
            ['email' => 'siswa@perpus.test'],
            [
                'nomor_anggota' => 'MBR-2026-0001',
                'name' => 'Budi Santoso',
                'password' => Hash::make('Siswa12345'),
                'role' => 'user',
                'kelas_jabatan' => 'XII RPL 1',
                'telepon' => '081234567890',
            ]
        );

        // Perbarui nama kategori lama yang kaku jika sudah terlanjur ada di DB
        Category::where('nama', 'Rekayasa Perangkat Lunak')->update(['nama' => 'Teknologi']);
        Category::where('nama', 'Jaringan & Keamanan Siber')->update(['nama' => 'Komputer & Jaringan']);
        Category::where('nama', 'Sastra & Fiksi Nusantara')->update(['nama' => 'Novel']);

        $catRpl = Category::updateOrCreate(
            ['nama' => 'Teknologi'],
            ['kode_ddc' => '005', 'deskripsi' => 'Rak A1 - Pemrograman & Teknologi']
        );

        $catJaringan = Category::updateOrCreate(
            ['nama' => 'Komputer & Jaringan'],
            ['kode_ddc' => '004', 'deskripsi' => 'Rak A2 - Infrastruktur & Sistem']
        );

        $catSastra = Category::updateOrCreate(
            ['nama' => 'Novel'],
            ['kode_ddc' => '899', 'deskripsi' => 'Rak B1 - Novel & Fiksi']
        );

        Category::updateOrCreate(
            ['nama' => 'Sains & Umum'],
            ['kode_ddc' => '500', 'deskripsi' => 'Rak C1 - Sains & Pengetahuan Umum']
        );

        $books = [
            [
                'isbn' => '978-623-001-101-2',
                'judul' => 'Arsitektur Aplikasi Web Modern dengan Laravel',
                'call_number' => '005.133 PRA a',
                'category_id' => $catRpl->id,
                'penulis' => 'Rizky Pratama, M.Kom.',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2025,
                'stok' => 4,
                'total_koleksi' => 4,
                'sinopsis' => 'Panduan komprehensif membangun aplikasi enterprise dengan Laravel, pola MVC, keamanan autentikasi, dan pengujian otomatis.',
            ],
            [
                'isbn' => '978-623-001-205-7',
                'judul' => 'Desain Basis Data Relasional & Optimasi SQL',
                'call_number' => '005.74 HAK d',
                'category_id' => $catRpl->id,
                'penulis' => 'Dr. Lukman Hakim',
                'penerbit' => 'Andi Offset',
                'tahun_terbit' => 2024,
                'stok' => 3,
                'total_koleksi' => 3,
                'sinopsis' => 'Membahas normalisasi ERD, indexing, transaksi ACID, serta teknik pencegahan race-condition pada sistem informasi.',
            ],
            [
                'isbn' => '978-602-881-412-9',
                'judul' => 'Administrasi Server Linux & Keamanan Jaringan',
                'call_number' => '004.65 SET a',
                'category_id' => $catJaringan->id,
                'penulis' => 'Hendra Setiawan',
                'penerbit' => 'Elex Media Komputindo',
                'tahun_terbit' => 2024,
                'stok' => 2,
                'total_koleksi' => 2,
                'sinopsis' => 'Panduan konfigurasi firewall, hardening server, dan manajemen layanan cloud untuk uji kompetensi keahlian.',
            ],
            [
                'isbn' => '978-979-3062-79-2',
                'judul' => 'Laskar Pelangi',
                'call_number' => '899.221 HIR l',
                'category_id' => $catSastra->id,
                'penulis' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka',
                'tahun_terbit' => 2005,
                'stok' => 5,
                'total_koleksi' => 5,
                'sinopsis' => 'Kisah inspiratif perjuangan sepuluh anak Belitung dalam menempuh pendidikan dengan penuh semangat dan persahabatan.',
            ],
        ];

        if (Book::count() === 0) {
            foreach ($books as $bookData) {
                Book::updateOrCreate(
                    ['isbn' => $bookData['isbn']],
                    $bookData
                );
            }
        }

        // Backfill data lama yang sudah ada sebelum migrasi USK
        User::whereNull('nomor_anggota')->get()->each(function (User $u): void {
            $prefix = $u->role === 'admin' ? 'LIB' : 'MBR';
            $u->update([
                'nomor_anggota' => sprintf('%s-%s-%04d', $prefix, date('Y'), $u->id),
                'kelas_jabatan' => $u->kelas_jabatan ?? ($u->role === 'admin' ? 'Pustakawan' : 'Siswa / Anggota'),
            ]);
        });

        Book::where('total_koleksi', 0)->get()->each(function (Book $b): void {
            $b->update(['total_koleksi' => $b->stok]);
        });

        Loan::whereNull('kode_transaksi')->get()->each(function (Loan $l): void {
            $l->update(['kode_transaksi' => sprintf('TRX-%s-%04d', date('Ymd'), $l->id)]);
        });

        // Pastikan pinjaman aktif yang jatuh temponya di hari yang sama disesuaikan menjadi 3 minggu (21 hari)
        Loan::whereIn('status', [Loan::STATUS_MENUNGGU, Loan::STATUS_DIPINJAM])
            ->get()
            ->each(function (Loan $l): void {
                if ($l->tanggal_pinjam && $l->tanggal_kembali && $l->tanggal_kembali->lte($l->tanggal_pinjam->copy()->addDays(7))) {
                    $l->update([
                        'tanggal_kembali' => $l->tanggal_pinjam->copy()->addDays(Loan::DEFAULT_LOAN_DAYS),
                    ]);
                }
            });
    }
}

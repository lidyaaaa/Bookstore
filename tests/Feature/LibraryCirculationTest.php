<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryCirculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_opac_search_and_availability_filter(): void
    {
        $category = Category::create(['nama' => 'Pemrograman', 'kode_ddc' => '005']);
        Book::create([
            'category_id' => $category->id,
            'judul' => 'Pemrograman Laravel Modern',
            'isbn' => '978-602-111',
            'call_number' => '005.133 LAR',
            'penulis' => 'Budi Raharjo',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2025,
            'stok' => 3,
            'total_koleksi' => 3,
        ]);

        $response = $this->get('/?search=978-602-111&availability=available');
        $response->assertOk();
        $response->assertSee('Pemrograman Laravel Modern');
        $response->assertSee('005.133 LAR');
    }

    public function test_quota_limit_max_two_active_books(): void
    {
        $student = User::factory()->create(['role' => 'user']);
        $books = collect(range(1, 3))->map(fn ($i) => Book::create([
            'judul' => "Buku Uji {$i}",
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 2,
            'total_koleksi' => 2,
        ]));

        $this->actingAs($student);

        // Pinjam buku 1 & 2 berhasil
        $this->post(route('loans.store', $books[0]), [
            'tanggal_kembali' => today()->addDays(5)->toDateString(),
        ])->assertRedirect(route('loans.index'));

        $this->post(route('loans.store', $books[1]), [
            'tanggal_kembali' => today()->addDays(5)->toDateString(),
        ])->assertRedirect(route('loans.index'));

        // Pinjam buku ke-3 ditolak karena batas kuota 2 buku aktif
        $this->post(route('loans.store', $books[2]), [
            'tanggal_kembali' => today()->addDays(5)->toDateString(),
        ])->assertSessionHasErrors('loan');

        $this->assertEquals(2, $student->activeLoans()->count());
    }

    public function test_overdue_lock_blocks_new_borrowing(): void
    {
        $student = User::factory()->create(['role' => 'user']);
        $book1 = Book::create([
            'judul' => 'Buku Lama Menunggak',
            'penulis' => 'Penulis A',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 1,
            'total_koleksi' => 2,
        ]);
        $book2 = Book::create([
            'judul' => 'Buku Baru',
            'penulis' => 'Penulis B',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 2,
            'total_koleksi' => 2,
        ]);

        // Buat pinjaman yang sudah lewat jatuh tempo (Overdue)
        Loan::create([
            'user_id' => $student->id,
            'book_id' => $book1->id,
            'tanggal_pinjam' => today()->subDays(20),
            'tanggal_kembali' => today()->subDays(10),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $this->assertTrue($student->hasOverdueLock());

        $this->actingAs($student)
            ->post(route('loans.store', $book2), [
                'tanggal_kembali' => today()->addDays(5)->toDateString(),
            ])
            ->assertSessionHasErrors('loan');
    }

    public function test_late_return_applies_correct_non_cash_sanctions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentShortLate = User::factory()->create(['role' => 'user']);
        $studentLongLate = User::factory()->create(['role' => 'user']);

        $book = Book::create([
            'judul' => 'Buku Algoritma',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 1,
            'total_koleksi' => 3,
        ]);

        // Kasus 1: Telat 4 hari (1-7 hari) -> Sanksi 4 x 2 = 8 hari
        $loanShort = Loan::create([
            'user_id' => $studentShortLate->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => today()->subDays(11),
            'tanggal_kembali' => today()->subDays(4),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $this->actingAs($admin)->post(route('loans.return', $loanShort));

        $loanShort->refresh();
        $studentShortLate->refresh();
        $this->assertEquals(4, $loanShort->hari_terlambat);
        $this->assertEquals(8, $loanShort->hari_sanksi);
        $this->assertTrue($studentShortLate->isSuspended());
        $this->assertEquals(today()->addDays(8)->toDateString(), $studentShortLate->suspended_until->toDateString());

        // Kasus 2: Telat 12 hari (> 7 hari) -> Sanksi flat 30 hari
        $loanLong = Loan::create([
            'user_id' => $studentLongLate->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => today()->subDays(20),
            'tanggal_kembali' => today()->subDays(12),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $this->actingAs($admin)->post(route('loans.return', $loanLong));

        $loanLong->refresh();
        $studentLongLate->refresh();
        $this->assertEquals(12, $loanLong->hari_terlambat);
        $this->assertEquals(30, $loanLong->hari_sanksi);
        $this->assertEquals(today()->addDays(30)->toDateString(), $studentLongLate->suspended_until->toDateString());
    }

    public function test_lost_book_does_not_restore_stock_and_suspends_for_30_days(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'user']);
        $book = Book::create([
            'judul' => 'Buku Jaringan',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 2,
            'total_koleksi' => 3,
        ]);

        $loan = Loan::create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => today()->subDays(3),
            'tanggal_kembali' => today()->addDays(4),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        $this->actingAs($admin)->post(route('loans.lost', $loan));

        $book->refresh();
        $loan->refresh();
        $student->refresh();

        $this->assertEquals(Loan::STATUS_HILANG, $loan->status);
        $this->assertEquals(2, $book->stok); // Stok tidak bertambah kembali
        $this->assertEquals(2, $book->total_koleksi); // Total fisik berkurang 1
        $this->assertTrue($student->isSuspended());
        $this->assertEquals(today()->addDays(30)->toDateString(), $student->suspended_until->toDateString());
    }

    public function test_self_extension_allowed_once_before_deadline(): void
    {
        $student = User::factory()->create(['role' => 'user']);
        $book = Book::create([
            'judul' => 'Buku Basis Data',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2024,
            'stok' => 1,
            'total_koleksi' => 2,
        ]);

        $initialDue = today()->addDays(3);
        $loan = Loan::create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => today()->subDays(4),
            'tanggal_kembali' => $initialDue,
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        // Admin tidak boleh memperpanjang pinjaman (hanya pengguna biasa)
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('loans.extend', $loan))
            ->assertForbidden();

        // Perpanjangan pertama oleh siswa berhasil (+7 hari)
        $this->actingAs($student)->post(route('loans.extend', $loan));
        $loan->refresh();
        $this->assertEquals(1, $loan->perpanjangan_ke);
        $this->assertEquals($initialDue->copy()->addDays(7)->toDateString(), $loan->tanggal_kembali->toDateString());

        // Perpanjangan kedua ditolak (maksimal 1x)
        $this->actingAs($student)
            ->post(route('loans.extend', $loan))
            ->assertSessionHasErrors('loan');
    }

    public function test_all_pages_render_without_blade_errors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'user']);
        $book = Book::create([
            'judul' => 'Buku Render Uji',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'stok' => 3,
            'total_koleksi' => 3,
        ]);
        $loan = Loan::create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'tanggal_pinjam' => today(),
            'tanggal_kembali' => today()->addDays(7),
            'status' => Loan::STATUS_DIPINJAM,
        ]);

        // Test seluruh halaman Admin
        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('books.index'))->assertOk();
        $this->actingAs($admin)->get(route('books.create'))->assertOk();
        $this->actingAs($admin)->get(route('books.edit', $book))->assertOk();
        $this->actingAs($admin)->get(route('loans.index'))->assertOk();
        $this->actingAs($admin)->get(route('loans.receipt', $loan))->assertOk();
        $this->actingAs($admin)->get(route('reports.index'))->assertOk();

        // Test seluruh halaman Siswa
        $this->actingAs($student)->get(route('dashboard'))->assertOk();
        $this->actingAs($student)->get(route('books.index'))->assertOk();
        $this->actingAs($student)->get(route('loans.index'))->assertOk();
        $this->actingAs($student)->get(route('loans.receipt', $loan))->assertOk();
        $this->actingAs($student)->get(route('member.card'))->assertOk();
    }

    public function test_pending_loan_does_not_decrement_stock_or_count_as_borrowed_until_approved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'user']);
        $book = Book::create([
            'judul' => 'Buku Menunggu ACC',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'stok' => 4,
            'total_koleksi' => 4,
        ]);

        // Siswa baru mengajukan pinjam (status: menunggu)
        $this->actingAs($student)->post(route('loans.store', $book));

        $book->refresh();
        $this->assertEquals(4, $book->stok);
        $this->assertEquals(0, $book->activeLoans()->count());

        // Setelah di-ACC Admin (status: dipinjam), barulah stok berkurang & dihitung dipinjam
        $loan = Loan::first();
        $this->actingAs($admin)->post(route('loans.approve', $loan));

        $book->refresh();
        $loan->refresh();
        $this->assertEquals(Loan::STATUS_DIPINJAM, $loan->status);
        $this->assertEquals(3, $book->stok);
        $this->assertEquals(1, $book->activeLoans()->count());
    }

    public function test_admin_can_approve_all_pending_loans_for_a_user_at_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'user']);
        $book1 = Book::create([
            'judul' => 'Buku Satu',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'stok' => 5,
            'total_koleksi' => 5,
        ]);
        $book2 = Book::create([
            'judul' => 'Buku Dua',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'stok' => 5,
            'total_koleksi' => 5,
        ]);

        $this->actingAs($student)->post(route('loans.store', $book1));
        $this->actingAs($student)->post(route('loans.store', $book2));

        $this->actingAs($admin)
            ->post(route('loans.approve-all', $student))
            ->assertRedirect();

        $this->assertEquals(2, $student->loans()->where('status', Loan::STATUS_DIPINJAM)->count());
        $this->assertEquals(4, $book1->fresh()->stok);
        $this->assertEquals(4, $book2->fresh()->stok);
    }

    public function test_book_availability_automatically_follows_total_physical_collection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'user']);

        // 1. Admin tambah buku dengan total fisik 10 -> ketersediaan (stok) otomatis 10
        $this->actingAs($admin)->post(route('books.store'), [
            'judul' => 'Buku Fisik Otomatis',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'total_koleksi' => 10,
        ])->assertRedirect(route('books.index'));

        $book = Book::where('judul', 'Buku Fisik Otomatis')->firstOrFail();
        $this->assertEquals(10, $book->total_koleksi);
        $this->assertEquals(10, $book->stok);

        // 2. Dipinjam 1 buku -> stok otomatis berkurang 1 jadi 9
        $this->actingAs($admin)->post(route('loans.store-admin'), [
            'user_id' => $student->id,
            'book_id' => $book->id,
        ]);
        $book->refresh();
        $this->assertEquals(9, $book->stok);

        // 3. Admin mengedit total buku fisik menjadi 20 -> stok otomatis jadi 19 (karena 1 sedang dipinjam)
        $this->actingAs($admin)->put(route('books.update', $book), [
            'judul' => 'Buku Fisik Otomatis',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'total_koleksi' => 20,
        ])->assertRedirect(route('books.index'));

        $book->refresh();
        $this->assertEquals(20, $book->total_koleksi);
        $this->assertEquals(19, $book->stok);
    }

    public function test_registration_validates_nisn_phone_and_class_dropdown(): void
    {
        // Gagal jika telepon kurang dari 10 digit atau kelas tidak valid
        $this->post(route('register.store'), [
            'name' => 'Siswa Baru',
            'nisn' => '12345',
            'email' => 'siswa@sekolah.sch.id',
            'kelas_jabatan' => 'Kelas Asal',
            'telepon' => '08123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['nisn', 'kelas_jabatan', 'telepon']);

        // Berhasil jika NISN 10 digit angka, telepon 10-13 digit angka, dan kelas sesuai dropdown
        $this->post(route('register.store'), [
            'name' => 'Siswa Baru',
            'nisn' => '0012345678',
            'email' => 'siswa@sekolah.sch.id',
            'kelas_jabatan' => 'XII RPL 1',
            'telepon' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'siswa@sekolah.sch.id',
            'nisn' => '0012345678',
            'kelas_jabatan' => 'XII RPL 1',
            'telepon' => '081234567890',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookstoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_without_kelas_input(): void
    {
        // Password tanpa simbol harus ditolak
        $this->post(route('register.store'), [
            'name' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'telepon' => '081234567899',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertSessionHasErrors('password');

        // Password dengan kombinasi huruf, angka, dan simbol berhasil
        $response = $this->post(route('register.store'), [
            'name' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'telepon' => '081234567899',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('users', [
            'email' => 'andi@example.com',
            'kelas_jabatan' => 'Pelanggan Umum',
            'role' => 'user',
        ]);
    }

    public function test_cart_and_checkout_transaction_flow_and_non_negative_stock(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::create(['nama' => 'Teknologi']);
        $book = Book::create([
            'category_id' => $category->id,
            'judul' => 'Arsitektur Aplikasi Web Modern dengan Laravel',
            'penulis' => 'Rizky Pratama',
            'penerbit' => 'Informatika',
            'tahun_terbit' => 2025,
            'harga' => 95000,
            'stok' => 3,
            'total_koleksi' => 3,
        ]);

        // 1. User gagal tambah ke keranjang jika melebihi stok (stok tidak boleh minus)
        $this->actingAs($user)
            ->post(route('cart.store', $book), ['jumlah' => 5])
            ->assertSessionHasErrors('cart');

        // 2. User berhasil tambah 2 buku ke keranjang
        $this->actingAs($user)
            ->post(route('cart.store', $book), ['jumlah' => 2])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'jumlah' => 2,
        ]);

        // 3. Checkout keranjang menjadi transaksi & mengurangi stok secara akurat (3 - 2 = 1)
        $this->actingAs($user)
            ->post(route('transactions.checkout'), [
                'metode_pembayaran' => 'QRIS',
                'alamat_pengiriman' => 'Jl. Merdeka No. 10, Jakarta',
                'catatan' => 'Kirim cepat',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);
        $this->assertSame(1, $book->fresh()->stok);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'total_item' => 2,
            'total_harga' => 190000,
            'status' => Transaction::STATUS_MENUNGGU,
        ]);
    }

    public function test_admin_cannot_delete_book_or_category_when_book_is_in_user_cart(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $category = Category::create(['nama' => 'Novel']);
        $book = Book::create([
            'category_id' => $category->id,
            'judul' => 'Laskar Pelangi',
            'penulis' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun_terbit' => 2014,
            'harga' => 85000,
            'stok' => 5,
            'total_koleksi' => 5,
        ]);

        // User menambahkan buku ke keranjang
        Cart::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'jumlah' => 1,
        ]);

        // Admin mencoba menghapus buku -> Ditolak!
        $this->actingAs($admin)
            ->delete(route('books.destroy', $book))
            ->assertSessionHasErrors('book');

        $this->assertDatabaseHas('books', ['id' => $book->id]);

        // Admin mencoba menghapus kategori dari buku tersebut -> Ditolak!
        $this->actingAs($admin)
            ->delete(route('categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_role_boundary_and_no_cache_back_button_protection(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // 1. User yang sudah login tidak bisa kembali ke halaman /login atau /register (otomatis ke /dashboard)
        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($admin)
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));

        // 2. Pembatas Role: Admin tidak bisa masuk ke halaman khusus User (/keranjang, /kartu-member) saat tekan tombol Back
        $this->actingAs($admin)
            ->get(route('cart.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('role');

        $this->actingAs($admin)
            ->get(route('member.card'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('role');

        // 3. Pembatas Role: User biasa tidak bisa masuk ke halaman khusus Admin (/laporan, /books/create) saat tekan tombol Back
        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('role');

        $this->actingAs($user)
            ->get(route('books.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('role');

        // 4. Header anti-cache (PreventBackHistory) selalu aktif agar browser tidak menampilkan halaman lama dari cache tombol Back
        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertHeader('Pragma', 'no-cache');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}

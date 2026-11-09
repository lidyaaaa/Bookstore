<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryCirculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_search_and_availability_filter(): void
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
            'harga' => 110000,
            'stok' => 3,
            'total_koleksi' => 3,
        ]);

        $response = $this->get('/?search=978-602-111&availability=available');
        $response->assertOk();
        $response->assertSee('Pemrograman Laravel Modern');
        $response->assertSee('Rp 110.000');
    }

    public function test_all_pages_render_without_blade_errors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $book = Book::create([
            'judul' => 'Buku Render Uji',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'harga' => 80000,
            'stok' => 3,
            'total_koleksi' => 3,
        ]);

        $trx = Transaction::create([
            'user_id' => $customer->id,
            'tanggal_transaksi' => today(),
            'total_item' => 1,
            'total_harga' => 80000,
            'metode_pembayaran' => 'QRIS',
            'status' => Transaction::STATUS_MENUNGGU,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx->id,
            'book_id' => $book->id,
            'jumlah' => 1,
            'harga_satuan' => 80000,
            'subtotal' => 80000,
        ]);

        // Test seluruh halaman Admin
        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('books.index'))->assertOk();
        $this->actingAs($admin)->get(route('books.create'))->assertOk();
        $this->actingAs($admin)->get(route('books.edit', $book))->assertOk();
        $this->actingAs($admin)->get(route('transactions.index'))->assertOk();
        $this->actingAs($admin)->get(route('transactions.receipt', $trx))->assertOk();
        $this->actingAs($admin)->get(route('reports.index'))->assertOk();

        // Test seluruh halaman Pelanggan (termasuk Keranjang / Cart)
        $this->actingAs($customer)->get(route('dashboard'))->assertOk();
        $this->actingAs($customer)->get(route('books.index'))->assertOk();
        $this->actingAs($customer)->get(route('cart.index'))->assertOk();
        $this->actingAs($customer)->get(route('transactions.index'))->assertOk();
        $this->actingAs($customer)->get(route('transactions.receipt', $trx))->assertOk();
        $this->actingAs($customer)->get(route('member.card'))->assertOk();
    }

    public function test_admin_can_create_and_update_book_and_stock_cannot_be_negative(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Stok minus ditolak
        $this->actingAs($admin)->post(route('books.store'), [
            'judul' => 'Buku Minus',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'harga' => 50000,
            'stok' => -5,
        ])->assertSessionHasErrors('stok');

        // Stok >= 0 berhasil
        $this->actingAs($admin)->post(route('books.store'), [
            'judul' => 'Buku Baru Toko',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'harga' => 90000,
            'stok' => 10,
        ])->assertRedirect(route('books.index'));

        $book = Book::where('judul', 'Buku Baru Toko')->firstOrFail();
        $this->assertSame(10, $book->stok);
        $this->assertSame(90000, $book->harga);
    }

    public function test_cancelling_pending_transaction_restores_book_stock(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $book = Book::create([
            'judul' => 'Buku Batal Beli',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2025,
            'harga' => 60000,
            'stok' => 5,
            'total_koleksi' => 5,
        ]);

        // Beli 2 eksemplar -> stok menjadi 3
        $this->actingAs($customer)->post(route('transactions.store', $book), [
            'jumlah' => 2,
            'metode_pembayaran' => 'QRIS',
            'alamat_pengiriman' => 'Jl. Sudirman No. 1',
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(3, $book->fresh()->stok);

        // Batalkan pesanan -> stok kembali menjadi 5
        $trx = Transaction::latest('id')->firstOrFail();
        $this->actingAs($customer)->post(route('transactions.cancel', $trx))
            ->assertRedirect(route('transactions.index'));

        $this->assertSame(5, $book->fresh()->stok);
        $this->assertSame(Transaction::STATUS_DIBATALKAN, $trx->fresh()->status);
    }
}

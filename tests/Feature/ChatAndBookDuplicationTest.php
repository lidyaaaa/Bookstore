<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Message;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatAndBookDuplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_add_duplicate_book_by_title_or_isbn(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Book::create([
            'judul' => 'Laskar Pelangi',
            'isbn' => '978-979-3062-79-2',
            'penulis' => 'Andrea Hirata',
            'penerbit' => 'Bentang Pustaka',
            'tahun_terbit' => 2005,
            'harga' => 85000,
            'stok' => 10,
            'total_koleksi' => 10,
        ]);

        // Attempt 1: duplicate judul
        $response1 = $this->actingAs($admin)->post(route('books.store'), [
            'judul' => 'Laskar Pelangi',
            'isbn' => '978-602-0000-00-1',
            'penulis' => 'Penulis Lain',
            'penerbit' => 'Penerbit Lain',
            'tahun_terbit' => 2020,
            'harga' => 90000,
            'stok' => 5,
        ]);

        $response1->assertSessionHasErrors('judul');

        // Attempt 2: duplicate isbn
        $response2 = $this->actingAs($admin)->post(route('books.store'), [
            'judul' => 'Judul Buku Berbeda',
            'isbn' => '978-979-3062-79-2',
            'penulis' => 'Penulis Lain',
            'penerbit' => 'Penerbit Lain',
            'tahun_terbit' => 2020,
            'harga' => 90000,
            'stok' => 5,
        ]);

        $response2->assertSessionHasErrors('isbn');

        $this->assertDatabaseCount('books', 1);
    }

    public function test_customer_can_send_chat_to_admin_and_saved_to_database(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($customer)->post(route('chat.store'), [
            'receiver_id' => $admin->id,
            'message' => 'Halo admin, apakah buku ini ready?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'sender_id' => $customer->id,
            'receiver_id' => $admin->id,
            'message' => 'Halo admin, apakah buku ini ready?',
            'is_read' => false,
        ]);
    }

    public function test_admin_can_chat_customer_linked_to_transaction(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $transaction = Transaction::create([
            'kode_transaksi' => 'TRX-TEST-001',
            'user_id' => $customer->id,
            'tanggal_transaksi' => now()->toDateString(),
            'total_item' => 1,
            'total_harga' => 50000,
            'metode_pembayaran' => 'QRIS',
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($admin)->post(route('chat.store'), [
            'receiver_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'message' => 'Pesanan Anda TRX-TEST-001 sedang kami packing ya.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('messages', [
            'sender_id' => $admin->id,
            'receiver_id' => $customer->id,
            'transaction_id' => $transaction->id,
            'message' => 'Pesanan Anda TRX-TEST-001 sedang kami packing ya.',
        ]);

        // Customer opens chat and marks message as read
        $this->actingAs($customer)->get(route('chat.index', ['transaction_id' => $transaction->id]));

        $this->assertDatabaseHas('messages', [
            'sender_id' => $admin->id,
            'receiver_id' => $customer->id,
            'is_read' => true,
        ]);
    }
}

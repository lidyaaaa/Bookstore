<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyamakan struktur tabel dari perpustakaan (2).sql (tanpa data dummy)
     * lalu menambahkan struktur Bookstore (harga buku, carts, transactions, transaction_items).
     */
    public function up(): void
    {
        if (! Schema::hasTable('anggota')) {
            Schema::create('anggota', function (Blueprint $table) {
                $table->char('NIS', 8)->primary();
                $table->string('Nama', 50)->nullable();
                $table->string('Kelas', 10)->nullable();
                $table->string('Alamat', 100)->nullable();
            });
        }

        if (! Schema::hasTable('buku')) {
            Schema::create('buku', function (Blueprint $table) {
                $table->char('KodeBuku', 6)->primary();
                $table->string('Judul', 100)->nullable();
                $table->string('Pengarang', 50)->nullable();
                $table->year('TahunTerbit')->nullable();
            });
        }

        if (! Schema::hasTable('peminjaman')) {
            Schema::create('peminjaman', function (Blueprint $table) {
                $table->integer('NoPinjam')->autoIncrement();
                $table->char('NIS', 8)->nullable()->index();
                $table->char('KodeBuku', 6)->nullable()->index();
                $table->date('TglPinjam')->nullable();
                $table->date('TglKembali')->nullable();
            });
        }

        if (! Schema::hasColumn('books', 'harga')) {
            Schema::table('books', function (Blueprint $table) {
                $table->unsignedBigInteger('harga')->default(0)->after('tahun_terbit');
            });
        }

        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('book_id')->constrained('books')->restrictOnDelete();
                $table->unsignedInteger('jumlah')->default(1);
                $table->timestamps();

                $table->unique(['user_id', 'book_id']);
            });
        }

        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('kode_transaksi', 40)->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->date('tanggal_transaksi');
                $table->unsignedInteger('total_item')->default(1);
                $table->unsignedBigInteger('total_harga')->default(0);
                $table->string('metode_pembayaran', 50)->default('QRIS');
                $table->text('alamat_pengiriman')->nullable();
                $table->string('status', 30)->default('menunggu');
                $table->string('catatan')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('transaction_items')) {
            Schema::create('transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->unsignedInteger('jumlah')->default(1);
                $table->unsignedBigInteger('harga_satuan')->default(0);
                $table->unsignedBigInteger('subtotal')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('carts');

        if (Schema::hasColumn('books', 'harga')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('harga');
            });
        }

        Schema::dropIfExists('peminjaman');
        Schema::dropIfExists('buku');
        Schema::dropIfExists('anggota');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan tabel kategori serta atribut standar USK pada users, books, dan loans.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode_ddc', 20)->nullable()->comment('Kode klasifikasi Dewey Decimal, misal: 005');
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_anggota', 30)->nullable()->unique()->after('id');
            $table->string('kelas_jabatan', 100)->nullable()->after('role');
            $table->string('telepon', 30)->nullable()->after('kelas_jabatan');
            $table->date('suspended_until')->nullable()->after('telepon')
                ->comment('Batas akhir pembekuan akun akibat keterlambatan atau buku hilang');
            $table->string('alasan_suspend')->nullable()->after('suspended_until');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->nullOnDelete();
            $table->string('isbn', 30)->nullable()->index()->after('judul');
            $table->string('call_number', 50)->nullable()->index()->after('isbn')
                ->comment('Nomor panggil rak fisik perpustakaan');
            $table->unsignedInteger('total_koleksi')->default(0)->after('stok')
                ->comment('Total eksemplar fisik milik perpustakaan');
            $table->text('sinopsis')->nullable()->after('cover');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('kode_transaksi', 40)->nullable()->unique()->after('id');
            $table->date('tanggal_dikembalikan')->nullable()->after('tanggal_kembali')
                ->comment('Tanggal aktual buku dikembalikan ke perpustakaan');
            $table->unsignedTinyInteger('perpanjangan_ke')->default(0)->after('status')
                ->comment('Jumlah perpanjangan mandiri yang telah dilakukan (maksimal 1x)');
            $table->unsignedInteger('hari_terlambat')->default(0)->after('perpanjangan_ke');
            $table->unsignedInteger('hari_sanksi')->default(0)->after('hari_terlambat')
                ->comment('Durasi skorsing akun (hari) yang dijatuhkan dari transaksi ini');
            $table->string('catatan')->nullable()->after('hari_sanksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'kode_transaksi',
                'tanggal_dikembalikan',
                'perpanjangan_ke',
                'hari_terlambat',
                'hari_sanksi',
                'catatan',
            ]);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['isbn', 'call_number', 'total_koleksi', 'sinopsis']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_anggota',
                'kelas_jabatan',
                'telepon',
                'suspended_until',
                'alasan_suspend',
            ]);
        });

        Schema::dropIfExists('categories');
    }
};

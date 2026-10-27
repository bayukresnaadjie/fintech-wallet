<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Wallet utama per user
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nomor_wallet', 16)->unique();     // 16 digit unik
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('saldo_tertahan', 15, 2)->default(0); // pending
            $table->decimal('limit_transaksi_harian', 15, 2)->default(5000000);   // 5jt default
            $table->decimal('limit_transaksi_bulanan', 15, 2)->default(20000000); // 20jt default
            $table->boolean('is_aktif')->default(true);
            $table->boolean('is_frozen')->default(false);     // suspend by admin
            $table->timestamps();
        });

        // Ledger — setiap perubahan saldo dicatat
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('referensi', 50)->index();         // nomor transaksi
            $table->enum('tipe', ['debit','credit']);
            $table->decimal('jumlah', 15, 2);
            $table->decimal('saldo_sebelum', 15, 2);
            $table->decimal('saldo_sesudah', 15, 2);
            $table->string('keterangan');
            $table->timestamps();
        });

        // Rekening bank user
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama_bank', 50);
            $table->string('kode_bank', 10)->nullable();
            $table->string('nomor_rekening', 30);
            $table->string('nama_pemilik');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('wallets');
    }
};

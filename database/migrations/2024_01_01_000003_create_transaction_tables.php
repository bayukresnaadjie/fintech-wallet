<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Top Up
        Schema::create('top_up_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('nomor_transaksi', 30)->unique();
            $table->decimal('jumlah', 15, 2);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('jumlah_diterima', 15, 2);        // jumlah - biaya
            $table->enum('metode', ['transfer_bank','virtual_account','minimarket','qris']);
            $table->string('kode_bank', 10)->nullable();
            $table->string('nomor_va', 30)->nullable();        // virtual account number
            $table->enum('status', ['pending','success','failed','expired'])->default('pending');
            $table->text('bukti_transfer')->nullable();        // path foto bukti
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('dikonfirmasi_at')->nullable();
            $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Transfer antar user
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 30)->unique();
            $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('penerima_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_pengirim_id')->constrained('wallets');
            $table->foreignId('wallet_penerima_id')->constrained('wallets');
            $table->decimal('jumlah', 15, 2);
            $table->decimal('biaya_admin', 15, 2)->default(0);
            $table->decimal('jumlah_diterima', 15, 2);
            $table->string('catatan')->nullable();
            $table->enum('status', ['pending','success','failed','reversed'])->default('pending');
            $table->boolean('konfirmasi_pin')->default(false);
            $table->string('kategori', 50)->nullable();       // makan, transport, dll
            $table->timestamp('diproses_at')->nullable();
            $table->timestamps();
        });

        // Tarik tunai ke bank
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts');
            $table->string('nomor_transaksi', 30)->unique();
            $table->decimal('jumlah', 15, 2);
            $table->decimal('biaya_admin', 15, 2)->default(6500);
            $table->decimal('jumlah_diterima', 15, 2);
            $table->enum('status', ['pending','diproses','success','failed','cancelled'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_at')->nullable();
            $table->timestamps();
        });

        // Split Bill
        Schema::create('split_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->string('nomor_tagihan', 20)->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->decimal('total', 15, 2);
            $table->decimal('per_orang', 15, 2);
            $table->integer('jumlah_orang')->default(2);
            $table->enum('status', ['aktif','lunas','dibatalkan'])->default('aktif');
            $table->date('jatuh_tempo')->nullable();
            $table->timestamps();
        });

        Schema::create('split_bill_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('split_bill_id')->constrained('split_bills')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('jumlah_tagihan', 15, 2);
            $table->boolean('sudah_bayar')->default(false);
            $table->timestamp('dibayar_at')->nullable();
            $table->foreignId('transfer_id')->nullable()->constrained('transfers')->nullOnDelete();
            $table->timestamps();
            $table->unique(['split_bill_id','user_id']);
        });

        // QR Code payment
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kode', 100)->unique();
            $table->decimal('jumlah', 15, 2)->nullable();     // null = bebas input
            $table->string('catatan')->nullable();
            $table->boolean('is_static')->default(false);     // static = bisa dipakai berulang
            $table->integer('batas_penggunaan')->nullable();
            $table->integer('sudah_digunakan')->default(0);
            $table->timestamp('expired_at')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Notifikasi
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', ['topup','transfer','withdraw','split','promo','sistem','keamanan']);
            $table->string('icon')->nullable();
            $table->decimal('jumlah', 15, 2)->nullable();
            $table->boolean('dibaca')->default(false);
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('split_bill_members');
        Schema::dropIfExists('split_bills');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('top_up_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tambah kolom ke users
        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_hp', 20)->unique()->nullable()->after('email');
            $table->string('pin')->nullable()->after('nomor_hp');              // bcrypt PIN 6 digit
            $table->boolean('pin_aktif')->default(false)->after('pin');
            $table->boolean('is_aktif')->default(true)->after('pin_aktif');
            $table->boolean('is_suspended')->default(false)->after('is_aktif');
            $table->enum('kyc_status', ['belum','pending','verified','rejected'])->default('belum')->after('is_suspended');
            $table->string('referral_code', 10)->unique()->nullable()->after('kyc_status');
            $table->foreignId('referred_by')->nullable()->constrained('users')->nullOnDelete()->after('referral_code');
            $table->timestamp('last_login')->nullable()->after('referred_by');
            $table->string('last_login_ip', 45)->nullable()->after('last_login');
        });

        // Profil user
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nik', 16)->nullable()->unique();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L','P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('foto_profil')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->timestamps();
        });

        // KYC verifikasi
        Schema::create('user_kyc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('foto_ktp')->nullable();
            $table->string('foto_selfie')->nullable();
            $table->string('foto_selfie_ktp')->nullable();
            $table->enum('status', ['pending','verified','rejected'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->timestamps();
        });

        // Device & session tracking
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_id')->unique();
            $table->string('device_name')->nullable();
            $table->string('device_type', 50)->nullable();    // mobile, web, tablet
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_used')->nullable();
            $table->timestamps();
        });

        // OTP
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('kode', 6);
            $table->enum('tipe', ['register','login','transfer','withdraw','reset_pin','kyc']);
            $table->string('via', 20)->default('email');      // email, sms, whatsapp
            $table->boolean('digunakan')->default(false);
            $table->timestamp('expired_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('user_kyc');
        Schema::dropIfExists('user_profiles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nomor_hp','pin','pin_aktif','is_aktif','is_suspended','kyc_status','referral_code','referred_by','last_login','last_login_ip']);
        });
    }
};

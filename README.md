# 💳 PayKu — Fintech Dompet Digital

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind-CDN-06B6D4?style=flat&logo=tailwindcss&logoColor=white)

Sistem dompet digital berbasis web terinspirasi dari GoPay, OVO, dan DANA. Mendukung top up, transfer antar user, tarik tunai, split bill, QR payment, dengan sistem ledger double-entry untuk keamanan transaksi.

---

## ✨ Fitur Utama

### 🔐 Auth & Keamanan
- Register & Login dengan email
- PIN 6 digit untuk konfirmasi setiap transaksi
- Verifikasi KYC (foto KTP + selfie)
- Tracking device & IP login
- Suspend user oleh admin

### 💰 Top Up Saldo
- Pilih nominal cepat atau manual
- 4 metode: Transfer Bank, Virtual Account, Minimarket, QRIS
- Upload bukti transfer
- Konfirmasi & approval oleh admin
- Notifikasi otomatis saat sukses

### 💸 Transfer Antar User
- Cari penerima by nomor HP / email / nomor wallet (AJAX)
- Konfirmasi PIN sebelum kirim
- Pilih kategori pengeluaran (makan, transport, dll)
- Tambah catatan transaksi
- Biaya admin: gratis untuk ≥ Rp 50.000
- Double-entry ledger dengan `lockForUpdate()` anti race condition

### 🏧 Tarik Tunai
- Daftar rekening bank terverifikasi
- Konfirmasi PIN + saldo ditahan saat pengajuan
- Proses & approval admin
- Refund otomatis jika ditolak

### 📱 QR Payment
- Generate QR statis (bisa dipakai berulang)
- Generate QR dinamis dengan nominal tertentu (berlaku 24 jam)
- Scan & bayar dengan konfirmasi PIN
- Transaksi real-time tanpa biaya

### 🤝 Split Bill
- Buat tagihan bersama dengan beberapa teman
- Hitung otomatis per orang
- Notifikasi undangan ke semua anggota
- Bayar via wallet dengan PIN
- Progress tracker siapa sudah / belum bayar
- Auto-update status ke "Lunas" saat semua bayar

### 📊 Laporan Keuangan
- Laporan per bulan (pilih bulan)
- Grafik cashflow harian
- Pengeluaran per kategori
- Riwayat mutasi lengkap (ledger)
- Ringkasan pemasukan vs pengeluaran

### 🔔 Notifikasi
- Notif real-time setiap transaksi
- Badge counter di sidebar
- Kategori: top up, transfer, tarik tunai, split bill, sistem

### ⚙️ Admin Panel
- Dashboard monitoring transaksi
- Approve / tolak top up
- Proses / tolak penarikan tunai
- Verifikasi / tolak KYC user
- Suspend / aktifkan user
- Lihat saldo semua user

---

## 🔒 Keamanan Sistem

| Fitur | Implementasi |
|---|---|
| PIN Transaksi | Bcrypt hash, diverifikasi setiap transaksi |
| Race Condition | `DB::transaction()` + `lockForUpdate()` |
| Double-Entry | Setiap debit/kredit dicatat di ledger |
| Limit Transaksi | Cek limit harian & bulanan per wallet |
| Validasi Saldo | Saldo dicek sebelum setiap debit |
| Saldo Tertahan | Saldo ditahan saat pengajuan tarik tunai |

---

## 💡 Konsep Double-Entry Ledger

```
Top Up Rp 500.000 (sukses):
  CREDIT wallet user  → saldo +500.000
  Ledger: {referensi, tipe:credit, jumlah:500.000, saldo_sebelum, saldo_sesudah}

Transfer Rp 150.000 A → B:
  DEBIT  wallet A     → saldo -150.000
  CREDIT wallet B     → saldo +150.000
  2 entri ledger tercipta atomically dalam 1 DB transaction
```

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | Laravel 11 (PHP 8.3) |
| Database | MySQL 8.0 |
| Frontend | Blade + Tailwind CSS (CDN) |
| Auth | Laravel built-in Auth |
| Storage | Laravel Storage (local/public) |
| QR Code | api.qrserver.com (free API) |
| Avatar | ui-avatars.com (free API) |

---

## ⚙️ Cara Install

### Requirements
- PHP >= 8.2
- Composer
- MySQL 8.0

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/bayukresnaadjie/fintech-wallet.git
cd fintech-wallet

# 2. Install dependencies
composer install

# 3. Copy & setup environment
cp .env.example .env
php artisan key:generate

# 4. Buat database
# CREATE DATABASE fintech_wallet;

# 5. Edit .env
# DB_DATABASE=fintech_wallet
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Migrate & seed
php artisan migrate:fresh --seed

# 7. Link storage
php artisan storage:link

# 8. Jalankan
php artisan serve
```

Akses: **http://127.0.0.1:8000**

---

## 🔑 Akun Demo

| Email | Password | PIN | Role | Saldo |
|---|---|---|---|---|
| admin@fintech.test | password | 123456 | Admin | Rp 50.000.000 |
| budi@fintech.test | password | 123456 | User (KYC ✓) | Rp 2.500.000 |
| siti@fintech.test | password | 123456 | User (KYC ✓) | Rp 1.750.000 |
| dika@fintech.test | password | 123456 | User (KYC ✓) | Rp 3.200.000 |
| rina@fintech.test | password | 123456 | User (KYC pending) | Rp 500.000 |

---

## 🗄️ Struktur Database

```
users                   — User + PIN + KYC status + referral
user_profiles           — Data pribadi (NIK, alamat, dll)
user_kyc                — Dokumen KYC + status verifikasi
user_devices            — Tracking device & IP login
otp_codes               — OTP untuk verifikasi

wallets                 — Saldo + limit transaksi per user
ledger_entries          — Log setiap perubahan saldo (double-entry)
bank_accounts           — Rekening bank user

top_up_requests         — Request top up + bukti + status
transfers               — Transfer antar user + kategori
withdrawal_requests     — Request tarik tunai ke bank
split_bills             — Tagihan split bill
split_bill_members      — Anggota & status bayar per split bill
qr_codes                — QR code statis & dinamis

notifikasi              — Push notif per transaksi
```

---

## 📁 Struktur Project

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── TopUpController.php
│   ├── TransferController.php      ← AJAX cari penerima
│   ├── WithdrawalController.php
│   ├── SplitBillController.php
│   ├── QrController.php
│   ├── LaporanController.php
│   ├── NotifikasiController.php
│   └── AdminController.php
├── Models/
│   ├── User.php                    ← Auto-create wallet & profil
│   ├── Wallet.php                  ← kredit() / debit() + ledger
│   ├── LedgerEntry.php
│   ├── Transfer.php
│   ├── TopUpRequest.php
│   ├── WithdrawalRequest.php
│   ├── SplitBill.php
│   ├── SplitBillMember.php
│   ├── QrCode.php
│   └── Notifikasi.php
resources/views/
├── layouts/app.blade.php           ← Sidebar + saldo card gradient
├── auth/{login,register}.blade.php
├── dashboard.blade.php             ← Quick actions + cashflow chart
├── topup/                          ← index, create, show
├── transfer/                       ← index, create (AJAX), show
├── withdrawal/                     ← index, create
├── splitbill/                      ← index, create, show
├── qr/                             ← index, scan
├── laporan/                        ← Grafik + ledger
├── notifikasi/
└── admin/                          ← dashboard, users
```

---

## 📸 Fitur Unggulan

### Dashboard
- Saldo real-time dengan gradient card
- Quick actions 5 menu utama
- Grafik cashflow 7 hari
- Pengeluaran per kategori
- Transaksi terbaru

### Transfer
- Cari penerima secara real-time (AJAX)
- Pilih kategori dengan emoji
- PIN input dengan UI angka besar
- Ringkasan biaya sebelum konfirmasi

### Split Bill
- Dynamic tambah anggota
- Hitung per orang otomatis
- Progress card visual
- Satu klik bayar dengan PIN

### Admin Panel
- Approve top up langsung dari dashboard
- Proses withdrawal dengan satu klik
- Verifikasi KYC user

---

## 🔮 Rencana Pengembangan

- [ ] OTP via WhatsApp / SMS
- [ ] Investasi reksa dana sederhana
- [ ] Cashback & reward points
- [ ] Export laporan PDF / Excel
- [ ] Real-time notification (WebSocket)
- [ ] Mobile app (React Native)
- [ ] Integrasi payment gateway (Midtrans)
- [ ] Virtual card untuk belanja online

---

## 👨‍💻 Developer

**Bayu Kresna Adjie**
- GitHub: [@bayukresnaadjie](https://github.com/bayukresnaadjie)
- LinkedIn: [bayu-kresna-adjie](https://www.linkedin.com/in/bayu-kresna-adjie-a883301ab)
- Lokasi: Yogyakarta, Indonesia

---

## 📄 Lisensi

Project ini dibuat untuk keperluan portofolio. Silakan digunakan sebagai referensi pembelajaran.

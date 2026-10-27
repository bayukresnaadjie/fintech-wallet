<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Transfer;
use App\Models\TopUpRequest;
use App\Models\WithdrawalRequest;
use App\Models\SplitBill;
use App\Models\SplitBillMember;
use App\Models\LedgerEntry;
use App\Models\Notifikasi;
use App\Models\QrCode;

class TransaksiSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email','admin@fintech.test')->first();
        $budi  = User::where('email','budi@fintech.test')->first();
        $siti  = User::where('email','siti@fintech.test')->first();
        $dika  = User::where('email','dika@fintech.test')->first();
        $rina  = User::where('email','rina@fintech.test')->first();

        // ── TOP UP ────────────────────────────────────────────────────────────
        $topups = [
            [$budi,  1000000, 'transfer_bank', 'BCA',     'success', 14],
            [$budi,  500000,  'virtual_account','014',    'success', 10],
            [$siti,  2000000, 'transfer_bank', 'Mandiri', 'success', 12],
            [$dika,  1500000, 'virtual_account','009',    'success', 8],
            [$rina,  500000,  'transfer_bank', 'BNI',     'pending', 1],
            [$budi,  3000000, 'transfer_bank', 'BCA',     'success', 5],
            [$siti,  750000,  'minimarket',    null,      'success', 3],
        ];

        foreach ($topups as [$user, $jumlah, $metode, $bank, $status, $daysAgo]) {
            $biaya   = $metode === 'transfer_bank' ? 2500 : 0;
            $diterima = $jumlah - $biaya;
            $trx = TopUpRequest::create([
                'user_id'          => $user->id,
                'wallet_id'        => $user->wallet->id,
                'nomor_transaksi'  => 'TU' . now()->subDays($daysAgo)->format('YmdHis') . rand(100,999),
                'jumlah'           => $jumlah,
                'biaya_admin'      => $biaya,
                'jumlah_diterima'  => $diterima,
                'metode'           => $metode,
                'kode_bank'        => $bank,
                'status'           => $status,
                'dikonfirmasi_at'  => $status === 'success' ? now()->subDays($daysAgo) : null,
                'dikonfirmasi_oleh'=> $status === 'success' ? $admin->id : null,
                'created_at'       => now()->subDays($daysAgo),
                'updated_at'       => now()->subDays($daysAgo),
            ]);

            if ($status === 'success') {
                // Catat ledger
                $wallet  = $user->wallet;
                $sebelum = (float) $wallet->saldo - $diterima;
                LedgerEntry::create([
                    'wallet_id'     => $wallet->id,
                    'referensi'     => $trx->nomor_transaksi,
                    'tipe'          => 'credit',
                    'jumlah'        => $diterima,
                    'saldo_sebelum' => max(0, $sebelum),
                    'saldo_sesudah' => (float) $wallet->saldo,
                    'keterangan'    => 'Top Up via ' . $trx->metode_label,
                    'created_at'    => now()->subDays($daysAgo),
                    'updated_at'    => now()->subDays($daysAgo),
                ]);
                Notifikasi::create(['user_id'=>$user->id,'judul'=>'Top Up Berhasil','pesan'=>'Top up sebesar Rp '.number_format($diterima,0,',','.').' berhasil masuk ke dompet Anda','tipe'=>'topup','jumlah'=>$diterima,'created_at'=>now()->subDays($daysAgo)]);
            }
        }

        // ── TRANSFER ──────────────────────────────────────────────────────────
        $transfers = [
            [$budi,  $siti,  150000,  'Bayar makan siang',    'makan',     7,  'success'],
            [$siti,  $dika,  250000,  'Patungan bensin',       'transport', 6,  'success'],
            [$dika,  $budi,  500000,  'Bayar hutang',          null,        5,  'success'],
            [$budi,  $dika,  75000,   'Kopi bareng',           'makan',     4,  'success'],
            [$siti,  $budi,  1000000, 'Transfer project',      null,        3,  'success'],
            [$dika,  $siti,  200000,  'Bayar tagihan listrik',  'tagihan',  2,  'success'],
            [$budi,  $rina,  300000,  'Kiriman bulanan',       null,        1,  'success'],
            [$dika,  $budi,  50000,   'Jajan bareng',          'makan',     0,  'pending'],
        ];

        foreach ($transfers as [$pengirim, $penerima, $jumlah, $catatan, $kategori, $daysAgo, $status]) {
            $biaya    = $jumlah >= 50000 ? 0 : 1000;
            $diterima = $jumlah - $biaya;
            $trx = Transfer::create([
                'nomor_transaksi'    => 'TR' . now()->subDays($daysAgo)->format('YmdHis') . rand(100,999),
                'pengirim_id'        => $pengirim->id,
                'penerima_id'        => $penerima->id,
                'wallet_pengirim_id' => $pengirim->wallet->id,
                'wallet_penerima_id' => $penerima->wallet->id,
                'jumlah'             => $jumlah,
                'biaya_admin'        => $biaya,
                'jumlah_diterima'    => $diterima,
                'catatan'            => $catatan,
                'kategori'           => $kategori,
                'status'             => $status,
                'konfirmasi_pin'     => true,
                'diproses_at'        => $status === 'success' ? now()->subDays($daysAgo) : null,
                'created_at'         => now()->subDays($daysAgo),
                'updated_at'         => now()->subDays($daysAgo),
            ]);

            if ($status === 'success') {
                // Ledger pengirim
                LedgerEntry::create([
                    'wallet_id'     => $pengirim->wallet->id,
                    'referensi'     => $trx->nomor_transaksi,
                    'tipe'          => 'debit',
                    'jumlah'        => $jumlah,
                    'saldo_sebelum' => (float)$pengirim->wallet->saldo + $jumlah,
                    'saldo_sesudah' => (float)$pengirim->wallet->saldo,
                    'keterangan'    => 'Transfer ke ' . $penerima->name . ($catatan ? ' - '.$catatan : ''),
                    'created_at'    => now()->subDays($daysAgo),
                ]);
                // Ledger penerima
                LedgerEntry::create([
                    'wallet_id'     => $penerima->wallet->id,
                    'referensi'     => $trx->nomor_transaksi,
                    'tipe'          => 'credit',
                    'jumlah'        => $diterima,
                    'saldo_sebelum' => (float)$penerima->wallet->saldo - $diterima,
                    'saldo_sesudah' => (float)$penerima->wallet->saldo,
                    'keterangan'    => 'Transfer dari ' . $pengirim->name . ($catatan ? ' - '.$catatan : ''),
                    'created_at'    => now()->subDays($daysAgo),
                ]);
                // Notifikasi
                Notifikasi::create(['user_id'=>$pengirim->id,'judul'=>'Transfer Berhasil','pesan'=>'Transfer Rp '.number_format($jumlah,0,',','.').' ke '.$penerima->name.' berhasil','tipe'=>'transfer','jumlah'=>$jumlah,'created_at'=>now()->subDays($daysAgo)]);
                Notifikasi::create(['user_id'=>$penerima->id,'judul'=>'Uang Masuk!','pesan'=>$pengirim->name.' mengirim Rp '.number_format($diterima,0,',','.').' ke dompet Anda','tipe'=>'transfer','jumlah'=>$diterima,'created_at'=>now()->subDays($daysAgo)]);
            }
        }

        // ── WITHDRAWAL ────────────────────────────────────────────────────────
        $bankBudi = $budi->bankAccounts()->first();
        $bankSiti = $siti->bankAccounts()->first();

        WithdrawalRequest::create([
            'user_id'          => $budi->id,
            'wallet_id'        => $budi->wallet->id,
            'bank_account_id'  => $bankBudi->id,
            'nomor_transaksi'  => 'WD' . now()->subDays(9)->format('YmdHis') . '001',
            'jumlah'           => 1000000,
            'biaya_admin'      => 6500,
            'jumlah_diterima'  => 993500,
            'status'           => 'success',
            'diproses_oleh'    => $admin->id,
            'diproses_at'      => now()->subDays(9),
            'created_at'       => now()->subDays(9),
        ]);

        WithdrawalRequest::create([
            'user_id'          => $siti->id,
            'wallet_id'        => $siti->wallet->id,
            'bank_account_id'  => $bankSiti->id,
            'nomor_transaksi'  => 'WD' . now()->subDays(2)->format('YmdHis') . '002',
            'jumlah'           => 500000,
            'biaya_admin'      => 6500,
            'jumlah_diterima'  => 493500,
            'status'           => 'pending',
            'created_at'       => now()->subDays(2),
        ]);

        // ── SPLIT BILL ────────────────────────────────────────────────────────
        $split1 = SplitBill::create([
            'dibuat_oleh'  => $budi->id,
            'nomor_tagihan'=> 'SB' . now()->subDays(3)->format('YmdHis'),
            'judul'        => 'Makan malam di restoran',
            'deskripsi'    => 'Dinner bareng team di Warung Padang',
            'total'        => 450000,
            'per_orang'    => 150000,
            'jumlah_orang' => 3,
            'status'       => 'aktif',
            'jatuh_tempo'  => now()->addDays(3)->toDateString(),
            'created_at'   => now()->subDays(3),
        ]);

        SplitBillMember::create(['split_bill_id'=>$split1->id,'user_id'=>$budi->id, 'jumlah_tagihan'=>150000,'sudah_bayar'=>true,'dibayar_at'=>now()->subDays(3)]);
        SplitBillMember::create(['split_bill_id'=>$split1->id,'user_id'=>$siti->id, 'jumlah_tagihan'=>150000,'sudah_bayar'=>true,'dibayar_at'=>now()->subDays(2)]);
        SplitBillMember::create(['split_bill_id'=>$split1->id,'user_id'=>$dika->id, 'jumlah_tagihan'=>150000,'sudah_bayar'=>false]);

        $split2 = SplitBill::create([
            'dibuat_oleh'  => $dika->id,
            'nomor_tagihan'=> 'SB' . now()->subDays(1)->format('YmdHis'),
            'judul'        => 'Patungan sewa kos bulanan',
            'total'        => 2400000,
            'per_orang'    => 800000,
            'jumlah_orang' => 3,
            'status'       => 'aktif',
            'jatuh_tempo'  => now()->addDays(7)->toDateString(),
            'created_at'   => now()->subDays(1),
        ]);

        SplitBillMember::create(['split_bill_id'=>$split2->id,'user_id'=>$dika->id, 'jumlah_tagihan'=>800000,'sudah_bayar'=>true,'dibayar_at'=>now()->subDay()]);
        SplitBillMember::create(['split_bill_id'=>$split2->id,'user_id'=>$budi->id, 'jumlah_tagihan'=>800000,'sudah_bayar'=>false]);
        SplitBillMember::create(['split_bill_id'=>$split2->id,'user_id'=>$siti->id, 'jumlah_tagihan'=>800000,'sudah_bayar'=>false]);

        // ── QR CODE ───────────────────────────────────────────────────────────
        QrCode::create(['user_id'=>$budi->id,'kode'=>'QRBUDI001STATIC','jumlah'=>null,'catatan'=>'QR Statis Budi','is_static'=>true,'is_aktif'=>true]);
        QrCode::create(['user_id'=>$siti->id,'kode'=>'QRSITI001AMOUNT','jumlah'=>50000,'catatan'=>'Bayar 50rb','is_static'=>false,'expired_at'=>now()->addHours(24),'is_aktif'=>true]);

        $this->command->info('✅ Transaksi seeded: topup, transfer, withdrawal, split bill, QR');
    }
}

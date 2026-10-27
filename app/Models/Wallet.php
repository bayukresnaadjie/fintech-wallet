<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $table    = 'wallets';
    protected $fillable = [
        'user_id','nomor_wallet','saldo','saldo_tertahan',
        'limit_transaksi_harian','limit_transaksi_bulanan',
        'is_aktif','is_frozen',
    ];
    protected $casts = [
        'saldo'                    => 'decimal:2',
        'saldo_tertahan'           => 'decimal:2',
        'limit_transaksi_harian'   => 'decimal:2',
        'limit_transaksi_bulanan'  => 'decimal:2',
        'is_aktif'                 => 'boolean',
        'is_frozen'                => 'boolean',
    ];

    public function user()          { return $this->belongsTo(User::class); }
    public function ledger()        { return $this->hasMany(LedgerEntry::class)->orderByDesc('created_at'); }
    public function topUpRequests() { return $this->hasMany(TopUpRequest::class); }
    public function withdrawals()   { return $this->hasMany(WithdrawalRequest::class); }

    // ── Core: Kredit saldo (uang masuk) ───────────────────────────────────────
    public function kredit(float $jumlah, string $referensi, string $keterangan): void
    {
        DB::transaction(function () use ($jumlah, $referensi, $keterangan) {
            $wallet = Wallet::lockForUpdate()->find($this->id);
            $sebelum = (float) $wallet->saldo;
            $sesudah = $sebelum + $jumlah;

            $wallet->update(['saldo' => $sesudah]);

            LedgerEntry::create([
                'wallet_id'      => $this->id,
                'referensi'      => $referensi,
                'tipe'           => 'credit',
                'jumlah'         => $jumlah,
                'saldo_sebelum'  => $sebelum,
                'saldo_sesudah'  => $sesudah,
                'keterangan'     => $keterangan,
            ]);

            $this->saldo = $sesudah;
        });
    }

    // ── Core: Debit saldo (uang keluar) ──────────────────────────────────────
    public function debit(float $jumlah, string $referensi, string $keterangan): void
    {
        DB::transaction(function () use ($jumlah, $referensi, $keterangan) {
            $wallet = Wallet::lockForUpdate()->find($this->id);

            if ((float) $wallet->saldo < $jumlah) {
                throw new \Exception('Saldo tidak mencukupi.');
            }

            $sebelum = (float) $wallet->saldo;
            $sesudah = $sebelum - $jumlah;

            $wallet->update(['saldo' => $sesudah]);

            LedgerEntry::create([
                'wallet_id'      => $this->id,
                'referensi'      => $referensi,
                'tipe'           => 'debit',
                'jumlah'         => $jumlah,
                'saldo_sebelum'  => $sebelum,
                'saldo_sesudah'  => $sesudah,
                'keterangan'     => $keterangan,
            ]);

            $this->saldo = $sesudah;
        });
    }

    // ── Cek limit harian ─────────────────────────────────────────────────────
    public function cekLimitHarian(float $jumlah): bool
    {
        $totalHariIni = Transfer::where('pengirim_id', $this->user_id)
            ->where('status', 'success')
            ->whereDate('created_at', today())
            ->sum('jumlah');

        $totalWithdraw = WithdrawalRequest::where('user_id', $this->user_id)
            ->where('status', 'success')
            ->whereDate('created_at', today())
            ->sum('jumlah');

        return ($totalHariIni + $totalWithdraw + $jumlah) <= (float) $this->limit_transaksi_harian;
    }

    // ── Cek limit bulanan ─────────────────────────────────────────────────────
    public function cekLimitBulanan(float $jumlah): bool
    {
        $totalBulanIni = Transfer::where('pengirim_id', $this->user_id)
            ->where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');

        return ($totalBulanIni + $jumlah) <= (float) $this->limit_transaksi_bulanan;
    }

    public function getSaldoFormatAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->saldo, 0, ',', '.');
    }

    public function getSaldoTertahanFormatAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->saldo_tertahan, 0, ',', '.');
    }

    // ── Generate nomor transaksi unik ─────────────────────────────────────────
    public static function generateNomorTransaksi(string $prefix = 'TRX'): string
    {
        do {
            $nomor = $prefix . now()->format('YmdHis') . rand(100, 999);
        } while (
            Transfer::where('nomor_transaksi', $nomor)->exists() ||
            TopUpRequest::where('nomor_transaksi', $nomor)->exists() ||
            WithdrawalRequest::where('nomor_transaksi', $nomor)->exists()
        );
        return $nomor;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table    = 'transfers';
    protected $fillable = [
        'nomor_transaksi','pengirim_id','penerima_id',
        'wallet_pengirim_id','wallet_penerima_id',
        'jumlah','biaya_admin','jumlah_diterima',
        'catatan','status','konfirmasi_pin','kategori','diproses_at',
    ];
    protected $casts = [
        'jumlah'          => 'decimal:2',
        'biaya_admin'     => 'decimal:2',
        'jumlah_diterima' => 'decimal:2',
        'konfirmasi_pin'  => 'boolean',
        'diproses_at'     => 'datetime',
    ];

    public function pengirim()       { return $this->belongsTo(User::class, 'pengirim_id'); }
    public function penerima()       { return $this->belongsTo(User::class, 'penerima_id'); }
    public function walletPengirim() { return $this->belongsTo(Wallet::class, 'wallet_pengirim_id'); }
    public function walletPenerima() { return $this->belongsTo(Wallet::class, 'wallet_penerima_id'); }

    public function getJumlahFormatAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->jumlah, 0, ',', '.');
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'success'  => ['label'=>'Berhasil',  'class'=>'bg-green-100 text-green-700',  'icon'=>'✓'],
            'pending'  => ['label'=>'Proses',    'class'=>'bg-yellow-100 text-yellow-700','icon'=>'⏳'],
            'failed'   => ['label'=>'Gagal',     'class'=>'bg-red-100 text-red-700',     'icon'=>'✕'],
            'reversed' => ['label'=>'Dikembalikan','class'=>'bg-gray-100 text-gray-600', 'icon'=>'↩'],
            default    => ['label'=>ucfirst($this->status),'class'=>'bg-gray-100 text-gray-500','icon'=>'—'],
        };
    }

    public function getKategoriIconAttribute(): string
    {
        return match($this->kategori) {
            'makan'       => '🍔',
            'transport'   => '🚗',
            'belanja'     => '🛍️',
            'tagihan'     => '📋',
            'hiburan'     => '🎮',
            'kesehatan'   => '🏥',
            'pendidikan'  => '📚',
            'split_bill'  => '🤝',
            default       => '💸',
        };
    }
}

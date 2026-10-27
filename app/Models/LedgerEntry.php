<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LedgerEntry extends Model {
    protected $table = 'ledger_entries';
    protected $fillable = ['wallet_id','referensi','tipe','jumlah','saldo_sebelum','saldo_sesudah','keterangan'];
    protected $casts = ['jumlah'=>'decimal:2','saldo_sebelum'=>'decimal:2','saldo_sesudah'=>'decimal:2'];
    public function wallet() { return $this->belongsTo(Wallet::class); }
    public function getJumlahFormatAttribute(): string { return 'Rp ' . number_format((float)$this->jumlah, 0, ',', '.'); }
    public function getTipeLabelAttribute(): array {
        return $this->tipe === 'credit'
            ? ['label'=>'Masuk',  'class'=>'text-green-600', 'sign'=>'+']
            : ['label'=>'Keluar', 'class'=>'text-red-600',   'sign'=>'-'];
    }
}

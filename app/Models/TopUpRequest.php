<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TopUpRequest extends Model {
    protected $table = 'top_up_requests';
    protected $fillable = ['user_id','wallet_id','nomor_transaksi','jumlah','biaya_admin','jumlah_diterima','metode','kode_bank','nomor_va','status','bukti_transfer','expired_at','dikonfirmasi_at','dikonfirmasi_oleh'];
    protected $casts = ['jumlah'=>'decimal:2','biaya_admin'=>'decimal:2','jumlah_diterima'=>'decimal:2','expired_at'=>'datetime','dikonfirmasi_at'=>'datetime'];
    public function user()            { return $this->belongsTo(User::class); }
    public function wallet()          { return $this->belongsTo(Wallet::class); }
    public function dikonfirmasiOleh(){ return $this->belongsTo(User::class,'dikonfirmasi_oleh'); }
    public function getJumlahFormatAttribute(): string { return 'Rp '.number_format((float)$this->jumlah,0,',','.'); }
    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'success' => ['label'=>'Berhasil','class'=>'bg-green-100 text-green-700'],
            'pending' => ['label'=>'Menunggu','class'=>'bg-yellow-100 text-yellow-700'],
            'failed'  => ['label'=>'Gagal',   'class'=>'bg-red-100 text-red-700'],
            'expired' => ['label'=>'Expired', 'class'=>'bg-gray-100 text-gray-500'],
            default   => ['label'=>ucfirst($this->status),'class'=>'bg-gray-100 text-gray-500'],
        };
    }
    public function getMetodeLabelAttribute(): string {
        return match($this->metode) {
            'transfer_bank'   => '🏦 Transfer Bank',
            'virtual_account' => '💳 Virtual Account',
            'minimarket'      => '🏪 Minimarket',
            'qris'            => '📱 QRIS',
            default           => $this->metode,
        };
    }
}

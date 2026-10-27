<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WithdrawalRequest extends Model {
    protected $table = 'withdrawal_requests';
    protected $fillable = ['user_id','wallet_id','bank_account_id','nomor_transaksi','jumlah','biaya_admin','jumlah_diterima','status','catatan_admin','diproses_oleh','diproses_at'];
    protected $casts = ['jumlah'=>'decimal:2','biaya_admin'=>'decimal:2','jumlah_diterima'=>'decimal:2','diproses_at'=>'datetime'];
    public function user()         { return $this->belongsTo(User::class); }
    public function wallet()       { return $this->belongsTo(Wallet::class); }
    public function bankAccount()  { return $this->belongsTo(BankAccount::class); }
    public function diprosesOleh() { return $this->belongsTo(User::class,'diproses_oleh'); }
    public function getJumlahFormatAttribute(): string { return 'Rp '.number_format((float)$this->jumlah,0,',','.'); }
    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'success'   => ['label'=>'Berhasil', 'class'=>'bg-green-100 text-green-700'],
            'pending'   => ['label'=>'Menunggu', 'class'=>'bg-yellow-100 text-yellow-700'],
            'diproses'  => ['label'=>'Diproses', 'class'=>'bg-blue-100 text-blue-700'],
            'failed'    => ['label'=>'Gagal',    'class'=>'bg-red-100 text-red-700'],
            'cancelled' => ['label'=>'Dibatalkan','class'=>'bg-gray-100 text-gray-500'],
            default     => ['label'=>ucfirst($this->status),'class'=>'bg-gray-100 text-gray-500'],
        };
    }
}

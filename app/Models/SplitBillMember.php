<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SplitBillMember extends Model {
    protected $table = 'split_bill_members';
    protected $fillable = ['split_bill_id','user_id','jumlah_tagihan','sudah_bayar','dibayar_at','transfer_id'];
    protected $casts = ['sudah_bayar'=>'boolean','dibayar_at'=>'datetime','jumlah_tagihan'=>'decimal:2'];
    public function splitBill() { return $this->belongsTo(SplitBill::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function transfer()  { return $this->belongsTo(Transfer::class); }
    public function getJumlahFormatAttribute(): string { return 'Rp '.number_format((float)$this->jumlah_tagihan,0,',','.'); }
}

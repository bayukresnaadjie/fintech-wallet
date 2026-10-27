<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BankAccount extends Model {
    protected $table = 'bank_accounts';
    protected $fillable = ['user_id','nama_bank','kode_bank','nomor_rekening','nama_pemilik','is_verified','is_default'];
    protected $casts = ['is_verified'=>'boolean','is_default'=>'boolean'];
    public function user() { return $this->belongsTo(User::class); }
    public function withdrawals() { return $this->hasMany(WithdrawalRequest::class); }
}

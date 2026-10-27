<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserKyc extends Model {
    protected $table = 'user_kyc';
    protected $fillable = ['user_id','foto_ktp','foto_selfie','foto_selfie_ktp','status','catatan_admin','diverifikasi_oleh','diverifikasi_at'];
    protected $casts = ['diverifikasi_at'=>'datetime'];
    public function user()           { return $this->belongsTo(User::class); }
    public function diverifikasiOleh(){ return $this->belongsTo(User::class,'diverifikasi_oleh'); }
    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'verified' => ['label'=>'Terverifikasi','class'=>'bg-green-100 text-green-700'],
            'pending'  => ['label'=>'Menunggu',     'class'=>'bg-yellow-100 text-yellow-700'],
            'rejected' => ['label'=>'Ditolak',      'class'=>'bg-red-100 text-red-700'],
            default    => ['label'=>ucfirst($this->status),'class'=>'bg-gray-100 text-gray-500'],
        };
    }
}

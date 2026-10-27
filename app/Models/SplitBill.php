<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SplitBill extends Model {
    protected $table = 'split_bills';
    protected $fillable = ['dibuat_oleh','nomor_tagihan','judul','deskripsi','total','per_orang','jumlah_orang','status','jatuh_tempo'];
    protected $casts = ['total'=>'decimal:2','per_orang'=>'decimal:2','jatuh_tempo'=>'date'];
    public function pembuatTagihan() { return $this->belongsTo(User::class,'dibuat_oleh'); }
    public function members()        { return $this->hasMany(SplitBillMember::class); }
    public function getTotalFormatAttribute(): string { return 'Rp '.number_format((float)$this->total,0,',','.'); }
    public function getPerOrangFormatAttribute(): string { return 'Rp '.number_format((float)$this->per_orang,0,',','.'); }
    public function getStatusBadgeAttribute(): array {
        return match($this->status) {
            'lunas'      => ['label'=>'Lunas',      'class'=>'bg-green-100 text-green-700'],
            'aktif'      => ['label'=>'Aktif',      'class'=>'bg-blue-100 text-blue-700'],
            'dibatalkan' => ['label'=>'Dibatalkan', 'class'=>'bg-red-100 text-red-700'],
            default      => ['label'=>ucfirst($this->status),'class'=>'bg-gray-100 text-gray-500'],
        };
    }
    public function getProgressAttribute(): int {
        $total  = $this->members()->count();
        if ($total === 0) return 0;
        $bayar  = $this->members()->where('sudah_bayar',true)->count();
        return round(($bayar/$total)*100);
    }
}

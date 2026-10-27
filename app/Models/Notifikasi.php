<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notifikasi extends Model {
    protected $table = 'notifikasi';
    protected $fillable = ['user_id','judul','pesan','tipe','icon','jumlah','dibaca','dibaca_at'];
    protected $casts = ['jumlah'=>'decimal:2','dibaca'=>'boolean','dibaca_at'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function getTipeIconAttribute(): string {
        return match($this->tipe) {
            'topup'    => '💰', 'transfer' => '💸',
            'withdraw' => '🏧', 'split'    => '🤝',
            'promo'    => '🎁', 'keamanan' => '🔒',
            default    => 'ℹ️',
        };
    }
    public static function kirim(int $userId, string $judul, string $pesan, string $tipe = 'sistem', ?float $jumlah = null): void {
        static::create(['user_id'=>$userId,'judul'=>$judul,'pesan'=>$pesan,'tipe'=>$tipe,'jumlah'=>$jumlah]);
    }
}

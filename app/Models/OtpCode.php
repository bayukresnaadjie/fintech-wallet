<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OtpCode extends Model {
    protected $table = 'otp_codes';
    protected $fillable = ['user_id','kode','tipe','via','digunakan','expired_at'];
    protected $casts = ['digunakan'=>'boolean','expired_at'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function isValid(): bool { return !$this->digunakan && $this->expired_at->isFuture(); }
    public static function generate(int $userId, string $tipe, string $via = 'email'): self {
        // Hapus OTP lama
        static::where('user_id',$userId)->where('tipe',$tipe)->delete();
        return static::create([
            'user_id'    => $userId,
            'kode'       => str_pad(rand(0,999999), 6, '0', STR_PAD_LEFT),
            'tipe'       => $tipe,
            'via'        => $via,
            'expired_at' => now()->addMinutes(5),
        ]);
    }
}

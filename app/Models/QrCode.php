<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class QrCode extends Model {
    protected $table = 'qr_codes';
    protected $fillable = ['user_id','kode','jumlah','catatan','is_static','batas_penggunaan','sudah_digunakan','expired_at','is_aktif'];
    protected $casts = ['jumlah'=>'decimal:2','is_static'=>'boolean','is_aktif'=>'boolean','expired_at'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
    public function isValid(): bool {
        if (!$this->is_aktif) return false;
        if ($this->expired_at && $this->expired_at->isPast()) return false;
        if ($this->batas_penggunaan && $this->sudah_digunakan >= $this->batas_penggunaan) return false;
        return true;
    }
    public static function generate(int $userId, ?float $jumlah = null, ?string $catatan = null, bool $isStatic = false): self {
        return static::create([
            'user_id'    => $userId,
            'kode'       => 'QR' . strtoupper(Str::random(20)),
            'jumlah'     => $jumlah,
            'catatan'    => $catatan,
            'is_static'  => $isStatic,
            'expired_at' => $isStatic ? null : now()->addHours(24),
        ]);
    }
    public function getQrUrlAttribute(): string {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode(route('qr.scan', $this->kode));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','email','nomor_hp','password','pin','pin_aktif',
        'is_aktif','is_suspended','kyc_status',
        'referral_code','referred_by','last_login','last_login_ip',
    ];

    protected $hidden = ['password','pin','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login'        => 'datetime',
        'pin_aktif'         => 'boolean',
        'is_aktif'          => 'boolean',
        'is_suspended'      => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->referral_code)) {
                $model->referral_code = strtoupper(Str::random(8));
            }
        });

        // Buat wallet otomatis saat user dibuat
        static::created(function ($model) {
            Wallet::create([
                'user_id'       => $model->id,
                'nomor_wallet'  => $model->generateNomorWallet(),
            ]);
            UserProfile::create(['user_id' => $model->id]);
        });
    }

    public function profile()       { return $this->hasOne(UserProfile::class); }
    public function kyc()           { return $this->hasOne(UserKyc::class); }
    public function wallet()        { return $this->hasOne(Wallet::class); }
    public function devices()       { return $this->hasMany(UserDevice::class); }
    public function otpCodes()      { return $this->hasMany(OtpCode::class); }
    public function bankAccounts()  { return $this->hasMany(BankAccount::class); }
    public function notifikasi()    { return $this->hasMany(Notifikasi::class); }
    public function referredBy()    { return $this->belongsTo(User::class, 'referred_by'); }
    public function referrals()     { return $this->hasMany(User::class, 'referred_by'); }

    // Transfer
    public function transferMasuk()  { return $this->hasMany(Transfer::class, 'penerima_id'); }
    public function transferKeluar() { return $this->hasMany(Transfer::class, 'pengirim_id'); }

    public function generateNomorWallet(): string
    {
        do {
            $nomor = '08' . str_pad(rand(0, 99999999999999), 14, '0', STR_PAD_LEFT);
        } while (Wallet::where('nomor_wallet', $nomor)->exists());
        return $nomor;
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->profile?->foto_profil
            ? asset('storage/' . $this->profile->foto_profil)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366F1&color=fff&size=64';
    }

    public function getKycBadgeAttribute(): array
    {
        return match($this->kyc_status) {
            'verified' => ['label'=>'Verified',  'class'=>'bg-green-100 text-green-700',  'icon'=>'✓'],
            'pending'  => ['label'=>'Pending',   'class'=>'bg-yellow-100 text-yellow-700','icon'=>'⏳'],
            'rejected' => ['label'=>'Rejected',  'class'=>'bg-red-100 text-red-700',     'icon'=>'✕'],
            default    => ['label'=>'Belum KYC', 'class'=>'bg-gray-100 text-gray-500',   'icon'=>'—'],
        };
    }

    public function verifyPin(string $pin): bool
    {
        return \Illuminate\Support\Facades\Hash::check($pin, $this->pin);
    }

    public function notifBelumDibaca(): int
    {
        return $this->notifikasi()->where('dibaca', false)->count();
    }

    public function isAdmin(): bool
    {
        return in_array($this->email, config('fintech.admin_emails', ['admin@fintech.test']));
    }
}

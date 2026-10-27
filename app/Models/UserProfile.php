<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserProfile extends Model {
    protected $table = 'user_profiles';
    protected $fillable = ['user_id','nik','tanggal_lahir','jenis_kelamin','alamat','kota','provinsi','kode_pos','foto_profil','pekerjaan'];
    protected $casts = ['tanggal_lahir'=>'date'];
    public function user() { return $this->belongsTo(User::class); }
}

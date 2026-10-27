<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserDevice extends Model {
    protected $table = 'user_devices';
    protected $fillable = ['user_id','device_id','device_name','device_type','os','browser','ip_address','is_trusted','last_used'];
    protected $casts = ['is_trusted'=>'boolean','last_used'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}

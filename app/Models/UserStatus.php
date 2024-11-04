<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_status';

    // Kolom yang dapat diisi
    protected $fillable = [
        'user_id',
        'fcm_id',
        'browser_name',
        'last_login_at',
        'last_scheduled_checkin',
        'is_online',
    ];

    // Relasi dengan model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeChecking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'division_id',
        'scheduled_time',
        'checkin_start_time',
        'is_active',
        'is_completed',
        'photo_path',
        'score',
        'location_latitude',
        'location_longitude'
    ];

    /**
     * Relasi ke model User
     * Satu EmployeeChecking hanya terkait dengan satu User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke model Division
     * Satu EmployeeChecking hanya terkait dengan satu Division
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Scope untuk memfilter jadwal yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk memfilter jadwal yang belum selesai
     */
    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }
}

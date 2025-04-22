<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayoffQuota extends Model
{
    protected $fillable = [
        'user_id', 'year', 'dayoff_type_id', 'quota', 'used',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function type()
    {
        return $this->belongsTo(DayoffType::class, 'dayoff_type_id');
    }
}

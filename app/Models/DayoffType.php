<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayoffType extends Model
{
    protected $fillable = [
        'name', 'code', 'is_limited', 'default_quota'
    ];

    public function quotas()
    {
        return $this->hasMany(DayoffQuota::class);
    }
}

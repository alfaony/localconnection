<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class DayoffType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'is_limited', 'default_quota','permission_required'
    ];

    public function quotas()
    {
        return $this->hasMany(DayoffQuota::class);
    }
}

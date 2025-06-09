<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DivisionQuotaLock extends Model
{
    protected $fillable = [
        'division_id', 'month', 'year', 'locked_quota'
    ];

    public function division()
    {
        return $this->belongsTo(Division::class)->withTrashed();
    }
}


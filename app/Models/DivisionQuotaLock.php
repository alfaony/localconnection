<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DivisionQuotaLock extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'division_id', 'month', 'year', 'locked_quota'
    ];

    public function division()
    {
        return $this->belongsTo(Division::class)->withTrashed();
    }
}


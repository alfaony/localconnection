<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataCenterEntry extends Model
{
    protected $fillable = ['name', 'capacity_mb'];

    public function dataCenter(): BelongsTo
    {
        return $this->belongsTo(DataCenter::class);
    }
}
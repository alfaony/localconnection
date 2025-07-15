<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopEntry extends Model
{
    protected $fillable = ['name', 'capacity_mb', 'pop_id'];

    public function Pop(): BelongsTo
    {
        return $this->belongsTo(Pop::class)->withTrashed();
    }
}

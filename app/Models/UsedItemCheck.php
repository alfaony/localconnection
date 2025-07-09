<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsedItemCheck extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'used_item_id',
        'master_check_item_id',
        'status',
        'notes',
    ];

    public function itemUsed()
    {
        return $this->belongsTo(UsedItem::class)->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo(MasterCheckItem::class, 'master_check_item_id')->withTrashed();
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsedItemRepair extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'used_item_id',
        'repair_item',
        'cost',
    ];

    public function item()
    {
        return $this->belongsTo(UsedItem::class)->withTrashed();
    }
}


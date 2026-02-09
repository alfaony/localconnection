<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsedLaptopCheck extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'used_laptop_id',
        'master_check_item_id',
        'status',
        'notes',
    ];

    public function laptop()
    {
        return $this->belongsTo(UsedLaptop::class)->withTrashed();
    }

    public function item()
    {
        return $this->belongsTo(MasterCheckItem::class, 'master_check_item_id');
    }
}

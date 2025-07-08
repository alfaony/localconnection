<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedLaptopRepair extends Model
{
protected $fillable = [
    'used_laptop_id',
    'repair_item',
    'cost',
];

public function laptop()
{
    return $this->belongsTo(UsedLaptop::class);
}
}

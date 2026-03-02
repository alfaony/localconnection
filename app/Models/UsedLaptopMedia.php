<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsedLaptopMedia extends Model
{
    protected $fillable = ['order','used_laptop_id', 'file_path', 'caption'];

    public function laptop()
    {
        return $this->belongsTo(UsedLaptop::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}

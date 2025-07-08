<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsedLaptopMedia extends Model
{
    protected $fillable = ['used_laptop_id', 'file_path', 'caption'];

    public function laptop()
    {
        return $this->belongsTo(UsedLaptop::class);
    }
}

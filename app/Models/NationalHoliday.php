<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NationalHoliday extends Model
{
    use HasFactory, SoftDeletes;

    // Field yang dapat diisi secara massal
    protected $fillable = [
        'name',
        'date'
    ];

    // Optional: Define additional model settings or methods here as needed
}

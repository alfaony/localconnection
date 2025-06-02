<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorResponse extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'phone',
        'message',
        'is_out_of_flow',
        'item_request_id',
    ];
    
}

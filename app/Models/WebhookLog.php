<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'signature',
        'headers',
        'payload',
        'is_valid',
        'status',
    ];
}

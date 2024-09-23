<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'endpoint',
        'method',
        'request_payload',
        'response_payload',
        'status_code',
        'error_message',
        'executed_at',
    ];
}

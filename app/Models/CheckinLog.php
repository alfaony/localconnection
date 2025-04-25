<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_checkin_id',
        'excecuted_in_at',
        'excecuted_out_at',
        'response_fcm',
        'response_firebase',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OltOnuMetric extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'olt_onu_id',
        'status',
        'rx_power',
        'tx_power',
        'distance_m',
        'recorded_at',
    ];

    protected $casts = [
        'rx_power' => 'float',
        'tx_power' => 'float',
        'distance_m' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function onu()
    {
        return $this->belongsTo(OltOnu::class, 'olt_onu_id');
    }
}

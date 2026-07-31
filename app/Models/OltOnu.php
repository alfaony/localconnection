<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OltOnu extends Model
{
    use HasFactory;

    public const STATUS_ONLINE = 'ONLINE';
    public const STATUS_OFFLINE = 'OFFLINE';
    public const STATUS_LOS = 'LOS';
    public const STATUS_UNKNOWN = 'UNKNOWN';

    protected $fillable = [
        'olt_id',
        'internet_customer_id',
        'pon_port',
        'onu_index',
        'serial_number',
        'name',
        'status',
        'rx_power',
        'tx_power',
        'distance_m',
        'last_down_reason',
        'last_seen_at',
        'last_polled_at',
        'raw_data',
    ];

    protected $casts = [
        'rx_power' => 'float',
        'tx_power' => 'float',
        'distance_m' => 'integer',
        'last_seen_at' => 'datetime',
        'last_polled_at' => 'datetime',
        'raw_data' => 'array',
    ];

    public function olt()
    {
        return $this->belongsTo(Olt::class);
    }

    public function customer()
    {
        return $this->belongsTo(InternetCustomer::class, 'internet_customer_id')->withTrashed();
    }

    public function metrics()
    {
        return $this->hasMany(OltOnuMetric::class);
    }
}

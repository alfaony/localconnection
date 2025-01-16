<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SensorZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sensor_zone'; // Nama tabel pivot

    protected $fillable = [
        'zone_id',
        'sensor_id',
        'sensor_code',
        'value',
    ];

    protected $dates = ['deleted_at']; // SoftDeletes

    /**
     * Relasi ke model Zone
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    /**
     * Relasi ke model Sensor
     */
    public function sensor()
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }
}
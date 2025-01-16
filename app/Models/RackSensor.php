<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RackSensor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rack_sensor';
    protected $fillable = ['rack_id', 'sensor_id', 'sensor_code', 'value'];

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function sensor()
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }
}

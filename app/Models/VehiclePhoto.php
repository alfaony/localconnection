<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiclePhoto extends Model
{
    protected $fillable = [
        'vehicle_id',
        'uploaded_by',
        'photo',
        'description',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }
}

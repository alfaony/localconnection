<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class ServiceType extends Model
{
    use SoftDeletes;

    protected $table = 'service_types';

    protected $fillable = 
    [
        'id', 'name', 'description', 'created_at', 'updated_at'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'provider_service_type')
                    ->withPivot('factor_volumetric')
                    ->withTimestamps();
    }

    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class, 'service_type_id');
    }
}
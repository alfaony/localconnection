<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class ShippingRate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'service_type_id',
        'origin_id',
        'destination_id',
        'base_weight',
        'base_price',
        'additional_weight',
        'additional_price',
        'rate_per_cbm',
        'delivery_time',
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

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function origin()
    {
        return $this->belongsTo(PostalCode::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(PostalCode::class, 'destination_id');
    }

    public function getFactorVolumetricAttribute()
    {
        $pivot = $this->serviceType->providers()
            ->where('provider_id', $this->provider_id)
            ->first()
            ->pivot ?? null;
            
        return $pivot && $pivot->factor_volumetric !== null && $pivot->factor_volumetric > 0
            ? $pivot->factor_volumetric
            : 4000;
    }
}
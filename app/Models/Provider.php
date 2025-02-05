<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Provider extends Model
{
    use SoftDeletes;

    protected $table = 'providers';

    protected $fillable = [
        'id', 'name', 'contact_info','description','email','created_at', 'updated_at'
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

    public function serviceTypes()
    {
        return $this->belongsToMany(ServiceType::class, 'provider_service_type')
        ->withPivot('factor_volumetric')
        ->withTimestamps();
    }

    public function shippingRates()
    {
        return $this->hasMany(ShippingRate::class, 'provider_id');
    }
}
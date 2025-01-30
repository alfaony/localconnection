<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class PostalCode extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'subdistrict_id', 'postal_code', 'created_at', 'updated_at'];

    public function shippingRatesAsOrigin()
    {
        return $this->hasMany(ShippingRate::class, 'origin_id');
    }
    
    public function shippingRatesAsDestination()
    {
        return $this->hasMany(ShippingRate::class, 'destination_id');
    }
    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class, 'subdistrict_id')->withTrashed();
    }

    public function getFullNameAttribute()
    {
        return "{$this->postal_code} - {$this->subdistrict->name} - {$this->subdistrict->district->name} - {$this->subdistrict->district->city->name} - {$this->subdistrict->district->city->province->name}";
    }
    public function getComplateNameAttribute()
    {
        $province = $this->subdistrict->district->city->province;
        $city = $this->subdistrict->district->city;
        $district = $this->subdistrict->district;
        $subDistrict = $this->subdistrict;
        $postalCode = $this;
        return "{$province->name}, {$city->name}, {$district->name}, {$subDistrict->name}, {$postalCode->postal_code}";
    }
}
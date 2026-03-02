<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class District extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'city_id', 'default_subdistrict_id','name', 'created_at', 'updated_at'];

    public function getFullNameAttribute()
    {
        return "{$this->name} - {$this->city->name} - {$this->city->province->name}";
    }
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->withTrashed();
    }

    public function subdistricts()
    {
        return $this->hasMany(Subdistrict::class, 'district_id');
    }

    public function defaultSubdistrict()
    {
        return $this->belongsTo(Subdistrict::class, 'default_subdistrict_id');
    }

    public function asDefaultCity()
    {
        return $this->hasOne(City::class, 'default_district_id', 'id');
    }

    public function districtCoverages()
    {
        return $this->hasMany(CoverageService::class, 'district_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Subdistrict extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'district_id', 'name', 'created_at', 'updated_at'];

    public function getFullNameAttribute()
    {
        return "{$this->name} - {$this->district->name} - {$this->district->city->name} - {$this->district->city->province->name}";
    }
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id')->withTrashed();
    }

    public function postalCodes()
    {
        return $this->hasMany(PostalCode::class);
    }

    public function defaultPostalCode()
    {
        return $this->hasOne(PostalCode::class,'subdistrict_id', 'id');
    }

    public function coverageService()
    {
        return $this->hasOne(CoverageService::class, 'subdistrict_id');
    }

    public function asDefaultDistrict()
    {
        return $this->hasOne(District::class, 'default_subdistrict_id', 'id');
    }

    public function subdistrictCoverages()
    {
        return $this->hasMany(CoverageService::class, 'subdistrict_id');
    }
}
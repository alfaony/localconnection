<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'province_id', 'name','default_district_id', 'created_at', 'updated_at'];

    public function getFullNameAttribute()
    {
        return "{$this->name} - {$this->province->name}";
    }
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id')->withTrashed();
    }

    public function districts()
    {
        return $this->hasMany(District::class, 'city_id');
    }

    public function defaultDistrict()
    {
        return $this->belongsTo(District::class, 'default_district_id');
    }
}
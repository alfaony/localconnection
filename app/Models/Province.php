<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Province extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'country_id', 'name', 'default_city_id', 'created_at', 'updated_at'];

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id')->withTrashed();
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'province_id');
    }

    public function defaultCity()
    {
        return $this->belongsTo(City::class, 'default_city_id');
    }

    public function provinceCoverages()
    {
        return $this->hasMany(CoverageService::class, 'province_id');
    }
}
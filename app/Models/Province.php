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

    public function getInitialAttribute()
    {
        $provinceName = $this->name;

        if (!$provinceName) return 'XXX';

        $words = explode(' ', trim($provinceName));

        // Jika ada 3 kata → ambil satu huruf tiap kata (NTT, NTT, DIY)
        if (count($words) >= 3) {
            return strtoupper(
                $words[0][0] . $words[1][0] . $words[2][0]
            );
        }

        // Jika ada 2 kata → ambil huruf pertama tiap kata (JB, SU)
        if (count($words) == 2) {
            return strtoupper(
                $words[0][0] . $words[1][0]
            );
        }

        // Jika 1 kata → ambil 2 huruf pertama (BA, RI)
        return strtoupper(substr($provinceName, 0, 2));
    }
}
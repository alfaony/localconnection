<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Country extends Model
{
    use SoftDeletes;

    protected $table = 'countries';

    protected $fillable = ['id', 'name', 'iso_code', 'created_at', 'updated_at'];

    public function provinces()
    {
        return $this->hasMany(Province::class, 'country_id');
    }
}
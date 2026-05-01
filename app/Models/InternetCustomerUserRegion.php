<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class InternetCustomerUserRegion extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'user_id',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }

    public function getLabelAttribute(): string
    {
        if ($this->subdistrict_id && $this->subdistrict) {
            return $this->subdistrict->name . ' - ' . ($this->district->name ?? '') . ' - ' . ($this->city->name ?? '') . ' - ' . ($this->province->name ?? '');
        }
        if ($this->district_id && $this->district) {
            return $this->district->name . ' - ' . ($this->city->name ?? '') . ' - ' . ($this->province->name ?? '');
        }
        if ($this->city_id && $this->city) {
            return $this->city->name . ' - ' . ($this->province->name ?? '');
        }
        if ($this->province_id && $this->province) {
            return $this->province->name;
        }
        return 'All Region';
    }
}

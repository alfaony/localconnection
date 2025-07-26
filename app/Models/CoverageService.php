<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class CoverageService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'province_id',
        'subdistrict_id',
        'district_id',
        'city_id'
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class)->withTrashed();
    }
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class)->withTrashed();
    }
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class)->withTrashed();
    }

    public function subdistrict(): BelongsTo
    {
        return $this->belongsTo(Subdistrict::class)->withTrashed();
    }


    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function coverageServiceOds(): HasMany
    {
        return $this->hasMany(CoverageServiceDistribution::class);
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = Auth::user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}
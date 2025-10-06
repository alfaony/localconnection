<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class OpticalDistribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_assign_id',
        'name',
        'capacity_mb',
        'address',
        'latitude',
        'longitude',
        'location_photo',
    ];

    protected $casts = [
        'company_id' => 'string',
        'user_assign_id' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'user_assign_id');
    }

    public function pops()
    {
        return $this->belongsToMany(Pop::class);
    }

    public function coverageServicesDistribution()
    {
        return $this->hasOne(CoverageServiceDistribution::class);
    }

    public function scopeByCompany($query, $company_id)
    {
        $company_ids = Auth::user()->accessibleCompanies->pluck('id')->push($company_id)->unique();
        return $query->whereIn('company_id', $company_ids);
    }
}
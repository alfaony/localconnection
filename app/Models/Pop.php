<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Pop extends Model
{
    protected $fillable = [
        'name',
        'capacity_mb',
        'is_multi_data_center',
        'monthly_cost',
        'lease_expiration_date',
        'address',
        'coordinates',
        'longitude',
        'latitude',
        'company_id',
        'user_created_id'
    ];

    public function dataCenters()
    {
        return $this->belongsToMany(DataCenter::class, 'pop_data_center', 'pop_id', 'data_center_id');
    }
    public function entries()
    {
        return $this->hasMany(PopEntry::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function userCreated()
    {
        return $this->belongsTo(User::class, 'user_created_id')->withTrashed();
    }
    

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = Auth::user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}


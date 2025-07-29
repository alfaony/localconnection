<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'value',
        'register_date',
        'start_date',
        'end_date',
        'quota',
        'is_active',
    ];

    protected $casts = [
        'register_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // RELATION: Ke company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    public function scopeByCompany($query,$companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        if($companyIds && Auth::user()->role->name != RoleSchema::ROOT)
        {
            return $query->whereIn("company_id",$companyIds);
        }
    }
}

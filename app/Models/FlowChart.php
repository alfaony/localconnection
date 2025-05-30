<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

class FlowChart extends Model
{
    protected $fillable = ['name', 'description', 'created_by','company_id','user_id', 'json_model'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

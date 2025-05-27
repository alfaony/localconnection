<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        if($companyId)
        {
            return $query->where('company_id', $companyId);
        }
    }
}

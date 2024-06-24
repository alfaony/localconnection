<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;

class Asset extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public function assetAssign()
    {
        return $this->hasMany(AssetAssign::class);
    }

    public function latestAssetAssign()
    {
        return $this->hasOne(AssetAssign::class)->latest('created_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    
    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }
}

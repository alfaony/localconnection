<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCheckItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','company_id','type','item_category_id'];

    public function checks()
    {
        return $this->hasMany(UsedLaptopCheck::class, 'master_check_item_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id')->withTrashed();
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterCheckItem extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','company_id'];

    public function checks()
    {
        return $this->hasMany(UsedLaptopCheck::class, 'master_check_item_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

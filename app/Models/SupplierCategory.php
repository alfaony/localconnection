<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCategory extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'name', 'company_id'
    ];

    public function productSuppliers()
    {
        return $this->belongsToMany(ProductSupplier::class, 'supplier_category_product_supplier');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id')->withTrashed();
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {   
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            $query->whereIn('company_id', $companyIds);
        }
    }
}

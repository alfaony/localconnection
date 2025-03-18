<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_name', 'store_name', 'phone_number', 'location', 
        'sales_information', 'additional_information', 
        'store_photo', 'ktp_photo',
        'company_id',
    ];

    public function supplierCategories()
    {
        return $this->belongsToMany(SupplierCategory::class, 'supplier_category_product_supplier');
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            $query->where('company_id', $companyId);
        }
    }
}
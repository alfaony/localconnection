<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
    ];

    /**
     * Relasi: SupplierType milik Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * (Optional) Jika SupplierType memiliki relasi ke suppliers
     */
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * Scope: Hanya SupplierType yang berelasi dengan $companyId
     */
    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}
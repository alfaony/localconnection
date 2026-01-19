<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerParameterType extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name', 'code', 'unit', 'description', 'is_active', 'sort_order', 'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function targetValues(): HasMany
    {
        return $this->hasMany(PartnerTargetValue::class, 'parameter_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopebyCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
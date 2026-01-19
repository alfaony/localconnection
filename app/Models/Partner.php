<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id', 'pic_user_id', 'name', 'partner_type',
        'industry', 'website', 'status', 'is_certified',
        'certification_level', 'certified_at', 'partnership_started_at','certification_file'
    ];

    protected $casts = [
        'is_certified' => 'boolean',
        'certified_at' => 'date',
        'partnership_started_at' => 'date',
    ];

    public function targets(): HasMany
    {
        return $this->hasMany(PartnerTarget::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getPartnerTypeNameAttribute(): string
    {
        return config('partners.partner_types')[$this->partner_type] ?? $this->partner_type;
    }
}

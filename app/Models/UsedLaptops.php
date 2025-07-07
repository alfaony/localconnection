<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedLaptop extends Model
{
    protected $fillable = [
        'name',
        'processor',
        'ram',
        'ssd',
        'gpu',
        'operating_system',
        'purchase_price',
        'notes',
        'is_sold',
        'sold_price',
        'sold_at',
    ];

    protected $casts = [
        'is_sold' => 'boolean',
        'sold_at' => 'date',
    ];

    // ✅ Relasi
    public function checks()
    {
        return $this->hasMany(UsedLaptopCheck::class);
    }

    public function repairs()
    {
        return $this->hasMany(UsedLaptopRepair::class);
    }

    // 💰 Harga jual disarankan = (harga beli + total perbaikan) + 30%
    public function getSuggestedSellingPriceAttribute()
    {
        $repairCost = $this->repairs->sum('cost');
        $base = $this->purchase_price + $repairCost;
        return round($base * 1.3);
    }

    // 🟢 Status Jual (label helper opsional)
    public function getSaleStatusAttribute()
    {
        return $this->is_sold
            ? 'Terjual (Rp ' . number_format($this->sold_price) . ')'
            : 'Belum Terjual';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}

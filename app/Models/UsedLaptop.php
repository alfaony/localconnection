<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class UsedLaptop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'serial_number',
        'weight',
        'name',
        'brand',
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
        'qr_code_path',
    ];

    protected $casts = [
        'sold_at' => 'date',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (empty($this->slug)) 
        {
            $slug = $value."-".$this->serial_number;
            $this->attributes['slug'] = $this->createUniqueSlug($slug);
        }
    }
    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'LIKE', "$slug%")->withTrashed()->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    // ✅ Relasi
    public function checks()
    {
        return $this->hasMany(UsedLaptopCheck::class);
    }

    public function repairs()
    {
        return $this->hasMany(UsedLaptopRepair::class);
    }

    public function media()
    {
        return $this->hasMany(UsedLaptopMedia::class);
    }

    
    // ❓ Apakah laptop perlu aksi?
    public function isAction()
    {
        return $this->is_sold ? false : true;
    }

    // 💰 Harga jual disarankan = (harga beli + total perbaikan) + 30%
    public function getSuggestedSellingPriceAttribute()
    {
        $repairCost = $this->repairs->sum('cost');
        $base = $this->purchase_price + $repairCost;
        return round($base * 1.3);
    }

    public function getJakartaPriceAttribute()
    {

        return $this->suggested_selling_price + config('services.used_laptop_charge.totebag_charge') + config('services.used_laptop_charge.totebag_cover_charge');
    }

    public function getJambiPriceAttribute()
    {

        return $this->suggested_selling_price + $this->jakarta_price + config('services.used_laptop_charge.totebag_charge') + config('services.used_laptop_charge.expedition_charge');
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
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}

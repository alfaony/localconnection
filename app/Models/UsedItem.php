<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\AwardsXp;

class UsedItem extends Model
{
    use SoftDeletes, AwardsXp;

    protected $fillable = [
        'serial_number',
        'name',
        'purchase_price',
        'notes',
        'is_sold',
        'sold_price',
        'sold_at',
        'qr_code_path',
        'company_id',
        'user_id',
        'rack_id',
    ];

    protected $casts = [
        'is_sold' => 'boolean',
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
    public function rack()
    {
        return $this->belongsTo(Rack::class)->withTrashed();
    }
    public function checks()
    {
        return $this->hasMany(UsedItemCheck::class);
    }

    public function repairs()
    {
        return $this->hasMany(UsedItemRepair::class);
    }

    public function media()
    {
        return $this->hasMany(UsedItemMedia::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ItemCategory::class);
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


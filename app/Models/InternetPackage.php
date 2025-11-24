<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class InternetPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'bandwidth',
        'type',
        'price',
        'price_nett',
        'description',
        'is_active',
        'company_id',
        'access_type',
        'rate_down_mbps',
        'rate_up_mbps',
        'fup_rate_down_mbps',
        'fup_rate_up_mbps',
        'quota_bytes',
        'version',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function promos()
    {
        return $this->belongsToMany(Promo::class);
    }

    public function getPromoActiveAttribute()
    {
        return $this->promos()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere(function ($q) {
                        $today = Carbon::today();
                        $q->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = Auth::user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}

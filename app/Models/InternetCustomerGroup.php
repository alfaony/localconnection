<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

class InternetCustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'last_number',
    ];

    protected $casts = [
        'last_number' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customers()
    {
        return $this->hasMany(InternetCustomer::class, 'group_id');
    }

    public function odps()
    {
        return $this->belongsToMany(
            OpticalDistribution::class,
            'internet_customer_group_odp',
            'group_id',
            'optical_distribution_id'
        );
    }

    /**
     * Format sequence number as grouping_id suffix.
     * 1–9999   → "0001"–"9999"
     * 10000+   → "010000", "010001", …
     */
    public static function formatSequence(int $n): string
    {
        return $n <= 9999
            ? str_pad($n, 4, '0', STR_PAD_LEFT)
            : '0' . $n;
    }

    /**
     * Parse the sequence number back from a grouping_id suffix (after stripping prefix).
     */
    public static function parseSequence(string $suffix): int
    {
        if (strlen($suffix) > 4 && $suffix[0] === '0') {
            return (int) substr($suffix, 1);
        }
        return (int) $suffix;
    }

    /**
     * Group name stripped of spaces — used as grouping_id prefix.
     */
    public function getGroupingPrefixAttribute(): string
    {
        return str_replace(' ', '', $this->name);
    }

    public function scopeByCompany($query, $companyId)
    {
        if ($companyId && Auth::user()->role->name != RoleSchema::ROOT) {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereIn('company_id', $companyIds);
        }
    }
}

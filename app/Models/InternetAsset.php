<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use App\Schemas\RoleSchema;

class InternetAsset extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    protected $fillable = [
        'company_id', 'name', 'category', 'brand', 'model',
        'serial_number', 'quantity', 'unit_price', 'purchase_date',
        'vendor', 'warranty_months', 'status', 'damaged_at', 'sold_at',
        'notes', 'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'damaged_at'    => 'date',
        'sold_at'       => 'date',
        'unit_price'    => 'decimal:2',
        'quantity'      => 'integer',
        'warranty_months' => 'integer',
    ];

    // ── Computed ───────────────────────────────────────────────────
    public function getTotalPriceAttribute(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    public function getAgeMonthsAttribute(): int
    {
        return (int) $this->purchase_date->diffInMonths(now());
    }

    public function getWarrantyExpiredAttribute(): bool
    {
        if ($this->warranty_months <= 0) return true;
        return $this->purchase_date->addMonths($this->warranty_months)->isPast();
    }

    public function getCategoryLabelAttribute(): string
    {
        $map = [
            'router'   => 'Router',
            'switch'   => 'Switch',
            'odp'      => 'ODP',
            'onu'      => 'ONU',
            'cable'    => 'Kabel',
            'server'   => 'Server',
            'tower'    => 'Tower',
            'antenna'  => 'Antena',
            'splitter' => 'Splitter',
            'other'    => 'Lainnya',
        ];
        return $map[$this->category] ?? ucfirst($this->category);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'      => '<span class="badge badge-success">Aktif</span>',
            'damaged'     => '<span class="badge badge-danger">Rusak</span>',
            'maintenance' => '<span class="badge badge-warning">Maintenance</span>',
            'sold'        => '<span class="badge badge-secondary">Dijual</span>',
            default       => '<span class="badge badge-light">-</span>',
        };
    }

    // ── Relations ──────────────────────────────────────────────────
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeByCompany($query, $companyId)
    {
        if ($companyId && Auth::user()->role->name !== RoleSchema::ROOT) {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereIn('company_id', $companyIds);
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public static function categoryOptions(): array
    {
        return [
            'router'   => 'Router',
            'switch'   => 'Switch',
            'odp'      => 'ODP',
            'onu'      => 'ONU',
            'cable'    => 'Kabel',
            'server'   => 'Server',
            'tower'    => 'Tower',
            'antenna'  => 'Antena',
            'splitter' => 'Splitter',
            'other'    => 'Lainnya',
        ];
    }
}

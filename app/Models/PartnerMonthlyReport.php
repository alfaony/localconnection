<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerMonthlyReport extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'partner_target_value_id', 'year', 'month', 'achievement_value',
        'achievement_percentage', 'notes', 'reported_by', 'reported_at',
        'user_id',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'achievement_value' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
        'reported_at' => 'datetime',
    ];

    public function partnerTargetValue(): BelongsTo
    {
        return $this->belongsTo(PartnerTargetValue::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($report) {
            if ($report->partnerTargetValue && $report->partnerTargetValue->target_value > 0) {
                $report->achievement_percentage = ($report->achievement_value / $report->partnerTargetValue->target_value) * 100;
            }
            if (is_null($report->reported_at)) {
                $report->reported_at = now();
            }
        });
    }

    public function getMonthNameAttribute(): string
    {
        return config('partners.months')[$this->month] ?? $this->month;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerTargetValue extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'partner_target_id', 'parameter_type_id', 'target_value', 'description',
    ];

    protected $casts = ['target_value' => 'decimal:2'];

    public function partnerTarget(): BelongsTo
    {
        return $this->belongsTo(PartnerTarget::class);
    }

    public function parameterType(): BelongsTo
    {
        return $this->belongsTo(PartnerParameterType::class, 'parameter_type_id');
    }

    public function monthlyReports(): HasMany
    {
        return $this->hasMany(PartnerMonthlyReport::class);
    }

    public function getTotalAchievement(): float
    {
        return $this->monthlyReports->sum('achievement_value');
    }

    public function getAchievementPercentage(): float
    {
        if ($this->target_value == 0) return 0;
        return ($this->getTotalAchievement() / $this->target_value) * 100;
    }

    public function getMonthlyReport($month, $year = null): ?PartnerMonthlyReport
    {
        $year = $year ?? $this->partnerTarget->year;
        return $this->monthlyReports()
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }
}
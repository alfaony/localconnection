<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverageServiceDistribution extends Model
{
    use SoftDeletes;

    protected $fillable = ['optical_distribution_id', 'coverage_service_id'];

    public function coverageService(): BelongsTo
    {
        return $this->belongsTo(CoverageService::class)->withTrashed();
    }

    public function ods(): BelongsTo
    {
        return $this->belongsTo(OpticalDistribution::class, 'optical_distribution_id')->withTrashed();
    }
}

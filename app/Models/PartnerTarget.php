<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerTarget extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['partner_id', 'year', 'status', 'notes', 'created_by'];
    protected $casts = ['year' => 'integer'];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function targetValues(): HasMany
    {
        return $this->hasMany(PartnerTargetValue::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

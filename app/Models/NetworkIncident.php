<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkIncident extends Model
{
    const TYPE_SINGLE = 'single';
    const TYPE_MASS_OUTAGE = 'mass_outage';

    protected $fillable = [
        'company_id',
        'internet_customer_id',
        'router_id',
        'type',
        'affected_count',
        'detected_at',
        'resolved_at',
        'noc_notified_at',
        'customer_notified_at',
        'noc_wa_response',
        'customer_wa_response',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
        'noc_notified_at' => 'datetime',
        'customer_notified_at' => 'datetime',
    ];

    public function internetCustomer()
    {
        return $this->belongsTo(InternetCustomer::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class)->withTrashed();
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function getIsResolvedAttribute(): bool
    {
        return $this->resolved_at !== null;
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return $this->detected_at->diffInMinutes(now());
        }

        return $this->detected_at->diffInMinutes($this->resolved_at);
    }
}

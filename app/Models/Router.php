<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Router extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_UP = 'UP';
    const STATUS_DOWN = 'DOWN';
    const STATUS_ERROR = 'ERROR';
    const STATUS_UNKNOWN = 'UNKNOWN';

    protected $fillable = [
        'pop_id',
        'company_id',
        'user_id',
        'name',
        'host',
        'port',
        'username',
        'password',
        'ssl',
        'active_status',      // ✅ Store status di DB
        'last_check_at',      // ✅ Track last health check
        'last_error',         // ✅ Store error message
    ];

    protected $casts = [
        'ssl' => 'boolean',
        'last_check_at' => 'datetime',
    ];

    protected $appends = ['is_online'];

    // ✅ Relations
    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class, 'pop_id')->withTrashed();
    }

    public function defaultPool()
    {
        return $this->belongsTo(AddressPool::class, 'default_pool_id');
    }

    public function pppoeServers()
    {
        return $this->hasMany(PppoeServer::class, 'router_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    // ✅ IMPROVED: Get cached status without auto-refresh
    public function getActiveAttribute($value)
    {
        // Return DB value directly, or use cache if needed
        return $this->active_status ?? self::STATUS_UNKNOWN;
    }

    // ✅ NEW: Check if router is online (boolean helper)
    public function getIsOnlineAttribute(): bool
    {
        return $this->active_status === self::STATUS_UP ?? $this->active == self::STATUS_UP; 
    }

    // ✅ NEW: Update status method (called by dedicated job)
    public function updateHealthStatus(string $status, ?string $error = null): void
    {
        $this->update([
            'active_status' => $status,
            'last_check_at' => now(),
            'last_error' => $error,
        ]);

        // Invalidate related caches
        Cache::forget("router_{$this->id}_active");
    }

    // ✅ NEW: Check if status needs refresh
    public function needsHealthCheck(): bool
    {
        if (!$this->last_check_at) {
            return true;
        }

        // Check every 2 minutes for active routers
        if ($this->active_status === self::STATUS_UP) {
            return $this->last_check_at->diffInMinutes(now()) >= 2;
        }

        // Check every 5 minutes for down/error routers
        return $this->last_check_at->diffInMinutes(now()) >= 5;
    }

    // ✅ Scope: Only online routers
    public function scopeOnline($query)
    {
        return $query->where('active_status', self::STATUS_UP);
    }

    public function scopeByCompany($query, $company_id)
    {
        return $query->where('company_id', $company_id);
    }
}
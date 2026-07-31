<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Olt extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_UP = 'UP';
    public const STATUS_DOWN = 'DOWN';
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_UNKNOWN = 'UNKNOWN';

    protected $fillable = [
        'company_id',
        'pop_id',
        'created_by',
        'name',
        'vendor',
        'model',
        'host',
        'port',
        'snmp_version',
        'snmp_community',
        'snmp_username',
        'snmp_security_level',
        'snmp_auth_protocol',
        'snmp_auth_password',
        'snmp_priv_protocol',
        'snmp_priv_password',
        'oid_map',
        'polling_enabled',
        'polling_interval',
        'status',
        'system_description',
        'uptime_seconds',
        'last_polled_at',
        'last_success_at',
        'last_error',
    ];

    protected $casts = [
        'snmp_community' => 'encrypted',
        'snmp_auth_password' => 'encrypted',
        'snmp_priv_password' => 'encrypted',
        'oid_map' => 'array',
        'polling_enabled' => 'boolean',
        'last_polled_at' => 'datetime',
        'last_success_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function pop()
    {
        return $this->belongsTo(Pop::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function onus()
    {
        return $this->hasMany(OltOnu::class);
    }

    public function scopeByCompany($query, string $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function needsPolling(): bool
    {
        if (!$this->polling_enabled) {
            return false;
        }

        return !$this->last_polled_at
            || $this->last_polled_at->lte(now()->subMinutes($this->polling_interval));
    }
}

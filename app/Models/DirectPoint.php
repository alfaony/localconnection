<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DirectPoint extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public $incrementing = false;
    protected $keyType = 'string';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'from_user_id',
        'to_user_id',
        'division_id',
        'division_quota_lock_id',
        'point',
        'approved_point',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static $logAttributes = [
        'from_user_id',
        'to_user_id',
        'division_id',
        'point',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['from_user_id', 'to_user_id', 'division_id', 'point', 'status'])
            ->useLogName('direct_point');
    }

    public function activities()
    {
        return $this->hasMany(\Spatie\Activitylog\Models\Activity::class, 'subject_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    // Relationships
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id')->withTrashed();
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id')->withTrashed();
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->withTrashed();
    }

    public function divisionQuotaLock()
    {
        return $this->belongsTo(DivisionQuotaLock::class)->withTrashed();
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    // Scopes
    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        
        if ($companyIds) {
            return $query->whereHas('fromUser', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            });
        }
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);
    }

    public function scopeForUser($query, $userId, $type = 'received')
    {
        $column = $type === 'received' ? 'to_user_id' : 'from_user_id';
        return $query->where($column, $userId);
    }

    // Helper Methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => '<span class="badge badge-warning">Pending</span>',
            self::STATUS_APPROVED => '<span class="badge badge-success">Approved</span>',
            self::STATUS_REJECTED => '<span class="badge badge-danger">Rejected</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    // Get final approved point (use approved_point if set, otherwise original point)
    public function getFinalPointAttribute()
    {
        return $this->approved_point ?? $this->point;
    }
}

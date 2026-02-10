<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerSubscription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'software_id',
        'user_id',
        'master_account_id',
        'package_id',
        'order_number',
        'harga_bayar',
        'tanggal_mulai',
        'tanggal_expired',
        'status',
        'payment_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'harga_bayar' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_expired' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            
            // Auto-generate order number if not provided
            if (empty($model->order_number)) {
                $model->order_number = 'SUB-' . date('YmdHis') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Get the company that provides the subscription.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the customer (user) that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the master account for the subscription.
     */
    public function masterAccount()
    {
        return $this->belongsTo(MasterAccount::class);
    }

    /**
     * Get the package for the subscription.
     */
    public function package()
    {
        return $this->belongsTo(SoftwarePackage::class, 'package_id');
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class, 'subscription_id');
    }

    /**
     * Get the latest payment.
     */
    public function latestPayment()
    {
        return $this->hasOne(SubscriptionPayment::class, 'subscription_id')->latestOfMany();
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope a query to only include paid subscriptions.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to filter by company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query for expiring soon subscriptions.
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('status', 'active')
            ->where('tanggal_expired', '<=', Carbon::now()->addDays($days))
            ->where('tanggal_expired', '>=', Carbon::now());
    }

    /**
     * Check if subscription is active and paid.
     */
    public function isActivePaid()
    {
        return $this->status === 'active' && $this->payment_status === 'paid';
    }

    /**
     * Check if subscription is expiring soon (within 7 days).
     */
    public function isExpiringSoon($days = 7)
    {
        if (!$this->tanggal_expired) {
            return false;
        }
        
        $daysUntilExpiry = Carbon::now()->diffInDays($this->tanggal_expired, false);
        return $daysUntilExpiry >= 0 && $daysUntilExpiry <= $days;
    }

    /**
     * Get days until expiry.
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->tanggal_expired) {
            return null;
        }
        
        return Carbon::now()->diffInDays($this->tanggal_expired, false);
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute()
    {
        return [
            'active' => 'success',
            'expired' => 'danger',
            'suspended' => 'warning',
        ][$this->status] ?? 'secondary';
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->harga_bayar, 0, ',', '.');
    }
}
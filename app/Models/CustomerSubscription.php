<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\SubscriptionChat;

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
                $model->order_number = 'BOS-' . date('YmHis') . '-' . strtoupper(Str::random(4));
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
        return $this->belongsTo(User::class)->withTrashed();
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
        return $this->belongsTo(SoftwarePackage::class, 'package_id')->withTrashed();
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

    public function software()
    {
        return $this->belongsTo(Software::class, 'software_id')->withTrashed();
    }

    /**
     * Get chats for this subscription.
     */
    public function chats()
    {
        return $this->hasMany(SubscriptionChat::class, 'subscription_id');
    }

    /**
     * Check if chat is allowed (active + paid + not expired).
     */
    public function canChat(): bool
    {
        return $this->status === 'active'
            && $this->payment_status === 'paid'
            && ($this->tanggal_expired === null || $this->tanggal_expired->isFuture());
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
     * Slot reservation deadline: 24 hours after creation.
     */
    public function getSlotDeadlineAttribute()
    {
        $hours = (int) env('SLOT_RESERVATION_HOURS', 1);
        return $this->created_at ? $this->created_at->copy()->addHours($hours) : null;
    }

    /**
     * Scope: unpaid subscriptions whose slot reservation has expired (created_at + 24h < now).
     */
    public function scopeSlotExpired($query)
    {
        $hours = (int) env('SLOT_RESERVATION_HOURS', 1);
        return $query->where('payment_status', 'unpaid')
            ->whereIn('status', ['active', 'pending'])
            ->where('created_at', '<', Carbon::now()->subHours($hours))
            ->whereDoesntHave('payments', function ($q) {
                $q->whereNotNull('manual_transfer_proof');
            });
    }

    /**
     * Get human-readable remaining time for slot reservation.
     * Returns null if not applicable.
     */
    public function getSlotRemainingAttribute()
    {
        if ($this->payment_status !== 'unpaid' || !$this->created_at) {
            return null;
        }

        $hasProof = $this->payments()->whereNotNull('manual_transfer_proof')->exists();
        if ($hasProof) {
            return 'Menunggu Konfirmasi';
        }

        $now = Carbon::now();
        $deadline = $this->slot_deadline;

        if ($now->gte($deadline)) {
            return 'Expired';
        }

        return $now->diffForHumans($deadline, ['parts' => 2, 'short' => true]);
    }

    /**
     * Check if slot reservation has expired (24h after creation).
     */
    public function isSlotExpired(): bool
    {
        if (!$this->created_at || $this->payment_status !== 'unpaid') {
            return false;
        }
        
        $hasProof = $this->payments()->whereNotNull('manual_transfer_proof')->exists();
        if ($hasProof) {
            return false;
        }

        return Carbon::now()->gte($this->slot_deadline);
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
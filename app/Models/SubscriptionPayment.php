<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubscriptionPayment extends Model
{
    use HasFactory;

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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subscription_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'subscription_id',
        'amount',
        'subtotal',
        'ppn_rate',
        'ppn_amount',
        'payment_gateway',
        'xendit_invoice_id',
        'xendit_external_id',
        'payment_method',
        'payment_channel',
        'status',
        'paid_at',
        'expired_at',
        'manual_transfer_bank',
        'manual_transfer_account_name',
        'manual_transfer_account_number',
        'manual_transfer_proof',
        'manual_transfer_sender_name',
        'manual_transfer_sender_bank',
        'midtrans_snap_token',
        'midtrans_order_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'ppn_rate' => 'decimal:2',
        'ppn_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        });
    }

    /**
     * Get the company that owns the payment.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the subscription that owns the payment.
     */
    public function subscription()
    {
        return $this->belongsTo(CustomerSubscription::class, 'subscription_id');
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter by company.
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Check if payment is paid.
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is expired.
     */
    public function isExpired()
    {
        return $this->status === 'expired' || 
               ($this->expired_at && $this->expired_at->isPast());
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute()
    {
        return [
            'pending' => 'warning',
            'paid' => 'success',
            'expired' => 'danger',
            'failed' => 'danger',
        ][$this->status] ?? 'secondary';
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get payment method display name.
     */
    public function getPaymentMethodDisplayAttribute()
    {
        $methods = [
            'CREDIT_CARD' => 'Kartu Kredit',
            'BCA' => 'BCA Virtual Account',
            'BNI' => 'BNI Virtual Account',
            'BRI' => 'BRI Virtual Account',
            'MANDIRI' => 'Mandiri Virtual Account',
            'PERMATA' => 'Permata Virtual Account',
            'ALFAMART' => 'Alfamart',
            'INDOMARET' => 'Indomaret',
            'OVO' => 'OVO',
            'DANA' => 'DANA',
            'LINKAJA' => 'LinkAja',
            'SHOPEEPAY' => 'ShopeePay',
            'QRIS' => 'QRIS',
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get payment gateway display name.
     */
    public function getPaymentGatewayDisplayAttribute()
    {
        $gateways = [
            'manual' => 'Manual Transfer',
            'xendit' => 'Xendit',
            'midtrans' => 'Midtrans',
        ];

        return $gateways[$this->payment_gateway] ?? $this->payment_gateway;
    }

    /**
     * Scope a query to filter by payment gateway.
     */
    public function scopeByPaymentGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * Check if payment is manual transfer.
     */
    public function isManualTransfer()
    {
        return $this->payment_gateway === 'manual';
    }

    /**
     * Check if payment is Xendit.
     */
    public function isXendit()
    {
        return $this->payment_gateway === 'xendit';
    }

    /**
     * Check if payment is Midtrans.
     */
    public function isMidtrans()
    {
        return $this->payment_gateway === 'midtrans';
    }

    /**
     * Get formatted subtotal.
     */
    public function getSubtotalFormattedAttribute()
    {
        return $this->subtotal ? 'Rp ' . number_format($this->subtotal, 0, ',', '.') : null;
    }

    /**
     * Get formatted PPN amount.
     */
    public function getPpnAmountFormattedAttribute()
    {
        return $this->ppn_amount ? 'Rp ' . number_format($this->ppn_amount, 0, ',', '.') : null;
    }

    /**
     * Check if payment has PPN.
     */
    public function hasPpn()
    {
        return $this->ppn_rate > 0 && $this->ppn_amount > 0;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class InternetCustomerPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internet_package_id',
        'internet_customer_id',
        'user_finance_id',
        'confirmation_finance_at',
        'amount_paid',
        'payment_months',
        'period_start',
        'period_end',
        'discount_amount',
        'amount_before_tax',
        'tax_rate',
        'tax_amount',
        'total_before_discount',
        'payment_method',
        'payment_proof',
        'payment_date',
        'transfer_date',
        'transfer_from_bank',
        'transfer_from_account_name',
        'transfer_notes',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'xendit_payment_channel',
        'xendit_payment_method',
        'xendit_paid_at',
        'xendit_raw_response',
        'midtrans_snap_token',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_paid_at',
        'midtrans_raw_response'
    ];

    protected $casts = [
        'xendit_raw_response' => 'array',
        'midtrans_raw_response' => 'array',
        'confirmation_finance_at' => 'datetime',
        'payment_date' => 'datetime',
        'xendit_paid_at' => 'datetime',
        'midtrans_paid_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'transfer_date' => 'date',
    ];

    // Configuration - bisa diubah sesuai kebutuhan
    const ENABLE_DISCOUNT = false; // Set true untuk enable discount
    const MIN_MONTHS = 1;
    const MAX_MONTHS = 24; // Maksimal 24 bulan (2 tahun)

    public function customer()
    {
        return $this->belongsTo(InternetCustomer::class, 'internet_customer_id');
    }

    public function userFinance()
    {
        return $this->belongsTo(User::class, 'user_finance_id');
    }

    public function coupons()
    {
        return $this->hasMany(InternetPurchaseCoupon::class,'internet_purchase_id');
    }

    /**
     * Get discount percentage based on payment months
     * Bisa disesuaikan dengan kebutuhan bisnis
     */
    public static function getDiscountPercentage($months)
    {
        if (!self::ENABLE_DISCOUNT) {
            return 0;
        }

        // CUSTOMIZE DISINI - Sesuaikan dengan kebutuhan bisnis
        $discounts = [
            3 => 5,     // 3 bulan = 5%
            6 => 10,    // 6 bulan = 10%
            9 => 12,    // 9 bulan = 12% (bisa ditambah)
            12 => 15,   // 12 bulan = 15%
            18 => 18,   // 18 bulan = 18% (bisa ditambah)
            24 => 20,   // 24 bulan = 20%
        ];

        $applicableDiscount = 0;
        foreach ($discounts as $tier => $percent) {
            if ($months >= $tier) {
                $applicableDiscount = $percent;
            }
        }

        return $applicableDiscount;
    }

    public static function getDiscountTiers()
    {
        if (!self::ENABLE_DISCOUNT) {
            return [];
        }

        // CUSTOMIZE DISINI - Tombol quick selection yang muncul
        return [
            ['months' => 3, 'discount' => 5, 'label' => 'Hemat 5%'],
            ['months' => 6, 'discount' => 10, 'label' => 'Hemat 10%'],
            ['months' => 12, 'discount' => 15, 'label' => 'Hemat 15%'],
            ['months' => 24, 'discount' => 20, 'label' => 'Hemat 20%'],
        ];
    }

    /**
     * Calculate total amount with discount
     */
    public static function calculateTotal($monthlyPrice, $months)
    {
        $subtotal = $monthlyPrice * $months;
        $discountPercentage = self::getDiscountPercentage($months);
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $total = $subtotal - $discountAmount;

        return [
            'months' => $months,
            'monthly_price' => $monthlyPrice,
            'subtotal' => $subtotal,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'has_discount' => $discountPercentage > 0
        ];
    }

    /**
     * Get formatted period - Format: "Jan 2025 - Mar 2025"
     */
    public function getPeriodFormattedAttribute()
    {
        if ($this->period_start && $this->period_end) {
            $start = Carbon::parse($this->period_start);
            $end = Carbon::parse($this->period_end);
            
            if ($start->year == $end->year) {
                return $start->format('M Y') . ' - ' . $end->format('M Y');
            }
            
            return $start->format('M Y') . ' - ' . $end->format('M Y');
        }
        return '-';
    }

    /**
     * Get detailed period - Format: "1 Jan 2025 - 31 Mar 2025"
     */
    public function getPeriodDetailedAttribute()
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->format('d M Y') . ' - ' . $this->period_end->format('d M Y');
        }
        return '-';
    }


    public function isWaiting()
    {
        return $this->payment_method && (!$this->confirmation_finance_at || !$this->xendit_paid_at);
    }
    /**
     * Check if payment is confirmed
     */
    public function isConfirmed()
    {
        return $this->user_finance_id && $this->confirmation_finance_at;
    }

    /**
     * Check if this period is currently active
     */
    public function isActivePeriod()
    {
        if (!$this->period_start || !$this->period_end) {
            return false;
        }

        $now = now();
        return $now->between($this->period_start, $this->period_end);
    }

    /**
     * Check if this period has expired
     */
    public function isExpired()
    {
        // Check if manually marked as expired
        if ($this->payment_method == \App\Schemas\ParamSchema::EXPIRED) {
            return true;
        }

        // Check if period has ended
        if (!$this->period_end) {
            return false;
        }

        return now()->greaterThan($this->period_end);
    }

    /**
     * Mark this payment as expired
     */
    public function markAsExpired()
    {
        $this->payment_method = \App\Schemas\ParamSchema::EXPIRED;
        $this->save();
        
        return $this;
    }

    /**
     * Get remaining days in this period
     */
    public function getRemainingDaysAttribute()
    {
        if (!$this->period_end) {
            return 0;
        }

        $remaining = now()->diffInDays($this->period_end, false);
        return max(0, $remaining);
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->payment_method == \App\Schemas\ParamSchema::EXPIRED) {
            return '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Expired</span>';
        } elseif ($this->payment_method == 'xendit') {
            return '<span class="badge badge-success"><i class="fas fa-credit-card mr-1"></i>Xendit</span>';
        } elseif ($this->payment_method == 'midtrans') {
            return '<span class="badge badge-warning"><i class="fas fa-credit-card mr-1"></i>Midtrans</span>';
        } elseif ($this->payment_method == 'manual_transfer') {
            return '<span class="badge badge-info"><i class="fas fa-university mr-1"></i>Transfer Manual</span>';
        } elseif ($this->payment_method == 'transfer') {
            return '<span class="badge badge-info"><i class="fas fa-university mr-1"></i>Transfer Manual</span>';
        } else {
            return ucfirst($this->payment_method ?? '');
        }
    }
}
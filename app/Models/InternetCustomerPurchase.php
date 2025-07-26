<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetCustomerPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internet_customer_id', 'user_finance_id', 'confirmation_finance_at',
        'amount_paid', 'payment_method', 'payment_proof',
        'xendit_invoice_id', 'xendit_payment_method',
        'xendit_paid_at', 'xendit_raw_response'
    ];

    public function customer()
    {
        return $this->belongsTo(InternetCustomer::class, 'internet_customer_id');
    }
}

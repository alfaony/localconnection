<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternetPurchaseCoupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'internet_customer_id',
        'internet_purchase_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function internetPurchase()
    {
        return $this->belongsTo(InternetCustomerPurchase::class, 'internet_purchase_id');
    }

    public function internetCustomer()
    {
        return $this->belongsTo(InternetCustomer::class, 'internet_customer_id');
    }
}

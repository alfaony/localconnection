<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_request_id',
        'product_supplier_id',
        'sprinter_id',
        'actual_price',
        'bon_photo',
        'payment_photo',
        'status',
        'payment_term_date',
        'payment_method',
        'rekening_number',
        'note',
    ];


    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class)->withTrashed();
    }

    public function productSupplier()
    {
        return $this->belongsTo(ProductSupplier::class,'product_supplier_id')->withTrashed();
    }

    public function sprinter()
    {
        return $this->belongsTo(User::class, 'sprinter_id')->withTrashed();
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }
}

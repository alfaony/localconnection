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
        'vendor_id',
        'sprinter_id',
        'actual_price',
        'bon_photo',
    ];

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class)->withTrashed();
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
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

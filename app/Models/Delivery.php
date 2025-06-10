<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_request_id',
        'sprinter_id',
        'resi_number',
        'shipping_method',
        'resi_number',
        'airwillbill_photo',
        'delivery_photo',
        'delivered_at',
    ];

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class, 'item_request_id')->withTrashed();
    }

    public function sprinter()
    {
        return $this->belongsTo(User::class, 'sprinter_id')->withTrashed();
    }
}

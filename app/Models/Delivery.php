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
        'item_purchase_id',
        'sprinter_id',
        'resi_number',
        'delivery_photo',
        'delivered_at',
    ];

    public function purchase()
    {
        return $this->belongsTo(ItemPurchase::class, 'item_purchase_id')->withTrashed();
    }

    public function sprinter()
    {
        return $this->belongsTo(User::class, 'sprinter_id')->withTrashed();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_purchase_id',
        'finance_id',
        'proof_image',
        'paid_at',
    ];

    public function purchase()
    {
        return $this->belongsTo(ItemPurchase::class, 'item_purchase_id')->withTrashed();
    }

    public function finance()
    {
        return $this->belongsTo(User::class, 'finance_id')->withTrashed();
    }
}

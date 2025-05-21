<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'supplier_category_id',
        'item_name',
        'description',
        'estimated_price',
        'assigned_pic_id',
        'status',
        'qty'
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function assignedPic()
    {
        return $this->belongsTo(User::class, 'assigned_pic_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(SupplierCategory::class,'supplier_category_id');
    }

    public function potentialVendors()
    {
        return $this->hasMany(PotentialVendor::class);
    }

    public function purchase()
    {
        return $this->hasOne(ItemPurchase::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

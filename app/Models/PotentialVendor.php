<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PotentialVendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_request_id',
        'vendor_id',
        'responded',
        'responded_at',
    ];

    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class)->withTrashed();
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
    }
}

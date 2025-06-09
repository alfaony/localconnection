<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;

class PotentialVendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_request_id',
        'product_supplier_id',
        'responded',
        'responded_at',
        'status',
        'price_offered',
        'note',
    ];

    // Auto generate response_token jika belum ada
    public static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->response_token)) {
                $vendor->response_token = static::generateUniqueToken();
            }
        });
    }

    protected static function generateUniqueToken()
    {
        do 
    {
            $token = Str::random(40);
        } while (static::withTrashed()->where('response_token', $token)->exists());

        return $token;
    }
    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class)->withTrashed();
    }

    public function productSupplier()
    {
        return $this->belongsTo(ProductSupplier::class, 'product_supplier_id')->withTrashed();
    }
}

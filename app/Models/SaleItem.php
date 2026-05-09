<?php
// app/Models/SaleItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_store_id',
        'quantity',
        'unit_price',
        'original_price',
        'discount_percent',
        'discount_type',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'unit_price'       => 'decimal:2',
        'original_price'   => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function productStore()
    {
        return $this->belongsTo(ProductStore::class)->withTrashed();
    }
}
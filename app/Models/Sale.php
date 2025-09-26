<?php
// app/Models/Sale.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) 
            {
                $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
            }
            if(empty($model->transaction_code))
            {
                $model->genreateTransactionCode();
            }
        });
    }

    protected $fillable = [
        'transaction_code',
        'total_amount',
        'tax_amount',
        'tax_value',
        'discount_amount',
        'final_amount',
        'payment_method',
        'payment_details',
        'status',
        'user_id',
        'customer_email',
        'transaction_number',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    private function genreateTransactionCode()
    {
         // Generate transaction_number
        $now = Carbon::now();
        $month = $now->format('m');
        $year = $now->format('Y');

        $lastSale = self::byCompany(auth()->user()->company_id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('transaction_number', 'desc')
            ->first();

        
        $nextNumber = $lastSale && $lastSale->transaction_number !== null ? intval($lastSale->transaction_number) + 1 : 1;

        // Format with leading zeroes (e.g. 0001)

        $this->transaction_number = $nextNumber;
        $randomCode = Str::random(6);
        $nowTransaction = Carbon::now()->format('h-i-s');
        $this->transaction_code = "{$nextNumber}-{$month}-{$year}-{$randomCode}-{$nowTransaction}";
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function productStores()
    {
        return $this->hasManyThrough(ProductStore::class, SaleItem::class, 'sale_id', 'id', 'id', 'product_store_id');
    }

    public function scopeByCompany($query,$companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereHas('user', function ($query) use ($companyIds) 
        {
            $query->whereIn('company_id', $companyIds);
        });
    }
}
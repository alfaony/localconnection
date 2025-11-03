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

    public function getPaymentMethodHtmlAttribute()
    {
        if ($this->payment_method === 'cash') {
            return '<span class="badge bg-success">Cash Payment</span>';
        }

        if ($this->payment_method === 'debit_credit') {
            return '<span class="badge bg-primary">Debit / Kredit</span>';
        }

        if ($this->payment_method === 'qris') {
            return '<span class="badge bg-info">QRIS</span>';
        }

        return '<span class="text-muted">No Details</span>';
    }

    public function getPaymentDetailsHtmlAttribute()
    {
        // Jika metode pembayaran adalah CASH
        if ($this->payment_method === 'cash') {
            $details = $this->payment_details;
            $amount = isset($details['cash_amount']) ? number_format($details['cash_amount'], 0, ',', '.') : '-';

            return '
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Metode Pembayaran Tunai</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Jumlah Uang Tunai : Rp ' . $amount . '
                        </p>
                    </div>
                </div>';
        }

        // Jika metode pembayaran adalah DEBIT/KREDIT
        if ($this->payment_method === 'debit_credit') {
            $details = $this->payment_details;

            return '
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Metode Pembayaran Kartu Debit/Kredit</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Nama Bank : ' . (isset($details['bankName']) ? $details['bankName'] : '-') . '<br>
                            Nomor Kartu : ' . (isset($details['cardNumber']) ? $details['cardNumber'] : '-') . '<br>
                            Pengesah : ' . (isset($details['cardEdcApprover']) ? $details['cardEdcApprover'] : '-') . '
                        </p>
                    </div>
                </div>';
        }

        // Jika metode pembayaran adalah QRIS
        if ($this->payment_method === 'qris') {
            $details = $this->payment_details;

            return '
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Metode Pembayaran QRIS</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Nama Bank QRIS : ' . (isset($details['bankName']) ? $details['bankName'] : '-') . '
                        </p>
                    </div>
                </div>';
        }

        // Default jika tidak dikenali
        return '<span class="text-muted">Tidak ada detail</span>';
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
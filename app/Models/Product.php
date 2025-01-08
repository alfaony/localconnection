<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory,SoftDeletes,LogsActivity;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string
    protected static $logName = 'product';

    // Spatie
    protected $fillable = [
        'price_sell', 'price_buy', 'user_created_id', 'user_updated_id',
    ];

    protected static $logAttributes = [
        'price_sell', 'price_buy', 'user_created_id', 'user_updated_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['price_sell', 'price_buy','user_created_id','user_updated_id'])
            ->useLogName('product');
        ;
    }
    public function activities()
    {
        return $this->hasMany(\Spatie\Activitylog\Models\Activity::class, 'subject_id');
    }
    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class,'product_category_id')->withTrashed();
    }
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function setNameAttribute($value)
    {
       if ($this->name != $value || $this->slug == '') {
            $this->attributes['name'] = $value;
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        } else {
            $this->attributes['name'] = $value;
        }
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::where('slug', $slug)->withTrashed()->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
    /**
     * Relasi ke tabel QuoteProduct (Produk dalam Penawaran)
     */
    public function quoteProducts()
    {
        return $this->hasMany(QuoteProduct::class, 'product_id');
    }

    /**
     * Relasi ke tabel WorkOrderProduct (Produk dalam Work Order)
     */
    public function workOrderProducts()
    {
        return $this->hasMany(WorkOrderProduct::class, 'product_id');
    }

    /**
     * Relasi ke tabel Purchase (Produk dalam Pembelian)
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'product_id');
    }

    /**
     * Relasi ke tabel InvoiceProduct (Produk dalam Faktur)
     */
    public function invoiceProducts()
    {
        return $this->hasMany(InvoiceProduct::class, 'product_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_created_id')->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class,'product_category_id')->withTrashed();
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }
}

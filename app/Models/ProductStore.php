<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class ProductStore extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    protected $fillable = [
        'barcode',
        'code',
        'category_product_store_id',
        'brand_product_store_id',
        'name',
        'variant',
        'specification',
        'length',
        'width',
        'height',
        'dimension',
        'weight',
        'selling_price',
        'user_create_id',
        'user_modified_id',
        'company_id',
        'rack_id',
    ];

    protected $casts = [
        'id' => 'string',
        'barcode' => 'string',
        'user_create_id' => 'string',
        'user_modified_id' => 'string',
        'company_id' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) 
            {
                $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
            }
            if($model->barcode == null)
            {
                $model->barcode = self::generateBarcode();
            }
            $model->dimension = $model->length . ' x ' . $model->width . ' x ' . $model->height;
        });

        static::updating(function ($model) {
            $model->user_modified_id = auth()->id();
            $model->dimension = $model->length . ' x ' . $model->width . ' x ' . $model->height;
        });
    }

    protected static function generateBarcode()
    {
        do {
            $barcode = now()->format('Y') . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        } while (self::withTrashed()->where('barcode', $barcode)->exists());

        return $barcode;
    }

    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
                    ->orWhere('variant', 'like', '%' . $value . '%')
                    ->orWhere('specification', 'like', '%' . $value . '%')
                    ->orWhere('barcode', 'like', '%' . $value . '%')
                    ->orWhere('code', 'like', '%' . $value . '%')
                    ;
    }

    public function scopeByCompany($query,$companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'rack_id')->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryProductStore::class, 'category_product_store_id')->withTrashed();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandProductStore::class, 'brand_product_store_id')->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_create_id')->withTrashed();
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_modified_id')->withTrashed();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id')->withTrashed();
    }

    public function media()
    {
        return $this->hasMany(ProductStoreMedia::class, 'product_store_id')->orderBy('order');
    }

    public function primaryMedia()
    {
        return $this->hasOne(ProductStoreMedia::class, 'product_store_id')->orderBy('order')->limit(1);
}
}
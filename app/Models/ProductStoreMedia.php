<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductStoreMedia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_store_id',
        'order',
        'file_path',
        'caption'
    ];

    protected $appends = ['file_url'];

    protected $casts = [
        'order' => 'integer',
        'product_store_id' => 'string'
    ];

    public function productStore()
    {
        return $this->belongsTo(ProductStore::class, 'product_store_id');
    }

    public function getFileUrlAttribute()
    {
        return s3_asset(true,10,$this->file_path);
    }

    public function getFileSizeAttribute()
    {
        $path = storage_path('app/public/' . $this->file_path);
        return file_exists($path) ? filesize($path) : 0;
    }

    public function getFileNameAttribute()
    {
        return basename($this->file_path);
    }
}   
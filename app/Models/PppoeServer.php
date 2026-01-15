<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class PppoeServer extends Model
{
    use SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'router_id','interface_id','service_name','address_pool_id','only_one','meta'
    ];
    protected $casts = [
        'only_one' => 'boolean',
        'meta' => 'array',
    ];

    public function router() { return $this->belongsTo(Router::class); }
    public function interface() { return $this->belongsTo(RouterInterface::class, 'interface_id'); }
    public function addressPool() { return $this->belongsTo(AddressPool::class, 'address_pool_id'); }
}
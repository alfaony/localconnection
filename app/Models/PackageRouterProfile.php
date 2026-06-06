<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class PackageRouterProfile extends Model
{
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
        'id','router_id','package_id','ros_profile','address_pool_id','local_address','ros_queue_type_up','ros_queue_type_down','meta'
    ];
    protected $casts = ['meta' => 'array'];

    public function router()      { return $this->belongsTo(Router::class); }
    public function package()     { return $this->belongsTo(InternetPackage::class, 'package_id'); }
    public function addressPool() { return $this->belongsTo(AddressPool::class, 'address_pool_id'); }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class HotspotServer extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'router_id', 'interface_id', 'name', 'address_pool_id',
        'profile_name', 'dns_name', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

    public function interface()
    {
        return $this->belongsTo(RouterInterface::class, 'interface_id');
    }

    public function addressPool()
    {
        return $this->belongsTo(AddressPool::class, 'address_pool_id');
    }

    public function voucherBatches()
    {
        return $this->hasMany(HotspotVoucherBatch::class);
    }

    public function vouchers()
    {
        return $this->hasMany(HotspotVoucher::class);
    }
}

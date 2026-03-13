<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class HotspotVoucherBatch extends Model
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
        'company_id', 'hotspot_server_id', 'internet_package_id',
        'quantity', 'prefix', 'generated_by', 'meta',
    ];

    protected $casts = [
        'meta'     => 'array',
        'quantity' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function hotspotServer()
    {
        return $this->belongsTo(HotspotServer::class, 'hotspot_server_id');
    }

    public function internetPackage()
    {
        return $this->belongsTo(InternetPackage::class, 'internet_package_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function vouchers()
    {
        return $this->hasMany(HotspotVoucher::class, 'hotspot_voucher_batch_id');
    }

    public function getUsedCountAttribute(): int
    {
        return $this->vouchers()->whereIn('status', ['active', 'expired'])->count();
    }

    public function getUnusedCountAttribute(): int
    {
        return $this->vouchers()->where('status', 'unused')->count();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class HotspotVoucher extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    const STATUS_UNUSED   = 'unused';
    const STATUS_ACTIVE   = 'active';
    const STATUS_EXPIRED  = 'expired';
    const STATUS_DISABLED = 'disabled';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'hotspot_voucher_batch_id', 'hotspot_server_id', 'internet_package_id',
        'username', 'password', 'status',
        'valid_from', 'expires_at', 'used_by_mac', 'meta',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'expires_at' => 'datetime',
        'meta'       => 'array',
    ];

    public function batch()
    {
        return $this->belongsTo(HotspotVoucherBatch::class, 'hotspot_voucher_batch_id');
    }

    public function hotspotServer()
    {
        return $this->belongsTo(HotspotServer::class, 'hotspot_server_id');
    }

    public function internetPackage()
    {
        return $this->belongsTo(InternetPackage::class, 'internet_package_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    public function isUnused(): bool
    {
        return $this->status === self::STATUS_UNUSED;
    }

    /** Aktifkan voucher saat pertama dipakai — set valid_from & expires_at */
    public function activate(string $mac = null): void
    {
        if ($this->valid_from) return; // sudah aktif

        $profile  = $this->internetPackage;
        $validFrom = now();
        $expiresAt = $profile->session_timeout_seconds > 0
            ? $validFrom->copy()->addSeconds($profile->session_timeout_seconds)
            : null;

        $this->update([
            'status'      => 'active',
            'valid_from'  => $validFrom,
            'expires_at'  => $expiresAt,
            'used_by_mac' => $mac,
        ]);
    }
}

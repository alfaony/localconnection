<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class XpConfig extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'is_enabled',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    /**
     * XP config detail per model type.
     */
    public function models()
    {
        return $this->hasMany(XpConfigModel::class);
    }

    /**
     * Companies yang menggunakan config ini.
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Ambil nilai XP untuk model tertentu.
     * Fallback ke 100 jika model tidak terdaftar di config.
     */
    public function resolveXp(string $sourceType): int
    {
        // 1. Cek jika ada konfigurasi spesifik untuk model ini
        $modelXp = $this->models->where('source_type', $sourceType)->first();
        if ($modelXp) {
            return $modelXp->xp;
        }

        // 2. Cek jika ada konfigurasi khusus 'ALL'
        $allXp = $this->models->where('source_type', 'ALL')->first();
        if ($allXp) {
            return $allXp->xp;
        }

        // 3. Final fallback jika tidak ada settingan sama sekali
        return 100;
    }
}

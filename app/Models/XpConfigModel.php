<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class XpConfigModel extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'xp_config_id',
        'source_type',
        'xp',
        'label',
    ];

    protected $casts = [
        'xp' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    /**
     * XpConfig yang memiliki entry ini.
     */
    public function config()
    {
        return $this->belongsTo(XpConfig::class, 'xp_config_id');
    }
}

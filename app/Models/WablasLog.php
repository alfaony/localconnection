<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;

class WablasLog extends Model
{
    use HasFactory, SoftDeletes;

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
        'company_id',
        'source',
        'source_id',
        'phone',
        'message',
        'type',
        'status',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeBySource($query, string $source, string $sourceId = null)
    {
        $query->where('source', $source);
        if ($sourceId) {
            $query->where('source_id', $sourceId);
        }
        return $query;
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public static function record(
        string $source,
        string $sourceId,
        string $phone,
        string $message,
        array $response = [],
        string $type = 'text',
    ): self {
        $status = (!empty($response['status']) && $response['status'] === true) ? 'success' : 'failed';

        return self::create([
            'source'     => $source,
            'source_id'  => $sourceId,
            'phone'      => $phone,
            'message'    => $message,
            'type'       => $type,
            'status'     => $status,
            'response'   => $response
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class JobsProvisioning extends Model
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

    const TYPE_PROVISION = 'provision';
    const TYPE_SUSPEND   = 'suspend';
    const TYPE_UNSUSPEND = 'unsuspend';
    const TYPE_MIGRATE   = 'migrate';
    const TYPE_RECONCILE = 'reconcile';

    const STATUS_QUEUED    = 'queued';
    const STATUS_RUNNING   = 'running';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED    = 'failed';

    protected $fillable = [
        'id','type','internet_customer_id','router_id','status','attempts','last_error','payload'
    ];
    protected $casts = [
        'attempts' => 'integer',
        'payload'  => 'array',
    ];

    public function internetCustomer() { return $this->belongsTo(InternetCustomer::class, 'internet_customer_id'); }
    public function router() { return $this->belongsTo(Router::class); }
}
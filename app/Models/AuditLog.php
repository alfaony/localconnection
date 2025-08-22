<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class AuditLog extends Model
{
    public $timestamps = false;           // hanya created_at
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
        'id','who','action','router_id','subscriber_id','internet_customer_id','before','after','created_at'
    ];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'created_at' => 'datetime',
    ];

    // NB: di migration kamu kolomnya 'subscriber_id' (UUID).
    // Kalau kamu ganti jadi 'internet_customer_id', ubah relasi di bawah.
    public function router() { return $this->belongsTo(Router::class); }
    public function internetCustomer() { return $this->belongsTo(InternetCustomer::class, 'internet_customer_id' /* atau 'subscriber_id' sesuai kolom */); }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Inbox extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id_to',
        'user_id_from',
        'message',
        'direct_url',
        'is_read',
        'is_notif',
    ];

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string
    
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function userTo()
    {
        return $this->belongsTo(User::class, 'user_id_to');
    }

    public function userFrom()
    {
        return $this->belongsTo(User::class, 'user_id_from');
    }
}


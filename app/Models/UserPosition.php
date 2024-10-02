<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;

class UserPosition extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false; // Karena menggunakan UUID
    protected $keyType = 'string'; // Mengatur tipe kunci UUID

    protected $fillable = [
        'user_id',
        'position_id',
        'start_date',
        'end_date',
    ];

    // Boot method to generate UUID when creating new records
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    // Relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke model Position
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function letterSubmission()
    {
        return $this->belongsTo(LetterSubmission::class);
    }
}

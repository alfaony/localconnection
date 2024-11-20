<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class PassChecking extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'uuid'; // Karena menggunakan UUID sebagai primary key
    public $incrementing = false; // Non-incrementing ID, karena UUID
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'pictures',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'pictures' => 'array', // Cast pictures as an array
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];


    /**
     * Get the user associated with the pass checking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }
}

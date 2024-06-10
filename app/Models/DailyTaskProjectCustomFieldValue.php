<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class DailyTaskProjectCustomFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'custom_field_id', 'ordering', 'value'];

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

    public function customField()
    {
        return $this->belongsTo(DailyTaskProjectCustomField::class, 'custom_field_id')->withTrashed();
    }
}


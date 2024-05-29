<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class DailyTaskCustomFieldValue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['daily_task_id', 'custom_field_id', 'custom_field_value_id'];

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

    public function task()
    {
        return $this->belongsTo(DailyTask::class, 'daily_task_id');
    }

    public function customField()
    {
        return $this->belongsTo(DailyTaskProjectCustomField::class, 'custom_field_id');
    }

    public function customFieldValue()
    {
        return $this->belongsTo(DailyTaskProjectCustomFieldValue::class, 'custom_field_value_id');
    }
}
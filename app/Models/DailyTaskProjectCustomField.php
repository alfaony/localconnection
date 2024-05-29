<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class DailyTaskProjectCustomField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'daily_task_project_id', 'name', 'type'];

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

    public function daily_task_project()
    {
        return $this->belongsTo(DailyTaskProject::class)->withTrashed();
    }

    public function values()
    {
        return $this->hasMany(DailyTaskProjectCustomFieldValue::class,'custom_field_id');
    }
}


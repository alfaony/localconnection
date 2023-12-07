<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class ReportProjectDetail extends Model
{
    use HasFactory,SoftDeletes;

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

    public function reportProject()
    {
        return $this->belongsTo(ReportProject::class)->withTrashed();
    }

    public function sortUrl()
    {
        return $this->hasOne(SortUrl::class,'source_id');
    }

    public function getUrlAttribute()
    {
        return $this->sortUrl ? url('/')."/".$this->sortUrl->slug : '';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class WorkOrder extends Model
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

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value;
        $this->attributes['slug'] = $this->createUniqueSlug($value);
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::where('slug', $slug)->withTrashed()->exists()) 
        {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function workOrderProduct()
    {
        return $this->hasMany(WorkOrderProduct::class);
    }

    public function userCreate()
    {
        return $this->belongsTo(User::class,'user_created_id','id');
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class)->withTrashed();
    }

    public function project()
    {
        return $this->hasOne(Project::class);
    }
    
    public function scopeByNumberResult($query,$number_result)
    {
        if($number_result)
        {
            return $query->where('number_result','like','%'.$number_result.'%');
        }
    }
}
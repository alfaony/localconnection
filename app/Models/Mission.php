<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['vision_id', 'mission','company_id'];

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

    public function setNameAttribute($value)
    {
        if ($this->attributes['name'] ?? null !== $value) {
            $this->attributes['name'] = $value;
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        } else {
            $this->attributes['name'] = $value;
        }
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
    
    public function vision()
    {
        return $this->belongsTo(Vision::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }
}

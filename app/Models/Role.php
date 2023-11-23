<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Role extends Model
{
    use HasFactory,SoftDeletes;

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

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = $this->createUniqueSlug($value);
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'LIKE', "$slug%")->withTrashed()->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // public function users()
    // {
    //     return $this->hasMany(User::class);
    // }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function scopeById($query, $id = null) {
        if ($id) {
            return $query->where('id', $id);
        }
    }

    public function scopeByPermissionName($query, $method = null, $table = null) 
    {
        if ($method && $table) {
            return $query->whereHas('permissions', function ($query) use ($method, $table) {
                $query->where('method', $method)->where('table', $table);
            });
        }
    }
}
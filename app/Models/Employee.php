<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Employee extends Model
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

    public function scopeByDateRange($query,$startDate = null,$endDate = null)
    {
        if($startDate && $endDate)
        {
            return $query->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
        }else
        {
            $today = Carbon::now();
            return $query->where('start_date', '<=', $today)->where('end_date', '>=', $today);
        }
    }

    public function job()
    {
        return $this->hasMany(Job::class);
    }

    public function scopeByActiveEmployee($query)
    {
        $today = Carbon::now()->format('Y-m-d');

        return $query->whereHas('job', function($a) use ($today) 
        {
            $a->where('start_date', '<=', $today)->where('end_date', '>=', $today);
        })->distinct('id');
    }

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

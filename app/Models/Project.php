<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Project extends Model
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

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
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

    public function manager()
    {
        return $this->hasMany(Manager::class);
    }

    public function suplier()
    {
        return $this->hasMany(Suplier::class);
    }

    public function getPurchaseAttribute()
    {
        // return $this->suplier()->sum('total_price');
        $suplierPurchase = $this->suplier() ? $this->suplier()->sum('total_price') : 0 ;
        $managerJob = $this->manager() ? $this->manager()->sum('total_job') : 0 ;
        
        return $suplierPurchase + $managerJob;
    }

    public function getProfitAttribute()
    {
        return $this->budget - $this->purchase;
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

    public function scopeBySearchReport($query,$project = null ,$startDate = null,$endDate = null)
    {
        if($project && $startDate && $endDate)
        {
            return $query->where('title','like', '%' . $project . '%')->byDateRange($startDate,$endDate);
        }
        
        if($project)
        {
            return $query->where('title','like', '%' . $project . '%');
        }else
        {
            $query->byDateRange($startDate,$endDate);
        }
    }
}

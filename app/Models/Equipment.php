<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Equipment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    protected static $logAttributes = [
        'total_stock', 'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['total_stock', 'user_id'])
            ->useLogName('equipment');
        ;
    }
    public function activities()
    {
        return $this->hasMany(\Spatie\Activitylog\Models\Activity::class, 'subject_id');
    }
    
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
       if ($this->name != $value || $this->slug == '') {
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
    public function equipmentReduction()
    {
        return $this->hasMany(EquipmentReduction::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
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

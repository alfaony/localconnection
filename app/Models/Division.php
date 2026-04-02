<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Division extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = ['id', 'user_id', 'name','requires_photo','requires_location','manual_checkin','point_quota_monthly'];

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    protected static $logAttributes = [
        'user_id', 'name','point_quota_monthly'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'name','point_quota_monthly'])
            ->useLogName('division');
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
    
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function objective()
    {
        return $this->hasMany(Objective::class);
    }

    public function objectives()
    {
        return $this->belongsToMany(Objective::class, 'division_objective')->using(DivisionObjective::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('weekly_report_required', 'is_primary');
    }

    public function scopeByCompany($query,$companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        if($companyIds)
        {
            return $query->whereHas('user', function ($query) use ($companyIds) 
            {
                $query->whereIn('company_id', $companyIds);
            });
        }
    }
}

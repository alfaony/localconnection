<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

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

    public function dailyTaskProjects()
    {
        return $this->belongsToMany(DailyTaskProject::class);
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::where('slug', $slug)->withTrashed()->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
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

    public function getBudgetsAttribute()
    {
        return $this->workOrder ? $this->workOrder->total : 0;

    }

    public function getProfitAttribute()
    {
        return $this->budgets - $this->purchase;
    }

    public function getProgressPercentageAttribute()
    {
        if($this->start_date && $this->end_date)
        {
            $now = Carbon::now();
            $start_date = Carbon::parse($this->start_date);
            $end_date = Carbon::parse($this->end_date);

            if ($now->greaterThanOrEqualTo($end_date)) {
                return 100;
            }
    
            $totalDuration = $start_date->diffInSeconds($end_date);
            $elapsedDuration = $start_date->diffInSeconds($now);
    
            return $totalDuration > 0 ? intval(($elapsedDuration / $totalDuration) * 100) : 0;

        }else
        {
            return 0;
        }

    }
    
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class)->withTrashed();
    }

    public function reportProject()
    {
        return $this->hasOne(ReportProject::class);
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

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getEndDateEmailShowAttribute()
    {
        return Carbon::parse($this->end_date);
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

    public function scopeByRole($query)
    {
        if(Auth::user()->role->name == RoleSchema::STAFF || Auth::user()->role->name == RoleSchema::PM)
        {
            return $query->where('user_id', Auth::user()->id);
        }
        {
            return $query->byCompany(Auth::user()->company_id);
        }
    }
}

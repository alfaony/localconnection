<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use App\Traits\AwardsXp;

class WorkOrder extends Model
{
    use HasFactory,SoftDeletes, AwardsXp;

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
        if (empty($this->slug)) {
            $this->attributes['slug'] = Uuid::uuid4()->toString();
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
        return $this->belongsTo(User::class,'user_created_id','id')->withTrashed();
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class)->withTrashed();
    }

    public function project()
    {
        return $this->hasOne(Project::class)->withoutGlobalScopes();
    }

    public function reportProject()
    {
        return $this->hasOne(ReportProject::class);
    }

    public function bast()
    {
        return $this->hasOne(Bast::class);
    }
    
    public function scopeByNumberResult($query,$number_result)
    {
        if($number_result)
        {
            return $query->where('number_result','like','%'.$number_result.'%');
        }
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('userCreate', function ($query) use ($companyId) 
            {
                $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
                $query->whereIn('company_id', $companyIds);
            });
        }
    }

    public function scopeByActive($query)
    {
        return $query->whereDate('date', '>=', Carbon::now());
    }

    public function scopeByUser($query, $userId)
    {
        if($userId)
        {
            return $query->whereHas('quote', function($query) use ($userId)
            {
                $query->where('customer_id',$userId);
            });
        }
    }
    public function scopeByDivision($query, $divisionId)
    {
        if ($divisionId === 'External') {
            return $query->whereHas('quote', function($q) {
                $q->whereNull('division_budget_id');
            });
        } elseif ($divisionId) {
            return $query->whereHas('quote.divisionBudget', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }
        return $query;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class ReportProject extends Model
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
    
    public function userCreate()
    {
        return $this->belongsTo(User::class,'user_created_id','id');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function reportProjectDetail()
    {
        return $this->hasMany(ReportProjectDetail::class)->orderBy('order');
    }

    public function reportedDetails()
    {
        return $this->reportProjectDetail()->byReport();
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('userCreate', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }
    public function scopeByDivision($query, $divisionId)
    {
        if ($divisionId === 'External') {
            return $query->whereHas('project.workOrder.quote', function($q) {
                $q->whereNull('division_budget_id');
            });
        } elseif ($divisionId) {
            return $query->whereHas('project.workOrder.quote.divisionBudget', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }
        return $query;
    }
}
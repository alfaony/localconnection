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
use App\Schemas\ParamSchema;

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
        if ($this->attributes['title'] ?? null !== $value) {
            $this->attributes['title'] = $value;
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        } else {
            $this->attributes['title'] = $value;
        }
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

    public function bast()
    {
        return $this->hasOne(Bast::class);
    }

    public function dailyTasks()
    {
        return $this->hasMany(DailyTask::class, 'project_id','id');
    }

    public function getStatusProjectAttribute()
    {
        $statusReport = false;
        $progressTask = false;
        $expiredProject = false;
        if($this->reportProject)
        {
            $statusReport = true;
        }

        if($this->progress_task == ParamSchema::PERCENTAGE)
        {
            $progressTask = true;
        }
        
        // Cek jika selisih tahun (hanya tahun, bukan tanggal lengkap) >= 2
        if ((now()->year - $this->created_at->year >= 2) || now()->year - Carbon::parse($this->end_date)->year >= 2) {
            $expiredProject = true;
        }

        if(($statusReport && $progressTask) || $expiredProject)
        {
            return ParamSchema::CLOSE;
        }
        {
            return ParamSchema::OPEN;
        }
    }

    // Mutator untuk menghitung progress
    public function getProgressTaskAttribute()
    {
        // Menghitung total DailyTask
        $totalTasks  =  $this->dailyTasks()->count();
        
        // Menghitung DailyTask yang berstatus 'DONE'
        $doneTasks = $this->dailyTasks()->whereHas('taskStatus', function ($query) {
            $query->where('name', ParamSchema::COMPLATE);
        })->count();

        // Jika tidak ada task, progress adalah 0
        if ($totalTasks === 0) {
            return 0;
        }

        // Menghitung persentase progress
        return round(($doneTasks / $totalTasks) * 100);
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
        if ($this->start_date && $this->end_date) {
            $now = Carbon::now();
            $start_date = Carbon::parse($this->start_date);
            $end_date = Carbon::parse($this->end_date);

            // Jika tanggal sekarang sebelum start_date, progress adalah 0%
            if ($now->lessThan($start_date)) {
                return 0;
            }

            // Jika tanggal sekarang melewati end_date, progress adalah 100%
            if ($now->greaterThanOrEqualTo($end_date)) {
                return 100;
            }

            // Hitung progress untuk tanggal yang berada di antara start_date dan end_date
            $totalDuration = $start_date->diffInSeconds($end_date);
            $elapsedDuration = $start_date->diffInSeconds($now);

            return intval(($elapsedDuration / $totalDuration) * 100);
        }

        return 0;
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

    public function meetings()
    {
        return $this->hasMany(Meeting::class)->withTrashed();
    }

    public function getMeetingsJsonAttribute()
    {
        if (!$this->relationLoaded('meetings')) {
            return []; // Pastikan relasi sudah di-load
        }

        return $this->meetings->map(function ($meeting) {
            return [
                'id' => $meeting->id,
                'meeting_name' => $meeting->meeting_name,
                'participants' => $meeting->relationLoaded('participants')
                    ? $meeting->participantRelasion->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                        ];
                    })->values()->all()
                    : []
            ];
        })->values()->all();
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

    public function scopeByRole($query, $search = false)
    {
        if((Auth::user()->role->name == RoleSchema::STAFF || Auth::user()->role->name == RoleSchema::PM) && !$search)
        {
            return $query->where('user_id', Auth::user()->id);
        }
        {
            return $query->byCompany(Auth::user()->company_id);
        }
    }
    public function scopeByDivision($query, $divisionId)
    {
        if ($divisionId) {
            return $query->whereHas('workOrder.quote.divisionBudget', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }
    }
}

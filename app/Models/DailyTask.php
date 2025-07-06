<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

use App\Schemas\ParamSchema;

class DailyTask extends Model
{
    use HasFactory, SoftDeletes;

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

        static::deleting(function ($dailytask) {
            // Cascade delete child tasks)
            foreach ($dailytask->children as $child) {
                $child->delete();
            }
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

    public function assign()
    {
        return $this->belongsTo(User::class,'assignment_user_id')->withTrashed();
    }
        
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class)->withTrashed();

    }

    public function category()
    {
        return $this->belongsTo(DailyTaskCategory::class,'daily_task_category_id')->withTrashed();
    }

    public function type()
    {
        return $this->belongsTo(DailyTaskType::class,'daily_task_type_id')->withTrashed();
    }

    public function head()
    {
        return $this->belongsTo(DailyTask::class,'child_daily_task_id')->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(DailyTask::class, 'child_daily_task_id');
    }
    
    public function media()
    {
        return $this->hasMany(DailyTaskMedia::class)->where('status', ParamSchema::FILEREPORT);
    }

    public function taskMedia()
    {
        return $this->hasMany(DailyTaskMedia::class)->where('status', ParamSchema::FILETASK);
    }

    public function extend()
    {
        return $this->hasMany(DailyTaskExtend::class);
    }

    public function message()
    {
        return $this->hasMany(DailyTaskMessage::class)->orderBy('created_at', 'asc');
    }
    
    public function project()
    {
        return $this->belongsTo(DailyTaskProject::class, 'daily_task_project_id')->withTrashed();
    }

    public function dataProject()
    {
        return $this->belongsTo(Project::class,'project_id')->withTrashed();
    }
    
    public function customFieldValues()
    {
        return $this->hasMany(DailyTaskCustomFieldValue::class);
    }

    public function objective()
    {
        return $this->belongsTo(Objective::class)->withTrashed();
    }
    public function keyResults()
    {
        return $this->belongsToMany(ObjectiveKeyResult::class);
    }
    public function statusRecords()
    {
        return $this->hasMany(DailyTaskStatusRecord::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->withTrashed();
    }

    public function divisionQuotaLock()
    {
        return $this->belongsTo(DivisionQuotaLock::class)->withTrashed();
    }

    public function recurringRule()
    {
        return $this->belongsTo(RecurringRule::class)->withTrashed();
    }

    public function momTask()
    {
        return $this->hasOne(MomTask::class, 'daily_task_id','id');
    }

    public function getDateRangeSubmitAttribute()
    {
        if ($this->submit && $this->start_date) {
            $startDate = Carbon::parse($this->start_date);
            $submitDate = Carbon::parse($this->submit);
            $endDate = Carbon::parse($this->end_date);

            if ($submitDate < $startDate) {
                $days = $startDate->diffInDays($submitDate);
                return "Kurang {$days} Hari";
            } elseif ($submitDate <= $endDate) {
                $days = $startDate->diffInDays($submitDate) + 1;
                return "{$days} Hari";
            } else {
                $days = $submitDate->diffInDays($endDate);
                return "Terlambat {$days} Hari";
            }
        }
        
        return "Tanggal tidak lengkap";
    }
    
    public function isOverdue()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $today = Carbon::today();

        return ($this->taskStatus->name == \App\Schemas\ParamSchema::DOING || $this->taskStatus->name == \App\Schemas\ParamSchema::INREVIEW || $this->taskStatus->name == \App\Schemas\ParamSchema::TODO ) && $today->gt($endDate);
    }

    public function getNameShowAttribute()
    {
        $latestExtend = $this->extend()->latest()->first();
        if ($latestExtend) {
            return "Extend: {$latestExtend->extend} {$this->name}";
        }
        return $this->name;
    }

    public function getDateShowAttribute()
    {
        // Atur lokal ke bahasa Indonesia
        if($this->start_date && $this->end_date)
        {
            Carbon::setLocale('id');
    
            $startDate = Carbon::parse($this->start_date);
            $endDate = Carbon::parse($this->end_date);
            $now = Carbon::now();
    
            // Fungsi untuk menerjemahkan bulan
            $translateMonth = function ($date) {
                $months = [
                    'January' => 'Januari',
                    'February' => 'Februari',
                    'March' => 'Maret',
                    'April' => 'April',
                    'May' => 'Mei',
                    'June' => 'Juni',
                    'July' => 'Juli',
                    'August' => 'Agustus',
                    'September' => 'September',
                    'October' => 'Oktober',
                    'November' => 'November',
                    'December' => 'Desember',
                ];
                return $months[$date->format('F')] ?? $date->format('F');
            };
    
            if ($startDate->isSameDay($endDate)) {
                if ($startDate->isToday()) {
                    return 'Hari Ini';
                } elseif ($startDate->isTomorrow()) {
                    return 'Besok';
                } elseif ($startDate->isSameWeek($now)) {
                    return $startDate->translatedFormat('l');
                } else {
                    return $startDate->format('d') . ' ' . $translateMonth($startDate);
                }
            } else {
                $startStr = $startDate->isToday() ? 'Hari Ini' : ($startDate->isTomorrow() ? 'Besok' : $startDate->format('d') . ' ' . $translateMonth($startDate));
                $endStr = $endDate->isToday() ? 'Hari Ini' : ($endDate->isTomorrow() ? 'Besok' : $endDate->format('d') . ' ' . $translateMonth($endDate));
    
                if ($startDate->year !== $endDate->year) {
                    $startStr .= ' ' . $startDate->format('Y');
                    $endStr .= ' ' . $endDate->format('Y');
                } elseif ($startDate->month !== $endDate->month) {
                    $startStr .= ' ' . $startDate->format('Y');
                }
    
                if ($startDate->isSameWeek($now) && $endDate->isSameWeek($now)) {
                    if ($endDate->isToday()) {
                        return $startStr . ' - Hari Ini';
                    } elseif ($endDate->isTomorrow()) {
                        return $startStr . ' - Besok';
                    }
                }
                return $startStr . ' - ' . $endStr;
            }
        }else
        {
            return "-";
        }
    }

    public function getLastCompleteDateAttribute()
    {
        // Cari status yang bernama 'complete' dan ambil yang terakhir
        $completeRecord = $this->statusRecords()
            ->whereHas('taskStatus', function($query) {
                $query->where('name', ParamSchema::COMPLATE);
            })
            ->orderBy('id', 'desc')
            ->first();

        // Jika ada, kembalikan tanggalnya; jika tidak, kembalikan null
        return $completeRecord ? $completeRecord->created_at : null;
    }

    public function scopeByCompany($query,$companyId)
    {
        
        if($companyId)
        {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereHas('user', function ($query) use ($companyIds) 
            {
                $query->whereIn('company_id', $companyIds);
            })
            ->orWhere(function ($query) use ($companyIds) {
                $query->where('assignment_user_id', auth()->user()->id);
            });
        }
    }

    
    public function scopeUserTasks($query, $userId)
    {
        return $query->where(function($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->orWhere('assignment_user_id', $userId);
                })
                ;
    }

    public function scopeByDateRange($query, $start_date, $end_date)
    {
        if($start_date && $end_date)
        {
            $query->where(function ($query) use ($start_date, $end_date) {
                $query->whereDate('start_date', '>=', $start_date)
                    ->whereDate('end_date', '<=', $end_date);
            })
            ->orWhere(function ($query) use ($start_date, $end_date) {
                $query->orWhereDate('start_date', '>=', $start_date)->whereDate('start_date', '<=', $end_date);
            })
            ->orWhere(function ($query) use ($start_date, $end_date) {
                $query->orWhereDate('end_date', '<=', $end_date)->whereDate('end_date', '>=', $start_date);
            })
            ;
        }
    }

    private function translateMonth($month)
    {
        $months = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        return $months[$month] ?? $month;
    }
}
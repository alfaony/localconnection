<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Schemas\ParamSchema;
use App\Traits\AwardsXp;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MomTask extends Model
{
    use HasFactory, SoftDeletes, AwardsXp;

    public $incrementing = false;

    protected $fillable = [
        'agenda_id',
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'attachment',
        'task_status_id',
        'external_email',
        'external_note',
        'token',
        'daily_task_id',
        'reject_reason',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->user_id && $model->external_email && !$model->token) {
                $model->token = (string) Str::uuid();
            }
        });
    }

    public function agenda()
    {
        return $this->belongsTo(MomAgenda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'task_status_id');
    }

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class)->withTrashed();
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class)->withTrashed();
    }
    

    public function getIsInternalAttribute()
    {
        return !is_null($this->user_id);
    }

    public function getIsExternalAttribute()
    {
        return !is_null($this->external_email);
    }


    public function isOverdue()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        $today = Carbon::today();

        return ($this->taskStatus->name == \App\Schemas\ParamSchema::DOING || $this->taskStatus->name == \App\Schemas\ParamSchema::INREVIEW || $this->taskStatus->name == \App\Schemas\ParamSchema::TODO ) && $today->gt($endDate);
        return false;
    }
    
    public function isAction()
    {
        return $this->taskStatus->name == \App\Schemas\ParamSchema::TODO || $this->taskStatus->name == \App\Schemas\ParamSchema::NOTCOMPLATE || $this->taskStatus->name == \App\Schemas\ParamSchema::BACKLOG ? true : false;
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
}

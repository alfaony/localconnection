<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MomTask extends Model
{
    use SoftDeletes;

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
        'token',
        'daily_task_id',
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

    public function getIsInternalAttribute()
    {
        return !is_null($this->user_id);
    }

    public function getIsExternalAttribute()
    {
        return !is_null($this->external_email);
    }
}

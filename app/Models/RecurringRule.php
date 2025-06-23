<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringRule extends Model
{
    protected $fillable = [
        'daily_task_id', 'frequency', 'interval', 'by_day',
        'by_month_day', 'by_month', 'count', 'until', 'start_date',
    ];

    protected $casts = [
        'by_day' => 'array',
        'by_month_day' => 'array',
        'by_month' => 'array',
        'start_date' => 'date',
        'until' => 'date',
    ];

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class)->withTrashed();
    }
}

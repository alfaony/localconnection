<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

use App\Schemas\ParamSchema;

class DailyTaskStatusRecord extends Model
{
    use HasFactory;

    protected $fillable = ['daily_task_id', 'task_status_id', 'date'];

    public function dailyTask()
    {
        return $this->belongsTo(DailyTask::class);
    }

    public function taskStatus()
    {
        return $this->belongsTo(TaskStatus::class);
    }

}

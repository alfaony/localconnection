<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Schemas\ParamSchema;

class Mom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'company_id',
        'user_id',
        'meeting_id',
        'project_id',
        'mom_date',
        'notes',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class)->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function agendas()
    {
        return $this->hasMany(MomAgenda::class);
    }

    public function tasks()
    {
        return $this->hasManyThrough(MomTask::class, MomAgenda::class, 'mom_id', 'agenda_id');
    }

    /**
     * Check apakah mom bisa dihapus (tidak punya task sama sekali)
     */
    public function getIsDeleteAttribute(): bool
    {
        return $this->tasks()->count() === 0;
    }

    /**
     * Total semua task
     */
    public function getTotalTasksAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Persentase task dengan status Completed
     */
    public function isDelete()
    {
        return intval($this->progress) === 0;
    }

    public function getProgressAttribute()
    {
        $tasks = $this->tasks; // from hasManyThrough MomTask

        if ($tasks->isEmpty()) {
            return 0;
        }

        // Bobot status
        $statusWeights = [
            'todo' => 0,
            'doing' => 25,
            'in review' => 65,
            'complete' => 100,
            'not complete' => 0,
        ];

        $totalScore = 0;
        $taskCount = 0;

        foreach ($tasks as $task) 
        {
            $status = strtolower($task->status->name ?? 'todo');
            $weight = $statusWeights[$status] ?? 0;

            $totalScore += $weight;
            $taskCount++;
        }

        return round($totalScore / $taskCount, 2); // return as percentage
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

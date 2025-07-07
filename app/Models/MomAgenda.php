<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Schemas\ParamSchema;

class MomAgenda extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mom_id',
        'title',
        'discussion_notes',
        'attachment',
    ];

    public function mom()
    {
        return $this->belongsTo(Mom::class);
    }

    public function tasks()
    {
        return $this->hasMany(MomTask::class, 'agenda_id');
    }

    public function getIsDeleteAttribute()
    {
        $allowedStatus = [ParamSchema::TODO, ParamSchema::BACKLOG];

        // Jika semua task memiliki status yang termasuk dalam list di atas
        $tasks = $this->tasks;

        if ($tasks->isEmpty()) {
            return true; // tidak ada task dianggap aman untuk delete
        }


        return $tasks->every(function ($task) use ($allowedStatus) {
            // dd($allowedStatus);
            return in_array(strtolower($task->status->name ?? ''), array_map('strtolower', $allowedStatus));
        });
    }


}

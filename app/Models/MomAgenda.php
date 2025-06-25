<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}

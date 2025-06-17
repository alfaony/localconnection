<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingAgenda extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'meeting_id',
        'project_id',
        'title',
        'discussion_notes',
        'attachment',
    ];

    /**
     * Relasi ke model Meeting
     * Satu agenda milik satu meeting
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class)->withTrashed();
    }

    /**
     * Relasi ke model Project
     * Satu agenda terkait satu project
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id')->withTrashed();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingRecurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'recurring_type',
        'recurring_daily_days',
        'recurring_monthly_date',
        'recurring_yearly_month',
        'recurring_yearly_date',
        'is_active',
    ];

    protected $casts = [
        'recurring_daily_days' => 'array',
        'is_active' => 'boolean',
    ];

    public function templateMeeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function generatedMeetings()
    {
        return $this->hasMany(Meeting::class, 'meeting_recurrence_id');
    }
}

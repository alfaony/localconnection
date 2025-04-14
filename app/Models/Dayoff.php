<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dayoff extends Model
{
    protected $fillable = [
        'user_id', 'dayoff_type_id', 'date_start', 'date_end', 'reason', 'file',
        'approval_hr_user_id', 'approval_finance_user_id',
        'approved_hr_at', 'approved_finance_at', 'rejected_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(DayoffType::class, 'dayoff_type_id')->withTrashed();
    }

    public function approvalHR()
    {
        return $this->belongsTo(User::class, 'approval_hr_user_id')->withTrashed();
    }

    public function approvalFinance()
    {
        return $this->belongsTo(User::class, 'approval_finance_user_id')->withTrashed();
    }
}

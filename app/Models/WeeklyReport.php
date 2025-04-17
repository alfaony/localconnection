<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'division_id',
        'date',
        'year',
        'week',
        'key_activities',
        'problems',
        'targets',
        'number_of_customers',
        'number_of_users',
        'number_of_products',
        'number_of_projects',
        'number_of_homepasses',
        'number_of_leads',
        'number_of_views',
        'number_of_profit',
    ];

    protected $casts = [
        'date' => 'date',
        'year' => 'integer',
        'week' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
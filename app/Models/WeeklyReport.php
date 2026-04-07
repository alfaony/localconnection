<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\AwardsXp;

class WeeklyReport extends Model
{
    use HasFactory, SoftDeletes, AwardsXp;

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
        'file',
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
    
    public function getIsEditableAttribute()
    {
        return $this->year == now()->year && $this->week == now()->isoWeek();
    }
    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }
}
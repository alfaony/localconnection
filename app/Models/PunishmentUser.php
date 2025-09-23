<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PunishmentUser extends Model
{
    use SoftDeletes;

    protected $table = 'punishment_users';

    protected $fillable = [
        'user_id',
        'dailytask_id',
        'point',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dailytask(): BelongsTo
    {
        return $this->belongsTo(DailyTask::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function($q) use ($search) {
            $q->where('name', 'like', '%'.$search.'%')
              ->orWhere('email', 'like', '%'.$search.'%');
        });
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        }
        return $query;
    }

    public function scopeByCompany($query, $company_id)
    {
        $company_ids = auth()->user()->accessibleCompanies->pluck('id')->push($company_id)->unique();
        return $query->whereHas('user', function($q) use ($company_ids) {
            $q->whereIn('company_id', $company_ids);
        });
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

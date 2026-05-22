<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportLink extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'company_id', 'name', 'date', 'link', 'description'];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function images()
    {
        return $this->hasMany(ReportLinkImage::class)->orderBy('order');
    }

    public function scopeByCompany($query, $companyId)
    {
        if ($companyId) {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereIn('company_id', $companyIds);
        }
    }
}

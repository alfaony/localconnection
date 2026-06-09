<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

class Promo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'type',
        'value',
        'register_date',
        'start_date',
        'end_date',
        'quota',
        'is_active',
    ];

    protected $casts = [
        'register_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function isAction()
    {
        $access = true;
        if ($this->start_date && $this->end_date && now()->format('Y-m-d') >= $this->start_date)
        {
            $access = false;
        }

        if($this->internetCustomers->count() > 0)
        {
            $access = false;
        }
        
        return $access;
    }

    public function isActiveTrigger()
    {
        $access = true;
        if ($this->start_date && $this->end_date && now()->format('Y-m-d') < $this->start_date && now()->format('Y-m-d') > $this->end_date)
        {
            $access = false;
        }

        return $access;
    }

    // RELATION: Ke company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function packageInternets()
    {
        return $this->belongsToMany(InternetPackage::class);
    }

    public function internetCustomers()
    {
        return $this->hasMany(InternetCustomer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    
    public function scopeByCompany($query,$companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        if($companyIds)
        {
            return $query->whereIn("company_id",$companyIds);
        }
    }
}

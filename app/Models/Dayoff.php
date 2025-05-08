<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Helpers\Access;
use Illuminate\Support\Facades\Auth;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

class Dayoff extends Model
{
    protected $fillable = [
        'user_id', 'dayoff_type_id', 'date_start', 'date_end', 'reason', 'file',
        'approval_hr_user_id', 'approval_finance_user_id',
        'approved_hr_at', 'approved_finance_at', 'rejected_at','reason_reject',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function type()
    {
        return $this->belongsTo(DayoffType::class, 'dayoff_type_id');
    }

    public function approvalHR()
    {
        return $this->belongsTo(User::class, 'approval_hr_user_id')->withTrashed();
    }

    public function approvalFinance()
    {
        return $this->belongsTo(User::class, 'approval_finance_user_id')->withTrashed();
    }

    public function durationInDays()
    {
        return Carbon::parse($this->date_start)->diffInDays(Carbon::parse($this->date_end)) + 1;
    }

    public function getPermissionChangedAttribute()
    {
        $permiision = true;

        if ($this->approved_hr_at || $this->approved_finance_at) {
            $permiision = false;
        }
        
        if ($this->rejected_at) 
        {
            $permiision = true;
        }

        if($this->user_id != Auth::user()->id)
        {
            $permiision = false;
        }

        return $permiision;
    }
    public function getStatusTextAttribute()
    {
        if ($this->rejected_at) {
            return 'Ditolak';
        } elseif ($this->approved_hr_at && $this->approved_finance_at) {
            return 'Disetujui';
        } else {
            return 'Menunggu';
        }
    }
    public function getStatusBadgeAttribute()
    {
        if ($this->rejected_at) {
            return '<span class="badge badge-pill badge-danger"><i class="fas fa-times-circle mr-1"></i>Ditolak</span>';
        } elseif ($this->approved_hr_at && $this->approved_finance_at) {
            return '<span class="badge badge-pill badge-success"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>';
        } else {
            return '<span class="badge badge-pill badge-warning"><i class="fas fa-clock mr-1"></i>Menunggu</span>';
        }
    }

    public function scopeByCompany($query,$companyId)
    {
        $approve = false;

        if(Access::can('hrApprovement', 'dayoffs') ||  Access::can('financeApprovement', 'dayoffs') || Auth::user()->role->name == RoleSchema::ROOT)
        {
            $approve = true;
        }

        if($companyId && $approve)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }else
        {
            return $query->where('user_id', Auth::user()->id);
        }
    }

    public function scopeByCompanyJob($query,$companyId, $role, $hrApprovement, $financeApprovement)
    {
        $approve = false;

        if($hrApprovement || $financeApprovement || $role->name == RoleSchema::ROOT)
        {
            $approve = true;
        }

        if($companyId && $approve)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }else
        {
            return $query->where('user_id', Auth::user()->id);
        }
    }

}

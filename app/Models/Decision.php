<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

use App\Schemas\RoleSchema;

class Decision extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'question',
        'answer',
        'user_create_id',
        'user_responsible_id',
        'user_accountable_id',
        'user_consult_id',
        'is_approve',
        'trust_score',
        'execution_score'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_create_id')->withTrashed();
    }

    public function userResponsible()
    {
        return $this->belongsTo(User::class,'user_responsible_id')->withTrashed();
    }

    public function userAccount()
    {
        return $this->belongsTo(User::class,'user_accountable_id')->withTrashed();
    }


    public function userConsult()
    {
        return $this->belongsTo(User::class,'user_consult_id')->withTrashed();
    }



    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            if(Auth::user()->role->name == RoleSchema::ADMIN)
            {
                return $query->whereHas('user', function ($query) use ($companyId)
                {
                    $query->where('company_id', $companyId);
                });
            }else
            {
                return $query->where(function ($q) use ($companyId) {
                    $q->where('user_create_id', Auth::user()->id)
                      ->orWhereJsonContains('user_sharing', Auth::user()->id);
                });
            }
        }
    }
}

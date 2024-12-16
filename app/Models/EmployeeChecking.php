<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Schemas\RoleSchema;
use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;
class EmployeeChecking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'division_id',
        'scheduled_time',
        'scheduled_timeout',
        'checkin_start_time',
        'is_active',
        'is_permission',
        'is_completed',
        'is_dayoff',
        'photo_path',
        'score',
        'location_latitude',
        'location_longitude'
    ];

    /**
     * Relasi ke model User
     * Satu EmployeeChecking hanya terkait dengan satu User
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Relasi ke model Division
     * Satu EmployeeChecking hanya terkait dengan satu Division
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Belongs To Pass
     */
    public function passChecking()
    {
        return $this->belongsTo(PassChecking::class)->withTrashed();
    }
    /**
     * is today
     */

     public function isToday()
     {
         return $this->created_at->toDateString() == Carbon::today()->toDateString();
     }

     
     /**
      * Dayoff
      */

      public function isDayoff()
      {
        return $this->is_dayoff;
      }

      /**
       * Sick
       */

       public function isSick()
       {
        return $this->is_permission;
       }
    /**
     * Scope untuk memfilter jadwal yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk memfilter jadwal yang belum selesai
     */
    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * 
     * FIlter by Role
     */
    public function scopeByRole($query, $userId = null)
    {
        if(Auth::user()->role->name == RoleSchema::ROOT || Auth::user()->role->name == RoleSchema::ADMIN || Auth::user()->role->name == RoleSchema::DIRECTOR || Auth::user()->role->name == RoleSchema::HR || Auth::user()->role->name == RoleSchema::FINANCE)
        {
            return $query->where('user_id', $userId);
        }
        else
        {
            return $query->where('user_id',Auth::user()->id);
        }
    }

    /**
     * 
     * By Company
     */
    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT) 
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value;
        if (empty($this->slug)) {
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        }
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'LIKE', "$slug%")->withTrashed()->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'custom_rest_times' => 'array', // This will automatically decode JSON into an array   
        'ip_addresses' => 'array',   
    ];

    protected $appends = ['point_checkin', 'today_percentage', 'point_percentage'];

    public function role()
    {
        return $this->belongsTo(Role::class)->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function taskAssigns()
    {
        return $this->hasMany(TaskAssign::class,'user_assign_id');
    }

    public function dailyTaskAssigns()
    {
        return $this->hasMany(DailyTask::class,'assignment_user_id');
    }

    public function settingCompany()
    {
        return $this->hasMany(SettingCompany::class);
    }

    public function status()
    {
        return $this->hasMany(UserStatus::class);
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approvement_user_id');
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class);
    }

    public function getFirstDivisionAttribute()
    {
        return $this->divisions->first();
    }
    
    public function userPosition()
    {
        return $this->hasMany(UserPosition::class);
    }

    public function employeeCheckings()
    {
        return $this->hasMany(EmployeeChecking::class);
    }
    public function getLastPositionAttribute()
    {
        return $this->userPosition()
        ? $this->userPosition()->whereNull('end_date')->orderBy('created_at', 'desc')->first()
        : null;
    }
    
    public function getLastPositionNowAttribute()
    {
        return $this->userPosition()
        ? $this->userPosition()->orderBy('created_at', 'desc')->first()
        : null;
    }

    public function getFirstPositionAttribute()
    {
        return $this->userPosition() ? $this->userPosition()
            ->whereHas('letterSubmission', function ($query) {
                $query->whereHas('letterType', function ($query) {
                    $query->where('template', ParamSchema::TEMPLATEPERJANJIANKERJA);
                });
            })
            ->latest('created_at')  // Assuming you want the latest based on 'created_at'/
            ->first() : "";
    }
    public function getAchievementDecodeAttribute()
    {
        return json_decode($this->achievement, true) ?? [];
    }
    public function getFailureDecodeAttribute($value)
    {
        return json_decode($this->failure, true) ?? [];
    }
    public function salary()
    {
        return $this->hasMany(UserSalary::class);
    }

    public function kye()
    {
        return $this->hasOne(Kye::class);
    }
    public function getLastSalaryAttribute()
    {
        return $this->salary()->latest()->first();
    }

    public function showName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->kye) {
                    switch ($this->kye->approval_status) {
                        case 'approved':
                            return $this->name . ' <i class="fa fa-check-circle text-primary"></i>';
                        case 'pending':
                            return $this->name . ' <i class="fa fa-clock text-warning"></i>';
                        case 'rejected':
                            return $this->name . ' <i class="fa fa-times-circle text-white"></i>';
                        default:
                            return $this->name;
                    }
                }

                return $this->name; // Jika tidak ada KYE
            }
        );
    }
    public function getPointCheckinAttribute()
    {
        $totalCheckins = $this->total_successful_checkins ?? 0;
        $targetCheckins = $this->total_days * ParamSchema::TARGET_CHECKIN;

        $pointPercentage = $targetCheckins ? ($totalCheckins / $targetCheckins) * 100 : 0;

        return "{$totalCheckins} (" . number_format($pointPercentage, 0) . "%)";
    }

    public function getTodayPercentageAttribute()
    {
        $totalToday = $this->total_checkin_today ?? 0;

        $todayPercentage = $totalToday ? ($totalToday / 10) * 100 : 0;

        return "{$totalToday} (" . number_format($todayPercentage, 0) . "%)";
    }

    public function getPointPercentageAttribute()
    {
        $totalCheckins = $this->total_successful_checkins ?? 0;
        $targetCheckins = $this->total_days * ParamSchema::TARGET_CHECKIN;

        return $targetCheckins ? ($totalCheckins / $targetCheckins) * 100 : 0;
    }

    // Scope query untuk mendapatkan data user dengan perhitungan
    public function scopeWithCheckinCounts($query, $userId = null, $start = null, $end = null, $today = null)
    {
        return $query->select('users.*')
            ->byCompany(auth()->user()->company_id)
            ->when($userId, function ($query) use ($userId) {
                $query->where('id', $userId);
            })
            ->withCount([
                'employeeCheckings as total_checkin_today' => function ($query) use ($today) {
                    $query->where('is_active', false)
                          ->where('is_completed', true)
                          ->where('is_dayoff', false)
                          ->where('is_permission', false)
                          ->whereDate('created_at', $today);
                },
                'employeeCheckings as total_successful_checkins' => function ($query) use ($start, $end) {
                    $query->where('is_active', false)
                          ->where('is_completed', true)
                          ->where('is_dayoff', false)
                          ->where('is_permission', false);
                    if ($start && $end) {
                        $query->whereBetween('created_at', [$start, $end]);
                    }
                },
                'employeeCheckings as total_days' => function ($query) use ($start, $end) {
                    $query->select(DB::raw('COUNT(DISTINCT DATE(created_at))'))
                          ->where('is_dayoff', false)
                          ->where('is_permission', false);
                    if ($start && $end) {
                        $query->whereBetween('created_at', [$start, $end]);
                    }
                }
            ]);
    }
    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            return $query->where("company_id",$companyId);
        }
    }

    // Is Or Not
    public function isShow()
     {
        if(Auth::user()->role->name == RoleSchema::ROOT || Auth::user()->role->name == RoleSchema::ADMIN || Auth::user()->role->name == RoleSchema::DIRECTOR || Auth::user()->role->name == RoleSchema::HR || Auth::user()->role->name == RoleSchema::FINANCE)
        {
            return true;      
        }else
        {
            if($this->id == Auth::user()->id)
            {
                return true;
            }else
            {
                return false;
            }
        }
        

     }
    public function scopeByRole($query,$role)
    {
        if($role)
        {
            return $query->whereHas('role', function ($query) use ($role)
            {
                $query->where('name', $role);
            });
        }
    }

    public function scopeByRoleSearch($query)
    {
        if(Auth::user()->role->name == RoleSchema::ROOT || Auth::user()->role->name == RoleSchema::ADMIN || Auth::user()->role->name == RoleSchema::DIRECTOR)
        {
            return $query->where('company_id', Auth::user()->company_id);
        }
        else
        {
            return $query->where('id', Auth::user()->id);
        }
    }

    public function scopeByRoleList($query,$userId)
    {
        // if(Auth::user()->role->name == RoleSchema::ROOT || Auth::user()->role->name == RoleSchema::ADMIN || Auth::user()->role->name == RoleSchema::DIRECTOR)
        // {
            if($userId)
            {
                return $query->where('id', $userId);
            }else
            {
                $query->byCompany(Auth::user()->company_id);
            }
        // }
        // else
        // {
        //     return $query->where('id', Auth::user()->id);
        // }
    }
}

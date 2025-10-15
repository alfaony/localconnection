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

use Carbon\Carbon;

use App\Helpers\Access;

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
        'dayoff_active' => 'boolean',
        'wfo_working_days' => 'array',  // ← Tambahkan ini
    ];

    // protected $appends = ['point_checkin', 'today_percentage', 'point_percentage'];

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

    public function dailyTasks()
    {
        return $this->hasMany(DailyTask::class, 'user_id'); // pembuat
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
        return $this->belongsToMany(Division::class)
            ->withPivot('weekly_report_required');
    }

    public function assignedRequests()
    {
        return $this->hasMany(ItemRequest::class, 'assigned_pic_id');
    }

    public function request()
    {
        return $this->hasMany(ItemRequest::class, 'user_id');
    }

    public function usedItems()
    {
        return $this->hasMany(UsedItem::class);
    }
    
    public function getFirstDivisionAttribute()
    {
        return $this->divisions->first();
    }
    
    public function getBackGroundVerifiedAttribute()
    {
        $existingAchievements = json_decode($this->achievement, true) ?? [];
        $existingFailures = json_decode($this->failure, true) ?? [];

        return !empty($this->background)
            && !empty($this->experience)
            && !empty($this->skill)
            && !empty($existingAchievements)
            && !empty($existingFailures);
    }
    public function userPosition()
    {
        return $this->hasMany(UserPosition::class);
    }

    public function employeeCheckings()
    {
        return $this->hasMany(EmployeeChecking::class);
    }

    public function dayoffQuotas()
    {
        return $this->hasMany(DayoffQuota::class);
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

    public function shouldWorkToday()
    {
        if (!$this->wfo_check_in || is_null($this->wfo_working_days)) {
            return false;
        }

        $today = now()->format('l'); // Get day name: Monday, Tuesday, etc.
        
        return $this->wfo_working_days[$today] ?? false;
    }

    /**
     * Check if user should work on a specific date
     */
    public function shouldWorkOnDate($date)
    {
        if (!$this->wfo_check_in || is_null($this->wfo_working_days)) {
            return false;
        }

        $dayName = \Carbon\Carbon::parse($date)->format('l');
        
        return $this->wfo_working_days[$dayName] ?? false;
    }

    public function salary()
    {
        return $this->hasMany(UserSalary::class);
    }

    public function dayoffs()
    {
        return $this->hasMany(Dayoff::class);
    }

    public function kye()
    {
        return $this->hasOne(Kye::class);
    }
    
    public function accessibleCompanies()
    {
        return $this->belongsToMany(Company::class, 'company_user_access');
    }
    public function getLastSalaryAttribute()
    {
        return $this->salary()->latest()->first();
    }

    public function remainingDayoffQuotas()
    {
        if (! $this->dayoff_active) {
            return collect();
        }

        return $this->dayoffQuotas
            ->filter(fn($quota) => $quota->type?->is_limited) // hanya quota terbatas
            ->map(function ($quota) {
                $total = $quota->quota ?? $quota->type->default_quota;
                $used = $quota->used ?? 0;
                return [
                    'type' => $quota->type->name,
                    'remaining' => $total - $used,
                ];
            });
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

        return ($targetCheckins ? number_format(($totalCheckins / $targetCheckins) * 100, 0) : 0);
    }

    public function isSick(): bool
    {
        $today = Carbon::today();

        return $this->dayoffs()
            ->whereHas('type', fn($q) => $q->where('permission_required', true))
            ->whereDate('date_start', '<=', $today)
            ->whereDate('date_end', '>=', $today)
            ->whereNull('rejected_at')
            ->whereNotNull('approved_hr_at')
            ->whereNotNull('approved_finance_at')
            ->exists();
    }

    public function isDayoff(): bool
    {
        $today = Carbon::today();

        return $this->dayoffs()
            ->whereHas('type', fn($q) => $q->where('permission_required','!=', true))
            ->whereDate('date_start', '<=', $today)
            ->whereDate('date_end', '>=', $today)
            ->whereNull('rejected_at')
            ->whereNotNull('approved_hr_at')
            ->whereNotNull('approved_finance_at')
            ->exists();
    }

    public function isSearchDayoff(): bool
    {
        return (Access::can('hrApprovement', 'dayoffs') ||  Access::can('financeApprovement', 'dayoffs') || Auth::user()->role->name == RoleSchema::ROOT || Auth::user()->role->name == RoleSchema::ADMIN || Auth::user()->role->name == RoleSchema::DIRECTOR);
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

    public function scopeWithCheckinCountsJob($query, $companyId = null, $userId = null, $start = null, $end = null, $today = null, $role = null)
    {
        return $query->select('users.*')
            ->byCompanyJob($companyId, $role)
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
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        if($companyIds && Auth::user()->role->name != RoleSchema::ROOT)
        {
            return $query->whereIn("company_id",$companyIds);
        }
    }

    public function scopeByCompanyJob($query,$companyId, $role)
    {
        if($companyId && $role && $role != RoleSchema::ROOT)
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
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push(Auth::user()->company_id)->unique();
            return $query->whereIn('company_id', $companyIds);
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

    // public function scopeByCompanyAccess($query,$user,$companyId, $role)
    // {
    //     if($companyId && $role && $role != RoleSchema::ROOT) 
    //     {
    //         $companyIds = $user->accessibleCompanies->pluck('id')->push($companyId)->unique();

    //         return $query->whereHas('user', function ($query) use ($companyIds) 
    //         {
    //             $query->whereIn('company_id', $companyIds);
    //         });
    //     }
    // }

    public function scopeByCompanyAccess($query, $user, $companyId, $role)
    {
        if ($companyId && $role && $role != RoleSchema::ROOT) {
            $companyIds = $user->accessibleCompanies->pluck('id')->push($companyId)->unique();

            return $query->whereIn('company_id', $companyIds);
        }

        return $query;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use Carbon\Carbon;

use App\Helpers\Access;

class User extends Authenticatable implements MustVerifyEmail
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

            // Jika email kosong tapi ada username, buat slug dari username
            if (empty($model->email) && !empty($model->username) && empty($model->slug)) {
                $model->slug = $model->createUniqueSlug($model->username);
            }
        });
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value;
        // Hanya buat slug dari email jika email tidak kosong
        if (!empty($value) && empty($this->slug)) {
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
        'username',
        'email',
        'password',
        'role_id',
        'company_id',
        'phone',
        'email_verified_at',
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

        $today = strtolower(now()->format('l')); // now()->format('l'); // Get day name: Monday, Tuesday, etc.
        return $this->wfo_working_days[$today] ?? false;
    }

    public function getPhotoIdentityAttribute($value)
    {
        if($this->id_card_image)
        {
            return Storage::url($this->id_card_image);
        }
        return null;
    }

    /**
     * Accessor for is_active - returns true if user has divisions
     */
    // public function isActive(): bool
    // {
    //    return $this->divisions()->count() > 0 ? true : false;
    // }

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

    
    public function accessibleCompanies()
    {
        return $this->belongsToMany(Company::class, 'company_user_access');
    }

    /**
     * Riwayat XP karyawan ini.
     */

    public function internetCustomerRegions()
    {
        return $this->hasMany(InternetCustomerUserRegion::class);
    }

    // Scope query untuk mendapatkan data user dengan perhitungan

    public function scopeByCompany($query,$companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        if($companyIds && Auth::user()->role->name != RoleSchema::SUPER_ADMIN)
        {
            return $query->whereIn("company_id",$companyIds);
        }
    }

    public function scopeByCompanyPublic($query,$companyId)
    {
        if($companyId)
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

    public function remainingDayoffQuotas(): array
    {
        return [];
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

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
    ];

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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approvement_user_id');
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class);
    }

    public function userPosition()
    {
        return $this->hasMany(UserPosition::class);
    }
    public function getLastPositionAttribute()
    {
        return $this->userPosition() ? $this->userPosition()->orderBy('end_date', 'desc')->first() : null ;
    }
    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->where("company_id",$companyId);
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
}

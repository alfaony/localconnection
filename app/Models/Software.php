<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\AwardsXp;

class Software extends Model
{
    use HasFactory, SoftDeletes, AwardsXp;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'softwares';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'nama',
        'slug',
        'tipe_paket',
        'description',
        'logo',
        'status',
        'pic_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
                $model->slug = $model->createUniqueSlug();
            }
        });
    }

    protected function createUniqueSlug()
    {
        $slug = Str::slug($this->nama);
        $count = static::where('slug', 'LIKE', "$slug%")->withTrashed()->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Get the company that owns the software.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the PIC (Person In Charge) of the software.
     */
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id')->withTrashed();
    }

    /**
     * Get the packages for the software.
     */
    public function packages()
    {
        return $this->hasMany(SoftwarePackage::class);
    }

    /**
     * Get the active packages for the software.
     */
    public function activePackages()
    {
        return $this->hasMany(SoftwarePackage::class)->where('status', 'active');
    }

    /**
     * Get the master accounts for the software.
     */
    public function masterAccounts()
    {
        return $this->hasMany(MasterAccount::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    /**
     * Get the active master accounts for the software.
     */
    public function activeMasterAccounts()
    {
        return $this->hasMany(MasterAccount::class)->where('status', 'active');
    }

    /**
     * Get available master accounts (with available slots).
     */
    public function availableMasterAccounts()
    {
        return $this->hasMany(MasterAccount::class)
            ->where('status', 'active')
            ->whereRaw('used_slots < max_slots');
    }

    /**
     * Scope a query to only include active software.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by company.
     */
    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }

    public function getNamaSoftwareAttribute()
    {
        return $this->nama;
    }

    // /**
    //  * Get the route key for the model.
    //  */
    // public function getRouteKeyName()
    // {
    //     return 'slug';
    // }
}
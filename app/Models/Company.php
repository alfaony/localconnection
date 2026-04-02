<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Company extends Model
{
    use HasFactory,SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string
    protected $fillable = [
        'name',
        'slug',
        'xp_config_id',
    ];

    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function setNameAttribute($value)
    {
       if ($this->name != $value || $this->slug == '') {
            $this->attributes['name'] = $value;
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        } else {
            $this->attributes['name'] = $value;
        }
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::where('slug', $slug)->withTrashed()->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function softwares()
    {
        return $this->hasMany(Software::class);
    }

    public function accessibleUsers()
    {
        return $this->belongsToMany(User::class, 'company_user_access');
    }

    /**
     * XP Config yang di-assign ke company ini.
     */
    public function xpConfig()
    {
        return $this->belongsTo(XpConfig::class);
    }

    /**
     * Cek apakah fitur XP aktif untuk company ini.
     */
    public function isXpEnabled(): bool
    {
        return $this->xpConfig !== null && $this->xpConfig->is_enabled;
    }

    public function scopeByCompany($query, $companyId){

        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('id', $companyIds);
    }
}
<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use App\Schemas\RoleSchema;

class Manager extends Model
{
    use HasFactory,SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    
    protected static function booted()
    {
        static::addGlobalScope(new RoleScope());
    }
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = $this->createUniqueSlug($value);
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::withoutGlobalScopes()->where('slug', $slug)->withTrashed()->exists()) 
        {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function job()
    {
        return $this->hasMany(Job::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeByProjectActive($query)
    {
        $today = Carbon::now();
        return $query->whereHas('project',function($a) use($today)
        {
            $a->where('start_date', '<=', $today)->where('end_date', '>=', $today);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }

    public function scopeByRole($query)
    {
        if(Auth::user()->role->name == RoleSchema::STAFF || Auth::user()->role->name == RoleSchema::PM)
        {
            return $query->where('user_id', Auth::user()->id);
        }
        {
            return $query->byCompany(Auth::user()->company_id);
        }
    }
}

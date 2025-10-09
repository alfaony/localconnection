<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;


class Zone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'warehouse_id', 'name','user_id'];
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id');
    }

    public function sensors(): BelongsToMany
    {
        return $this->belongsToMany(Sensor::class, 'sensor_zone')->withPivot('sensor_code','value','id')->whereNull('sensor_zone.deleted_at'); // Hanya ambil sensor yang aktif
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function racks()
    {
        return $this->hasMany(Rack::class);
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->whereIn('company_id', $companyIds);
            });
        }
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use App\Schemas\RoleSchema;
use Ramsey\Uuid\Uuid;
class Rack extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'zone_id', 'name', 'description'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function sensors()
    {
        return $this->belongsToMany(Sensor::class, 'rack_sensor')
            ->withPivot('sensor_code', 'value', 'id')
            ->withTimestamps()
            ->whereNull('rack_sensor.deleted_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

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

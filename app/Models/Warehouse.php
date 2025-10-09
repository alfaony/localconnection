<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

class Warehouse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['id', 'name', 'location', 'longitude', 'latitude', 'warehouse_type_id','user_id'];

    public $incrementing = false; // UUID sebagai primary key
    protected $keyType = 'string';

    // Override UUID dengan Ramsey UUID
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    // Relasi ke WarehouseType
    public function warehouseType()
    {
        return $this->belongsTo(WarehouseType::class, 'warehouse_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereHas('user', function ($query) use ($companyIds) 
            {
                $query->whereIn('company_id', $companyIds);
            });
        }
    }
}

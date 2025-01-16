<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

class Sensor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'name', 'type', 'user_id'];

    public $incrementing = false; // Gunakan UUID
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sensor) {
            $sensor->id = Uuid::uuid4()->toString();
        });
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
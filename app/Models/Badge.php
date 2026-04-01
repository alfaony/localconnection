<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class Badge extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'name',
        'image',
        'description',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }
}

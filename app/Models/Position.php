<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id'; // Primary key
    public $incrementing = false; // Karena id menggunakan UUID, nonaktifkan auto-incrementing
    protected $keyType = 'string'; // Set key type sebagai string karena UUID

    protected $fillable = [
        'id',
        'company_id',
        'name',
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
    /**
     * Scope a query to filter positions by company.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $companyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Relationship to Company model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class EmployeeXpHistory extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    // History bersifat immutable — tidak ada updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'company_id',
        'xp',
        'source_type',
        'source_id',
        'description',
    ];

    protected $casts = [
        'xp'        => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    /**
     * User pemilik XP ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Scope: filter berdasarkan user.
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter berdasarkan company.
     */
    public function scopeByCompany($query, string $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Cek apakah XP ini bernilai positif (reward).
     */
    public function isReward(): bool
    {
        return $this->xp > 0;
    }

    /**
     * Cek apakah XP ini bernilai negatif (penalty).
     */
    public function isPenalty(): bool
    {
        return $this->xp < 0;
    }
}

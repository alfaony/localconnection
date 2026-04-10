<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class ChallengeUser extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'challenge_id',
        'user_id',
        'invited_by',
        'reward_given',
        'completed_at',
        'finished_at',
    ];

    protected $casts = [
        'reward_given' => 'boolean',
        'completed_at' => 'datetime',
        'finished_at'  => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by')->withTrashed();
    }
}

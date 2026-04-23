<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Challenge extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    // ── Module type constants ──────────────────────────────────────────────
    const MODULE_TASK          = 'task';
    const MODULE_INTERNET      = 'internet';
    const MODULE_KASIR         = 'kasir';
    const MODULE_SPRINTER      = 'sprinter';
    const MODULE_MEETING       = 'meeting';
    const MODULE_DECISION      = 'decision';
    const MODULE_WEEKLY_REPORT = 'weekly_report';
    const MODULE_SCORE         = 'score';

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'reward_point',
        'reward_xp',
        'module_type',
        'target_count',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'reward_point' => 'integer',
        'reward_xp'    => 'integer',
        'target_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function challengeUsers()
    {
        return $this->hasMany(ChallengeUser::class);
    }

    public function invitedUsers()
    {
        return $this->belongsToMany(User::class, 'challenge_users', 'challenge_id', 'user_id')
                    ->withPivot('reward_given', 'completed_at', 'invited_by')
                    ->withTimestamps();
    }

    /**
     * Event yang mengandung challenge ini (opsional — challenge tidak wajib punya event).
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_challenges', 'challenge_id', 'event_id')
                    ->withTimestamps();
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }

    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now()->toDateString())
                     ->where('end_date',   '>=', now()->toDateString());
    }

    public function scopeByInvitedUser($query, $userId)
    {
        return $query->whereHas('invitedUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public static function moduleOptions(): array
    {
        return [
            self::MODULE_TASK          => 'Count Task (Selesai)',
            self::MODULE_INTERNET      => 'Count Pemasangan Internet',
            self::MODULE_KASIR         => 'Count Transaksi Kasir',
            self::MODULE_SPRINTER      => 'Count Sprinter (Ditugaskan)',
            self::MODULE_MEETING       => 'Count Meeting & MOM',
            self::MODULE_DECISION      => 'Count Keputusan (TanyaBOS)',
            self::MODULE_WEEKLY_REPORT => 'Count Laporan Manager',
            self::MODULE_SCORE         => 'Count Score (XP)',
        ];
    }

    public function moduleLabel(): string
    {
        return self::moduleOptions()[$this->module_type] ?? $this->module_type;
    }

    public function moduleIcon(): string
    {
        return [
            self::MODULE_TASK          => 'bi bi-check2-circle',
            self::MODULE_INTERNET      => 'bi bi-wifi',
            self::MODULE_KASIR         => 'bi bi-cart-check',
            self::MODULE_SPRINTER      => 'fas fa-running',
            self::MODULE_MEETING       => 'bi bi-people-fill',
            self::MODULE_DECISION      => 'bi bi-clipboard-check',
            self::MODULE_WEEKLY_REPORT => 'bi bi-file-earmark-text',
            self::MODULE_SCORE         => 'bi bi-star-fill',
        ][$this->module_type] ?? 'bi bi-trophy';
    }

    public function moduleColor(): string
    {
        return [
            self::MODULE_TASK          => '#38ef7d',
            self::MODULE_INTERNET      => '#4facfe',
            self::MODULE_KASIR         => '#f5a623',
            self::MODULE_SPRINTER      => '#f093fb',
            self::MODULE_MEETING       => '#667eea',
            self::MODULE_DECISION      => '#f5576c',
            self::MODULE_WEEKLY_REPORT => '#43e97b',
            self::MODULE_SCORE         => '#ffd700',
        ][$this->module_type] ?? '#a0a8d0';
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($this->end_date->endOfDay(), false));
    }

    public function isActive(): bool
    {
        $today = now()->toDateString();
        return ($this->start_date->toDateString() <= $today && $this->end_date->toDateString() >= $today) && $this->status == 'running';
    }

    public function isExpired(): bool
    {
        return $this->end_date->toDateString() < now()->toDateString();
    }


    public function isAbles(): bool
    {
        return $this->status == 'draft' ? true : false;
    }

    public function isFinished(): bool
    {
        return $this->status == 'finish' ? true : false;
    }
}

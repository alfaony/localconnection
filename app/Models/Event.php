<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'company_id',
        'created_by',
        'name',
        'description',
        'image',
        'color',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_routine',
        'routine_end_date',
        'is_active',
        'sync_participants',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'routine_end_date'  => 'date',
        'is_routine'        => 'boolean',
        'is_active'         => 'boolean',
        'sync_participants' => 'boolean',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }

    // ── Relationships ───────────────────────────────────────────────────────

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function eventUsers()
    {
        return $this->hasMany(EventUser::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'event_users', 'event_id', 'user_id')
                    ->withPivot('invited_by')
                    ->withTimestamps();
    }

    public function occurrences()
    {
        return $this->hasMany(EventOccurrence::class);
    }

    public function eventViews()
    {
        return $this->hasMany(EventView::class);
    }

    /**
     * Challenge yang tergabung dalam event ini.
     * Challenge tidak wajib punya event (relasi opsional dari sisi challenge).
     */
    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'event_challenges', 'event_id', 'challenge_id')
                    ->withTimestamps();
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Durasi dalam hari (inklusif).
     */
    public function durationDays(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function timeRange(): string
    {
        if ($this->start_time && $this->end_time) {
            return substr($this->start_time, 0, 5) . ' – ' . substr($this->end_time, 0, 5);
        }
        if ($this->start_time) {
            return 'Mulai ' . substr($this->start_time, 0, 5);
        }
        return '';
    }

    /**
     * Buat occurrence untuk minggu tertentu (jika belum ada).
     * $weekStart = Carbon (monday of target week)
     */
    public function generateOccurrenceForWeek(Carbon $weekStart): ?EventOccurrence
    {
        // Hitung offset hari dari occurrence pertama ke start week
        $firstStart = $this->start_date->copy()->startOfDay();
        $firstEnd   = $this->end_date->copy()->startOfDay();
        $duration   = $firstStart->diffInDays($firstEnd); // durasi event dalam hari

        // Cari occurrence yang jatuh di minggu ini
        // Occurrence ke-N: start = firstStart + N*7, end = firstEnd + N*7
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Cek apakah routine_end_date sudah terlampaui
        if ($this->routine_end_date && $weekStart->gt($this->routine_end_date)) {
            return null;
        }

        // Hitung occurrence start terdekat yang overlap dengan minggu ini
        $diff = $firstStart->diffInDays($weekStart, false);
        if ($diff < 0) {
            // Minggu yang diminta sebelum occurrence pertama — kembalikan occurrence pertama jika masih overlap
            $occStart = $firstStart->copy();
        } else {
            $weeks    = (int) floor($diff / 7);
            $occStart = $firstStart->copy()->addWeeks($weeks);
            // Jika occStart sudah lewat minggu ini, mundur 1 minggu
            if ($occStart->gt($weekEnd)) {
                $occStart->subWeek();
            }
        }
        $occEnd = $occStart->copy()->addDays($duration);

        // Pastikan occurrence ini overlap dengan minggu yang diminta
        if ($occEnd->lt($weekStart) || $occStart->gt($weekEnd)) {
            return null;
        }

        // Cek routine_end_date
        if ($this->routine_end_date && $occStart->gt($this->routine_end_date)) {
            return null;
        }

        return EventOccurrence::firstOrCreate(
            ['event_id' => $this->id, 'start_date' => $occStart->toDateString()],
            [
                'id'       => Uuid::uuid4()->toString(),
                'end_date' => $occEnd->toDateString(),
            ]
        );
    }
}

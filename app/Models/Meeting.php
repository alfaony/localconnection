<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

use Carbon\Carbon;

use App\Helpers\Access;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'user_id',
        'meeting_name',
        'meeting_type',
        'google_meet_link',
        'google_event_id',
        'meeting_agenda',
        'meeting_location',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'notes',
        'pic_name',
        'status',
        'attachment',
        'attachment_link'
    ];

    protected $casts = [
        'participants' => 'array',
    ];

    public function setMeetingNameAttribute($value)
    {
        if ($this->attributes['meeting_name'] ?? null !== $value) {
            $this->attributes['meeting_name'] = $value;
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        } else {
            $this->attributes['meeting_name'] = $value;
        }
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::where('slug', $slug)->withTrashed()->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'meeting_user')
                ->withPivot(['is_attended','join_time']);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function getCombinedParticipantsAttribute(): Collection
    {
        // Peserta internal via relasi belongsToMany
        $internal = $this->participants()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => ParamSchema::INTERNAL,
                'is_attended' => $user->pivot->is_attended,
                'join_time' => $user->pivot->join_time
            ];
        });
        

        // Peserta eksternal via kolom JSON 'participants'
        $external = collect($this->participants_external)->map(function ($email) {
            return [
                'id' => $email,
                'email' => $email,
                'name' => $email . ' (External)',
                'status' => ParamSchema::EXTERNAL
            ];
        });

        if ($internal->isNotEmpty() && $external->isNotEmpty()) {
            return $internal->merge($external)->values();
        }

        if ($internal->isNotEmpty()) {
            return $internal;
        }

        if ($external->isNotEmpty()) {
            return $external;
        }

        return collect();
    }

    public function getParticipantsExternalAttribute(): array
    {
        return is_array($this->participants)
            ? $this->participants
            : json_decode($this->participants ?? '[]', true);
    }

    public function scopeByCompany($query, $companyId)
    {
        $user = auth()->user();
        $companyIds = $user->accessibleCompanies->pluck('id')->push($companyId)->unique();

        if ($user->role->name !== RoleSchema::ROOT) {
            return $query->where(function ($q) use ($companyIds, $user) {
                $q->whereIn("company_id", $companyIds)
                ->orWhereHas('participants', function ($q2) use ($user) {
                    $q2->where('users.id', $user->id);
                });
            });
        }

        return $query;
    }
}


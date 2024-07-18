<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Objective extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['id', 'division_id','mission_id', 'name', 'user_id', 'start_date', 'end_date'];

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    public function setNameAttribute($value)
    {
        if ($this->attributes['name'] ?? null !== $value) {
            $this->attributes['name'] = $value;
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        } else {
            $this->attributes['name'] = $value;
        }
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::where('slug', $slug)->withTrashed()->exists()) 
        {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
    
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function keyResults()
    {
        return $this->hasMany(ObjectiveKeyResult::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function getDateShowAttribute()
    {
        // Atur lokal ke bahasa Indonesia
        Carbon::setLocale('id');

        if($this->start_date && $this->end_date)
        {
            $startDate = Carbon::parse($this->start_date);
            $endDate = Carbon::parse($this->end_date);
            $now = Carbon::now();
    
            // Fungsi untuk menerjemahkan bulan
            $translateMonth = function ($date) {
                $months = [
                    'January' => 'Januari',
                    'February' => 'Februari',
                    'March' => 'Maret',
                    'April' => 'April',
                    'May' => 'Mei',
                    'June' => 'Juni',
                    'July' => 'Juli',
                    'August' => 'Agustus',
                    'September' => 'September',
                    'October' => 'Oktober',
                    'November' => 'November',
                    'December' => 'Desember',
                ];
                return $months[$date->format('F')] ?? $date->format('F');
            };
    
            if ($startDate->isSameDay($endDate)) {
                if ($startDate->isToday()) {
                    return 'Hari Ini';
                } elseif ($startDate->isTomorrow()) {
                    return 'Besok';
                } elseif ($startDate->isSameWeek($now)) {
                    return $startDate->translatedFormat('l');
                } else {
                    return $startDate->format('d') . ' ' . $translateMonth($startDate);
                }
            } else {
                $startStr = $startDate->isToday() ? 'Hari Ini' : ($startDate->isTomorrow() ? 'Besok' : $startDate->format('d') . ' ' . $translateMonth($startDate));
                $endStr = $endDate->isToday() ? 'Hari Ini' : ($endDate->isTomorrow() ? 'Besok' : $endDate->format('d') . ' ' . $translateMonth($endDate));
    
                if ($startDate->year !== $endDate->year) {
                    $startStr .= ' ' . $startDate->format('Y');
                    $endStr .= ' ' . $endDate->format('Y');
                } elseif ($startDate->month !== $endDate->month) {
                    $startStr .= ' ' . $startDate->format('Y');
                }
    
                if ($startDate->isSameWeek($now) && $endDate->isSameWeek($now)) {
                    if ($endDate->isToday()) {
                        return $startStr . ' - Hari Ini';
                    } elseif ($endDate->isTomorrow()) {
                        return $startStr . ' - Besok';
                    }
                }
                return $startStr . ' - ' . $endStr;
            }
        }else
        {
            return '-';
        }
    }
    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('user', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }

    public function scopeByUserDivisions($query, $userId)
    {
        $user = User::with('divisions')->find($userId);
        $divisionIds = $user->divisions->pluck('id');

        return $query->whereIn('division_id', $divisionIds);
    }
}

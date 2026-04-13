<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
class Vehicle extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'company_id', 'pic_user_id', 'vehicle_id', 'vehicle_type', 'type', 'position',
        'service_terakhir', 'subscription_stnk', 'subscription_kir'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'service_terakhir',
                'subscription_stnk',
                'subscription_kir'
            ])
            ->useLogName('vehicle_update')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getColorStatusFor($dateField)
    {
        $date = $this->{$dateField};

        if (!$date) {
            return 'secondary'; // atau null, kalau tidak ingin ditampilkan
        }

        $diff = Carbon::now()->diffInDays(Carbon::parse($date), false);

        if ($diff > 30) {
            return 'success'; // hijau
        } elseif ($diff >= 0 && $diff <= 30) {
            return 'warning'; // kuning
        } else {
            return 'danger'; // merah (sudah lewat)
        }
    }

    public function getStatusFor($dateField)
    {
        $date = $this->{$dateField};

        if (!$date) {
            return 'secondary'; // atau null, kalau tidak ingin ditampilkan
        }

        $diff = Carbon::now()->diffInDays(Carbon::parse($date), false);

        if ($diff > 30) {
            return false;
        } elseif ($diff >= 0 && $diff <= 30) {
            return true;
        } else {
            return true;
        }
    }

    public function picUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id')->withTrashed();
    }

    public function photos()
    {
        return $this->hasMany(VehiclePhoto::class)->latest();
    }

    public function hasPhotoThisMonth()
    {
        return $this->photos()
            ->whereYear('taken_at', now()->year)
            ->whereMonth('taken_at', now()->month)
            ->exists();
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

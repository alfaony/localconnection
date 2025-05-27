<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
class SubscribeLetter extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'pic_user_id', 'name', 'valid_from', 'valid_until', 'document_path','company_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'valid_from',
                'valid_until'
            ])
            ->useLogName('subscribe_letter_update')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getColorStatusFor($field)
    {
        $date = $this->{$field};

        if (!$date) return 'secondary';

        $diff = Carbon::now()->diffInDays(Carbon::parse($date), false);

        if ($diff > 30) return 'success';       // Hijau
        elseif ($diff >= 0) return 'warning';   // Kuning
        else return 'danger';                   // Merah
    }
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

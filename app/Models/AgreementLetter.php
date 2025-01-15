<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class AgreementLetter extends Model
{
    use HasFactory,SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string
    protected $casts = [
        'custom_fields' => 'array', // Agar otomatis dikonversi ke array saat diakses
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

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value;
        if (empty($this->slug)) {
            $this->attributes['slug'] = Uuid::uuid4()->toString();
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

    public function getRouteKeyName()
    {
        return 'slug';
    }
    
    public function userCreate()
    {
        return $this->belongsTo(User::class,'user_created_id','id')->withTrashed();
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class)->withTrashed();
    }

    public function templateAgreement()
    {
        return $this->belongsTo(TemplateAgreement::class,'template_agreement_id');
    }
    public function getCustomField($key, $default = '-')
    {
        return $this->custom_fields[$key] ?? $default;
    }
    public function getRentStartDurationIdAttribute()
    {
        $date = Carbon::parse($this->rent_start_duration)->locale('id');
        $date->settings(['formatFunction' => 'translatedFormat']);
        return $date->format('l, j F Y');
    }

    public function getRentEndDurationIdAttribute()
    {
        $date = Carbon::parse($this->rent_end_duration)->locale('id');
        $date->settings(['formatFunction' => 'translatedFormat']);
        return $date->format('l, j F Y');
    }

    public function getRentStartDurationIdNoDayAttribute()
    {
        $date = Carbon::parse($this->rent_start_duration)->locale('id');
        $date->settings(['formatFunction' => 'translatedFormat']);
        return $date->format('j F Y');
    }

    public function getRentEndDurationIdNoDayAttribute()
    {
        $date = Carbon::parse($this->rent_end_duration)->locale('id');
        $date->settings(['formatFunction' => 'translatedFormat']);
        return $date->format('j F Y');
    }

    public function getRentCountAttribute()
    {
        $rentStart = Carbon::parse($this->rent_start_duration);
        $rentEnd = Carbon::parse($this->rent_end_duration);
    
        $diff = $rentStart->diff($rentEnd);
    
        $diffInYears = $diff->y; // Tahun
        $diffInMonths = $diff->m; // Bulan
        $diffInDays = $diff->d; // Hari
    
        $result = [];
    
        if ($diffInYears > 0) {
            $result[] = $diffInYears . ' tahun';
        }
    
        if ($diffInMonths > 0) {
            $result[] = $diffInMonths . ' bulan';
        }
    
        if ($diffInDays > 0) {
            $result[] = $diffInDays . ' hari';
        }
    
        return implode(' ', $result);
    }
    // public function getRentCountAttribute()
    // {
    //     $rentStart = Carbon::parse($this->rent_start_duration);
    //     $rentEnd = Carbon::parse($this->rent_end_duration);

    //     $diffInYears = $rentStart->diffInYears($rentEnd);
    //     $diffInMonths = $rentStart->diffInMonths($rentEnd) % 12;

    //     $result = '';

    //     if ($diffInYears > 0) {
    //         $result .= $diffInYears . ' tahun ';
    //     }

    //     if ($diffInMonths > 0) {
    //         $result .= $diffInMonths . ' bulan';
    //     }

    //     return $result;
    // }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId)
        {
            return $query->whereHas('userCreate', function ($query) use ($companyId) 
            {
                $query->where('company_id', $companyId);
            });
        }
    }
    
}
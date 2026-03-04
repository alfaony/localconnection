<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use NumberFormatter;

class Quote extends Model
{
    use HasFactory,SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string

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

    public function quoteProduct()
    {
        return $this->hasMany(QuoteProduct::class);
    }

    public function userCreate()
    {
        return $this->belongsTo(User::class,'user_created_id','id')->withTrashed();
    }

    public function userUpdate()
    {
        return $this->belongsTo(User::class,'user_updated_id','id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function divisionBudget()
    {
        return $this->belongsTo(DivisionBudget::class,'division_budget_id')->withTrashed();
    }

    public function workOrder()
    {
        return $this->hasOne(WorkOrder::class);
    }
    
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getTotalTerbilangAttribute()
    {
        return $this->convertToWords($this->total);
    }

    // Fungsi untuk mengonversi angka ke teks (bahasa Indonesia)
    private function convertToWords($number)
    {
        if (!is_numeric($number)) {
            return "Nol";
        }

        // Gunakan NumberFormatter untuk bahasa Indonesia
        $formatter = new NumberFormatter('id_ID', NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number)) . " rupiah";
    }
    public function getQuoteNumberResultAttribute()
    {
        $date = Carbon::parse($this->created_at)->format('m/Y');
        $nomor = $this->quote_number;
        return $nomor.'/'.$date;
    }

    public function scopeByUser($query,$user)
    {
        if($user)
        {
            $query->whereHas('userCreate', function($query) use ($user) 
            {
                $query->where('name', 'LIKE', '%' . $user . '%');
            });
        }
    }

    public function scopeByNumberResult($query,$number_result)
    {
        if($number_result)
        {
            return $query->where('number_result','like','%'.$number_result.'%');
        }
    }

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

    public function scopeByActive($query)
    {
        return $query->whereDate('date', '>=', Carbon::now());
    }
    public function scopeByDivision($query, $divisionId)
    {
        if ($divisionId) {
            return $query->whereHas('divisionBudget', function ($q) use ($divisionId) {
                $q->where('division_id', $divisionId);
            });
        }
    }
}

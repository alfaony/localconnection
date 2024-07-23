<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class DivisionBudget extends Model
{
    use HasFactory,SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string
    protected $fillable = [
        'user_id',
        'division_id',
        'name',
        'amount',
        'is_approved',
        'notes',
        'file'
    ];

    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = $this->createUniqueSlug($value);
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
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    // DivisionBudget.php
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'division_budget_id');
    }

    // Quote.php
    public function divisionBudget()
    {
        return $this->belongsTo(DivisionBudget::class);
    }

    public function getInitialBudgetAttribute()
    {
        return $this->quotes->sum('total') + $this->amount;
    }

    public function getBudgetUsagePercentageAttribute()
    {
        $initialBudget = $this->initial_budget;
        $usedBudget = $this->quotes->sum('total');

        if ($initialBudget == 0) {
            return 0;
        }

        return round(($usedBudget / $initialBudget) * 100, 2);
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'invoices'; // Nama tabel di database
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'connecting' => 'boolean',
    ];
    protected $keyType = 'uuid'; // Karena menggunakan UUID sebagai primary key
    public $incrementing = false; // Non-incrementing ID, karena UUID
    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log semua perubahan atribut
            ->logOnlyDirty() // Hanya log perubahan yang berbeda dari nilai sebelumnya
            ->useLogName('invoice') // Nama log (opsional)
            ->setDescriptionForEvent(fn(string $eventName) => "Invoice has been {$eventName}"); // Deskripsi untuk setiap event
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

    // Relasi ke model User
    public function userCreate(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_created_id');
    }

    // Relasi ke model BAST
    public function bast(): BelongsTo
    {
        return $this->belongsTo(Bast::class);
    }

    public function invoiceProducts()
    {
        return $this->hasMany(InvoiceProduct::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class)->withTrashed();
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
}


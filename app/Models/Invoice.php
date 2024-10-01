<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invoices'; // Nama tabel di database

    protected $keyType = 'uuid'; // Karena menggunakan UUID sebagai primary key
    public $incrementing = false; // Non-incrementing ID, karena UUID

    protected $fillable = [
        'user_id',
        'bast_id',
        'start_date',
        'end_date',
        'status',
        'invoice_xero_id',
        'contact_xero_id',
    ];

    // Relasi ke model User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke model BAST
    public function bast(): BelongsTo
    {
        return $this->belongsTo(Bast::class);
    }

    // Mutator untuk format tanggal (jika diperlukan)
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

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


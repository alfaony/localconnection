<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyCustomerSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'snapshot_date',
        'active_customer_count',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}

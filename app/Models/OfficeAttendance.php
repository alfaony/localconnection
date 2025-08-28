<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarcodeAttendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'barcode_attendances';

    protected $keyType = 'string'; // Karena UUID
    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_id',
        'code',
        'is_used',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeByCompany($query,$companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        if($companyIds && Auth::user()->role->name != RoleSchema::ROOT)
        {
            return $query->whereIn("company_id",$companyIds);
        }
    }
}

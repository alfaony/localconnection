<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'barcode_attendance_id',
        'time',
        'location_lat',
        'location_long',
        'selfie_path',
    ];

    public function barcode()
    {
        return $this->belongsTo(BarcodeAttendance::class, 'barcode_attendance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCompany($query, $companyIds, $access = false)
    {
        if($access) 
        {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyIds)->unique();   
            return $query->whereIn('company_id', $companyIds);
        }else
        {
            return $query->where('user_id', auth()->user()->id);
        }
    }
}
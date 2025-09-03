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
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeByCompany($query, $companyIds, $accessGeneral = false, $accessDivision = false)
    {
        if($accessGeneral) 
        {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyIds)->unique();   
            return $query->whereIn('company_id', $companyIds);
        }
        elseif ($accessDivision) 
        {
            // Filter hanya attendance milik user yang berada pada division yang sama dengan user saat ini
            $divisionIds = auth()->user()->divisions()->pluck('divisions.id');

            return $query->whereHas('user.divisions', function ($q) use ($divisionIds) {
                $q->whereIn('divisions.id', $divisionIds);
            });
        }
        else
        {
            return $query->where('user_id', auth()->user()->id);
        }
    }
}
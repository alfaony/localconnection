<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;

class Kye extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'full_name',
        'birth_place',
        'birth_date',
        'address',
        'employee_photo',
        'ktp_number',
        'ktp_photo',
        'selfie_ktp',
        'ktp_family',
        'npwp_number',
        'google_maps',
        'house_photo',
        'skck',
        'phone_number',
        'email',
        'imei_number',
        'emergency_phone',
        'emergency_contact',
        'bank_account_name',
        'bank_name',
        'account_number',
        'approval_status',
        'approval_note',
        'call_name',
        'gender',
        'marital_status',
        'number_of_children',
        'npwp_photo',
        'address_domisili',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // Automatically generate a UUID when creating a new record
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public function isDestroy()
    {
        return $this->approval_status == 'approved' ? false : true;
    }

    public function isEdit()
    {
        if($this->approval_status != 'approved')
        {
            return true;
        }else
        {
            return false;
        }
    }

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereHas('user', function ($query) use ($companyIds)
            {
                $query->whereIn('company_id', $companyIds);
            });
        }
    }
}

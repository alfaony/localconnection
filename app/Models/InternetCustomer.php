<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternetCustomer extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string'; // Tipe kunci primer adalah string

    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    protected $fillable = [
        'user_customer_id',
        'company_id',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
        'internet_package_id',
        'partnership_agreement_id',
        'code',
        'name',
        'address',
        'ktp_number',
        'ktp_photo',
        'is_paid',
        'status',
    ];

    // ✅ RELATIONS
    public function userCustomer()
    {
        return $this->belongsTo(UserCustomer::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function subdistrict()
    {
        return $this->belongsTo(Subdistrict::class);
    }

    public function internetPackage()
    {
        return $this->belongsTo(InternetPackage::class);
    }

    public function partnershipAgreement()
    {
        return $this->belongsTo(PartnershipAgreement::class);
    }

    public function purchase()
    {
        return $this->hasOne(InternetCustomerPurchase::class);
    }

    public function installation()
    {
        return $this->hasOne(InternetCustomerInstallation::class);
    }

    public function getStatusBadgeAttribute()
    {
        $status = $this->attributes['status'] ?? '';

        switch ($status) {
            case 'pending':
                return '<span class="badge badge-warning">Pending</span>';
            case 'waiting_payment_confirmation':
                return '<span class="badge badge-info">Waiting Payment Confirmation</span>';
            case 'process_installation':
                return '<span class="badge badge-primary">Process Installation</span>';
            case 'installed':
                return '<span class="badge badge-success">Installed</span>';
            default:
                return '<span class="badge badge-secondary">Unknown</span>';
        }
    }

    public function scopeByCompany($query,$companyId)
    {
        if($companyId && Auth::user()->role->name != RoleSchema::ROOT)
        {
            $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
            return $query->whereIn("company_id",$companyIds);
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;
use App\Services\RouterOSService;
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
        'promo_id',
        'router_id',
        'access_type',
        'username',
        'pass_hash',
        'ip_address',
        'mac_address',
        'vlan_id',
        'expires_at',
        'ros_comment_uuid',
        'meta',
        'override_pool_id'
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
        return $this->belongsTo(InternetPackage::class, 'internet_package_id')->withTrashed();
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class, 'promo_id')->withTrashed();
    }

    public function overridePool()
    {
        return $this->belongsTo(AddressPool::class, 'override_pool_id');
    }

    public function partnershipAgreement()
    {
        return $this->belongsTo(PartnershipAgreement::class);
    }

    public function router()
    {
        return $this->belongsTo(Router::class);
    }

     public function installation()
    {
        return $this->hasOne(InternetCustomerInstallation::class);
    }

    public function getOldestUnconfirmedPurchase()
    {
        if ($this->purchases()->exists()) {
            return $this->purchases()
                ->whereNull('user_finance_id')
                ->whereNull('confirmation_finance_at')
                ->orderBy('created_at', 'asc')
                ->first();
        }

        return null; // Tidak ada purchases
    }
    public function purchases()
    {
        return $this->hasMany(InternetCustomerPurchase::class);
    }

    public function isActiveConneciton()
    {
        $ros = new RouterOSService();
        $client = $ros->client($this->router);
        return $ros->isUserActive($client, $this->username);
    }

    public function candidateRouters()
    {
        $ods = $this->subdistrict?->coverageService?->coverageServiceOds ?? collect();
        if ($ods->isEmpty()) return collect();

        $odIds = $ods->pluck('optical_distribution_id');

        return \App\Models\Router::select('id','name')->whereHas('pop.opticalDistributions', fn($q)=>
            $q->whereIn('optical_distributions.id',$odIds)
        )->distinct()->get();
    }

    public function getStatusBadgeAttribute()
    {
        $status = $this->attributes['status'] ?? '';

        switch ($status) {
            case 'pending':
                return '<span class="badge badge-light">Pending</span>';
            case 'waiting_payment_subscription':
                return '<span class="badge badge-secondary">Waiting Payment Subscription</span>';
            case 'waiting_payment_confirmation':
                return '<span class="badge badge-secondary">Waiting Payment Confirmation</span>';
            case 'process_installation':
                return '<span class="badge badge-lightblue">Process Installation</span>';
            case 'installed':
                return '<span class="badge badge-info">Installed</span>';
            case 'active':
                return '<span class="badge badge-success">Active</span>';
            case 'expired':
                return '<span class="badge badge-danger">Expired</span>';
            case 'cancelled':
                return '<span class="badge badge-dark">Cancelled</span>';
            case 'suspended':
                return '<span class="badge badge-warning">Suspended</span>';
            case 'disconnected':
                return '<span class="badge badge-danger">Disconnected</span>';
            case "reactivated":
                return '<span class="badge badge-success">Reactivated</span>';
            default:
                return '<span class="badge badge-light">Unknown</span>';
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

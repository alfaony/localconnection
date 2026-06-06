<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Auth;
use App\Schemas\RoleSchema;
use App\Models\Radius\RadAcct;
use App\Services\RouterOSService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\Province;

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

            DB::transaction(function () use ($model) {
                $customer = $model;
                // OPTIONAL: Lock table for extra safety
                // DB::statement('LOCK TABLE internet_customers WRITE');

                // ===== SAFE AUTO-INCREMENT =====
                do {
                    $nextNumber = InternetCustomer::max('code_cust') + 1;

                    $duplicateNumber = InternetCustomer::where('code_cust', $nextNumber)->exists();
                } while ($duplicateNumber);

                $customer->code_cust = $nextNumber;

                // ===== PROVINCE PREFIX =====
                $prefix = Province::find($customer->province_id)->initial;

                // ===== GENERATE CUSTOMER CODE =====
                do {
                    $finalCode = $prefix . $nextNumber;

                    $duplicateCode = InternetCustomer::where('code', $finalCode)->exists();
                } while ($duplicateCode);

                $customer->code = $finalCode;
            });
        });

        static::updating(function ($model) {
            if ($model->isDirty('status')) {
                $model->meta = null;
            }
        });
    }

    function generateProvincePrefix($provinceName)
    {
        if (!$provinceName) return 'XXX';

        $words = explode(' ', trim($provinceName));

        // Jika ada 3 kata → ambil satu huruf tiap kata (NTT, NTT, DIY)
        if (count($words) >= 3) {
            return strtoupper(
                $words[0][0] . $words[1][0] . $words[2][0]
            );
        }

        // Jika ada 2 kata → ambil huruf pertama tiap kata (JB, SU)
        if (count($words) == 2) {
            return strtoupper(
                $words[0][0] . $words[1][0]
            );
        }

        // Jika 1 kata → ambil 2 huruf pertama (BA, RI)
        return strtoupper(substr($provinceName, 0, 2));
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
        'customer_type',
        'promo_id',
        'router_id',
        'access_type',
        'username',
        'pass_hash',
        'ip_address',
        'local_address',
        'mac_address',
        'vlan_id',
        'expires_at',
        'ros_comment_uuid',
        'meta',
        'override_pool_id',
        'last_updated_router',
        'code_cust',
        'optical_distribution_id',
        'grouping_id',
        'group_id',
        'action_user_id',
        'hotspot_server_id',
        'ip_binding_type',
        'ip_binding_mode',
        'npwp_number',
        'npwp_photo',
    ];

    // ✅ RELATIONS
    public function coupons()
    {
        return $this->hasMany(InternetPurchaseCoupon::class,'internet_customer_id');
    }

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

    public function router()
    {
        return $this->belongsTo(Router::class)->withTrashed();
    }

    public function hotspotServer()
    {
        return $this->belongsTo(HotspotServer::class, 'hotspot_server_id');
    }

    public function odp()
    {
        return $this->belongsTo(OpticalDistribution::class, 'optical_distribution_id')->withTrashed();
    }

    public function group()
    {
        return $this->belongsTo(InternetCustomerGroup::class, 'group_id')->withTrashed();
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

    public function getOldestUnconfirmed()
    {
        if ($this->purchases()->exists()) {
            return $this->purchases()
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return null; // Tidak ada purchases
    }
    public function purchases()
    {
        return $this->hasMany(InternetCustomerPurchase::class);
    }

    public function latestPurchase()
    {
        return $this->hasOne(InternetCustomerPurchase::class)->latestOfMany();
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_user_id')->withTrashed();
    }

    public function isActiveConneciton()
    {
        // 🟢 RADIUS primary
        try {
            return RadAcct::where('username', $this->username)
                ->whereNull('acctstoptime')
                ->exists();
        } catch (\Throwable $e) {
            // 🔴 Fallback Direct API
            try {
                $ros = new RouterOSService();
                $client = $ros->client($this->router);
                return $ros->isUserActive($client, $this->username);
            } catch (\Throwable $e2) {
                return false;
            }
        }
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
                return '<span class="badge badge-light text-dark">Pending</span>';
            case 'waiting_payment_subscription':
                return '<span class="badge badge-secondary">Waiting Payment Subscription</span>';
            case 'waiting_payment_confirmation':
                return '<span class="badge badge-secondary">Waiting Payment Confirmation</span>';
            case 'process_installation':
                return '<span class="badge badge-primary">Process Installation</span>';
            case 'customer_existing':
                return '<span class="badge badge-primary">Customer Existing Installation</span>';
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
                return '<span class="badge badge-success">Connecting</span>';
            case 'closed':
                return '<span class="badge badge-dark">Closed</span>';
            case 'inactive':
                return '<span class="badge badge-danger">Inactive</span>';
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

    public function scopeByCompanyJob($query,$companyIds)
    {
        if($companyIds)
        {
            return $query->whereIn("company_id",$companyIds);
        }
        return $query;
    }
}

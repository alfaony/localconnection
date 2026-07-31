<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternetPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'bandwidth',
        'type',
        'customer_type',
        'price',
        'price_nett',
        'description',
        'is_active',
        'company_id',
        'access_type',
        'rate_down_mbps',
        'rate_up_mbps',
        'fup_rate_down_mbps',
        'fup_rate_up_mbps',
        'quota_bytes',
        'version',
        'meta',
        'session_timeout_seconds',
        'idle_timeout_seconds',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'session_timeout_seconds' => 'integer',
        'idle_timeout_seconds'    => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function promos()
    {
        return $this->belongsToMany(Promo::class);
    }

    public function getPromoActiveAttribute()
    {
        return $this->promos()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere(function ($q) {
                        $today = Carbon::today();
                        $q->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function scopeByCompany($query, $companyId)
    {
        $companyIds = Auth::user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('company_id', $companyIds);
    }

    // ==================== REGION RELATIONS ====================

    public function regions(): HasMany
    {
        return $this->hasMany(InternetPackageRegion::class);
    }

    public function regionProvinces(): HasMany
    {
        return $this->hasMany(InternetPackageRegion::class)->where('region_type', 'province');
    }

    public function regionCities(): HasMany
    {
        return $this->hasMany(InternetPackageRegion::class)->where('region_type', 'city');
    }

    public function regionDistricts(): HasMany
    {
        return $this->hasMany(InternetPackageRegion::class)->where('region_type', 'district');
    }

    public function regionSubdistricts(): HasMany
    {
        return $this->hasMany(InternetPackageRegion::class)->where('region_type', 'subdistrict');
    }

    public function hotspotVoucherBatches(): HasMany
    {
        return $this->hasMany(HotspotVoucherBatch::class, 'internet_package_id');
    }

    // ==================== HOTSPOT VOUCHER ACCESSORS ====================

    /** Label durasi human-readable, misal "1 Jam", "1 Hari", "Unlimited" */
    public function getDurationLabelAttribute(): string
    {
        $s = (int) $this->session_timeout_seconds;
        if ($s <= 0) return 'Unlimited';
        if ($s < 3600) return ($s / 60) . ' Menit';
        if ($s < 86400) return ($s / 3600) . ' Jam';
        return ($s / 86400) . ' Hari';
    }

    /** Label quota human-readable, misal "1 GB", "500 MB", "Unlimited" */
    public function getQuotaLabelAttribute(): string
    {
        $b = (int) $this->quota_bytes;
        if ($b <= 0) return 'Unlimited';
        if ($b < 1024 * 1024) return round($b / 1024, 1) . ' KB';
        if ($b < 1024 * 1024 * 1024) return round($b / (1024 * 1024), 1) . ' MB';
        return round($b / (1024 * 1024 * 1024), 2) . ' GB';
    }

    /** Rate string untuk MikroTik: "10M/5M" */
    public function getRateLimitAttribute(): string
    {
        return "{$this->rate_down_mbps}M/{$this->rate_up_mbps}M";
    }

    /**
     * Scope: tampilkan paket yang relevan untuk wilayah tertentu.
     *
     * Logika:
     * - Paket GLOBAL (tanpa region aktif apapun) → selalu tampil
     * - Paket REGION-SPESIFIK → hanya tampil jika ada region aktif yang cocok
     *   dengan subdistrict, district, city, atau province si customer
     *
     * Prioritas: subdistrict > district > city > province > global
     *
     * CATATAN: doesntHave('regions') diganti dengan subquery is_active=true
     * agar paket yang semua regionnya inactive tetap dianggap 'global'.
     */
    public function scopeForRegion($query, $provinceId = null, $cityId = null, $districtId = null, $subdistrictId = null)
    {
        // Cast ke int agar string '0' atau '' dari Select2 tidak dianggap valid
        $provinceId    = (int) $provinceId    ?: null;
        $cityId        = (int) $cityId        ?: null;
        $districtId    = (int) $districtId    ?: null;
        $subdistrictId = (int) $subdistrictId ?: null;

        return $query->where(function ($q) use ($provinceId, $cityId, $districtId, $subdistrictId) {

            // Paket global: tidak punya region aktif SAMA SEKALI
            $q->whereDoesntHave('regions', fn ($r) => $r->where('is_active', true));

            // Atau punya region aktif yang COCOK dengan wilayah customer
            if ($subdistrictId) {
                $q->orWhereHas('regions', fn ($r) =>
                    $r->where('region_type', 'subdistrict')
                      ->where('region_id', $subdistrictId)
                      ->where('is_active', true)
                );
            }
            if ($districtId) {
                $q->orWhereHas('regions', fn ($r) =>
                    $r->where('region_type', 'district')
                      ->where('region_id', $districtId)
                      ->where('is_active', true)
                );
            }
            if ($cityId) {
                $q->orWhereHas('regions', fn ($r) =>
                    $r->where('region_type', 'city')
                      ->where('region_id', $cityId)
                      ->where('is_active', true)
                );
            }
            if ($provinceId) {
                $q->orWhereHas('regions', fn ($r) =>
                    $r->where('region_type', 'province')
                      ->where('region_id', $provinceId)
                      ->where('is_active', true)
                );
            }
        });
    }

    /**
     * Ambil harga paket untuk wilayah tertentu.
     *
     * Konsep:
     * - Wilayah mengontrol apakah paket MUNCUL di area tersebut
     * - Tiap wilayah BISA punya harga sendiri (price / price_nett di internet_package_regions)
     * - Jika harga di level paling spesifik null, lanjut cari harga di wilayah induknya
     * - Jika semua harga wilayah null → ikut harga default paket (price / price_nett)
     * - Prioritas harga: subdistrict > district > city > province > global
     *
     * @return array ['price' => float, 'price_nett' => float, 'region_label' => string, 'region_type' => string]
     */
    public function getPriceForRegion($provinceId = null, $cityId = null, $districtId = null, $subdistrictId = null): array
    {
        // Cast ke int agar '' dari Select2 tidak dianggap valid
        $subdistrictId = (int) $subdistrictId ?: null;
        $districtId    = (int) $districtId    ?: null;
        $cityId        = (int) $cityId        ?: null;
        $provinceId    = (int) $provinceId    ?: null;

        $regionIds = [
            'subdistrict' => $subdistrictId,
            'district'    => $districtId,
            'city'        => $cityId,
            'province'    => $provinceId,
        ];

        // Ambil seluruh wilayah yang cocok dalam urutan paling spesifik. Sebuah
        // region tanpa harga tetap berguna untuk ketersediaan paket, tetapi tidak
        // boleh menutup harga khusus yang telah diset pada wilayah induknya.
        $matchedRegions = collect($regionIds)
            ->filter()
            ->map(function ($regionId, $regionType) {
                return $this->regions
                    ->where('region_type', $regionType)
                    ->where('region_id', $regionId)
                    ->where('is_active', true)
                    ->first();
            })
            ->filter()
            ->values();

        // price dan price_nett dapat diwariskan secara independen karena salah
        // satunya boleh dikosongkan pada konfigurasi wilayah.
        $priceRegion = $matchedRegions->first(
            fn ($region) => $region->price !== null
        );
        $priceNettRegion = $matchedRegions->first(
            fn ($region) => $region->price_nett !== null
        );

        $price     = $priceRegion ? (float) $priceRegion->price : (float) $this->price;
        $priceNett = $priceNettRegion ? (float) $priceNettRegion->price_nett : (float) $this->price_nett;

        // Metadata utama mengikuti sumber harga nett karena nilai itulah yang
        // digunakan saat tagihan bulanan otomatis dibuat.
        $effectiveRegion = $priceNettRegion ?? $priceRegion ?? $matchedRegions->first();
        $regionType      = $effectiveRegion?->region_type ?? 'global';
        $regionLabel     = $effectiveRegion?->region_label ?? 'Global';

        return [
            'price'        => $price,
            'price_nett'   => $priceNett,
            'region_label' => $regionLabel,
            'region_type'  => $regionType,
            'is_custom_price'       => $priceRegion !== null || $priceNettRegion !== null,
            'price_region_type'      => $priceRegion?->region_type ?? 'global',
            'price_nett_region_type' => $priceNettRegion?->region_type ?? 'global',
        ];
    }
}

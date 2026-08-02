<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class Company extends Model
{
    use HasFactory,SoftDeletes;

    public $incrementing = false; // Karena kita menggunakan UUID, bukan auto-increment
    protected $keyType = 'string'; // Tipe kunci primer adalah string
    protected $fillable = [
        'name',
        'slug',
        'xp_config_id',
        'custom_domain',
        'custom_domain_verification_token',
        'custom_domain_verified_at',
    ];

    protected $casts = [
        'custom_domain_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Saat membuat model baru, tetapkan UUID
        static::creating(function ($model) 
        {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    public function setNameAttribute($value)
    {
       if ($this->name != $value || $this->slug == '') {
            $this->attributes['name'] = $value;
            $this->attributes['slug'] = $this->createUniqueSlug($value);
        } else {
            $this->attributes['name'] = $value;
        }
    }

    protected function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $baseSlug = $slug;

        $count = 1;
        while (static::where('slug', $slug)->withTrashed()->exists()) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function customSlugs()
    {
        return $this->hasMany(CompanyCustomSlug::class);
    }

    /**
     * Slug publik untuk generate URL: custom slug pertama jika ada, fallback ke slug default.
     */
    public function getPublicSlugAttribute(): string
    {
        return $this->customSlugs()->value('slug') ?? $this->slug;
    }

    /**
     * Resolve company dari URL slug: cek company_custom_slugs dulu, lalu slug default.
     * Mendukung semua custom slug sehingga URL lama tidak 404.
     */
    public static function resolveBySlug(string $slug): ?self
    {
        $custom = CompanyCustomSlug::where('slug', $slug)->first();

        if ($custom) {
            return $custom->company;
        }

        return static::where('slug', $slug)->first();
    }

    public static function resolveBySlugOrFail(string $slug): self
    {
        $company = static::resolveBySlug($slug);

        if (!$company) {
            abort(404);
        }

        return $company;
    }

    /**
     * Resolve company dari hostname request (subdomain ATAU custom domain).
     *
     * @param string $host       Host dari request, mis: "oni.internetrt.com" atau "www.domainsaya.com"
     * @param string $baseDomain Domain utama platform, mis: "internetrt.com"
     */
    public static function resolveByHost(string $host, string $baseDomain): ?self
    {
        $host = strtolower(trim($host));
        $baseDomain = strtolower(trim($baseDomain));

        // Kasus 1: subdomain gratis -> "{slug}.internetrt.com"
        if (str_ends_with($host, ".{$baseDomain}")) {
            $slug = substr($host, 0, -(strlen($baseDomain) + 1));

            // Hindari salah tangkap subdomain sistem lain, mis "www.internetrt.com"
            // atau "app.internetrt.com" -> bukan tenant, biarkan null.
            if ($slug === '' || in_array($slug, self::RESERVED_SUBDOMAINS)) {
                return null;
            }

            return static::resolveBySlug($slug);
        }

        // Kasus 2: domain utama platform sendiri (tanpa subdomain) -> bukan tenant
        if ($host === $baseDomain) {
            return null;
        }

        // Kasus 3: custom domain -> HARUS sudah terverifikasi sebelum dipakai
        return static::where('custom_domain', $host)
            ->whereNotNull('custom_domain_verified_at')
            ->first();
    }

    /**
     * Subdomain yang direservasi buat sistem sendiri, tidak boleh dipakai
     * sebagai slug tenant (mis "www.internetrt.com" untuk landing page utama).
     */
    public const RESERVED_SUBDOMAINS = ['www', 'app', 'api', 'admin', 'mail', 'ftp', 'ns1', 'ns2'];

    public function getPublicUrlAttribute(): string
    {
        if ($this->custom_domain && $this->custom_domain_verified_at) {
            return 'https://' . $this->custom_domain;
        }

        return 'https://' . $this->public_slug . '.' . config('app.tenant_base_domain', 'internetrt.com');
    }

    /**
     * Generate token verifikasi buat custom domain baru, simpan, dan
     * kembalikan instruksi TXT record yang harus ditambahkan customer.
     */
    public function requestCustomDomain(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $token = 'keloola-verify-' . Str::random(32);

        $this->update([
            'custom_domain' => $domain,
            'custom_domain_verification_token' => $token,
            'custom_domain_verified_at' => null, // reset, wajib verifikasi ulang tiap ganti domain
        ]);

        return [
            'domain' => $domain,
            'txt_record_name' => "_keloola-verify.{$domain}",
            'txt_record_value' => $token,
            'cname_target' => config('app.tenant_cname_target', 'tenants.internetrt.com'),
        ];
    }

    /**
     * Cek TXT record via DNS query. Dipanggil dari tombol "Verify" di UI.
     */
    public function verifyCustomDomain(): bool
    {
        if (!$this->custom_domain || !$this->custom_domain_verification_token) {
            return false;
        }

        $records = @dns_get_record("_keloola-verify.{$this->custom_domain}", DNS_TXT);

        foreach ($records ?: [] as $record) {
            if (($record['txt'] ?? null) === $this->custom_domain_verification_token) {
                $this->update(['custom_domain_verified_at' => now()]);
                return true;
            }
        }

        return false;
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function softwares()
    {
        return $this->hasMany(Software::class);
    }

    public function accessibleUsers()
    {
        return $this->belongsToMany(User::class, 'company_user_access');
    }

    /**
     * XP Config yang di-assign ke company ini.
     */
    public function xpConfig()
    {
        return $this->belongsTo(XpConfig::class);
    }

    /**
     * Cek apakah fitur XP aktif untuk company ini.
     */
    public function isXpEnabled(): bool
    {
        return $this->xpConfig !== null && $this->xpConfig->is_enabled;
    }

    public function scopeByCompany($query, $companyId){

        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();
        return $query->whereIn('id', $companyIds);
    }
}
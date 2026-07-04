<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;

class InternetPackageRegion extends Model
{
    protected $table = 'internet_package_regions';

    protected $fillable = [
        'internet_package_id',
        'region_type',   // 'province' | 'city' | 'district' | 'subdistrict'
        'region_id',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'region_id'  => 'integer',
    ];

    // ==================== RELATIONS ====================

    public function internetPackage()
    {
        return $this->belongsTo(InternetPackage::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Nama wilayah (teks saja)
     */
    public function getRegionNameAttribute(): string
    {
        return match ($this->region_type) {
            'province'    => Province::find($this->region_id)?->name ?? '-',
            'city'        => City::find($this->region_id)?->name ?? '-',
            'district'    => District::find($this->region_id)?->name ?? '-',
            'subdistrict' => Subdistrict::find($this->region_id)?->name ?? '-',
            default       => '-',
        };
    }

    /**
     * Label lengkap hierarki wilayah
     * Contoh: "Turikale — Kab. Maros — Sulawesi Selatan"
     */
    public function getRegionLabelAttribute(): string
    {
        return match ($this->region_type) {
            'province' => Province::find($this->region_id)?->name ?? '-',
            'city' => (function () {
                $city = City::with('province')->find($this->region_id);
                return $city ? "{$city->name} — {$city->province?->name}" : '-';
            })(),
            'district' => (function () {
                $d = District::with('city.province')->find($this->region_id);
                return $d ? "{$d->name} — {$d->city?->name} — {$d->city?->province?->name}" : '-';
            })(),
            'subdistrict' => (function () {
                $s = Subdistrict::with('district.city.province')->find($this->region_id);
                return $s ? "{$s->name} — {$s->district?->name} — {$s->district?->city?->name} — {$s->district?->city?->province?->name}" : '-';
            })(),
            default => '-',
        };
    }

    /**
     * Label tipe untuk UI
     */
    public function getRegionTypeLabelAttribute(): string
    {
        return match ($this->region_type) {
            'province'    => 'Provinsi',
            'city'        => 'Kabupaten/Kota',
            'district'    => 'Kecamatan',
            'subdistrict' => 'Kelurahan/Desa',
            default       => '-',
        };
    }

    /**
     * Warna badge Bootstrap
     */
    public function getRegionTypeBadgeColorAttribute(): string
    {
        return match ($this->region_type) {
            'province'    => 'primary',
            'city'        => 'info',
            'district'    => 'success',
            'subdistrict' => 'warning',
            default       => 'secondary',
        };
    }
}

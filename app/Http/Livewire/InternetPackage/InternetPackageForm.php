<?php

namespace App\Http\Livewire\InternetPackage;

use App\Models\InternetPackage;
use App\Models\InternetPackageRegion;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternetPackageForm extends Component
{
    public $packageId;
    public $name;
    public $bandwidth;
    public $type = 'dedicated';
    public $customer_type = 'rumah';
    public $price;
    public $price_nett;
    public $description;
    public $is_active = true;
    public $access_type = 'pppoe';
    public $rate_down_mbps;
    public $rate_up_mbps;
    public $fup_rate_down_mbps = 0;
    public $fup_rate_up_mbps = 0;
    public $quota_bytes = 0;
    public int $session_timeout_seconds = 0;

    // Helper konversi satuan untuk hotspot_voucher
    public string $timeout_value = '0';
    public string $timeout_unit  = 'seconds';
    public string $quota_value   = '0';
    public string $quota_unit    = 'MB';

    // ============= REGION =============
    // Konsep baru: wilayah hanya sebagai register ketersediaan.
    // Harga menggunakan harga paket itu sendiri (bukan per wilayah).
    public $regions = [];

    // State form tambah region baru
    public $region_type = 'city';
    public $region_province_id = null;
    public $region_city_id = null;
    public $region_district_id = null;
    public $region_subdistrict_id = null;
    public $region_price = null;       // nullable — kosong = ikut harga global paket
    public $region_price_nett = null;  // nullable — kosong = ikut harga global paket

    // Dropdown options (dinamis)
    public $regionCities = [];
    public $regionDistricts = [];
    public $regionSubdistricts = [];

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'bandwidth'        => 'required|integer|min:1',
            'type'             => 'required|in:dedicated,broadband',
            'price'            => 'required|numeric|min:0',
            'price_nett'       => 'required|numeric|min:0',
            'description'      => 'nullable|string',
            'is_active'        => 'boolean',
            'access_type'      => 'required|in:pppoe,hotspot,ipoe',
            'rate_down_mbps'   => 'required|integer|min:1',
            'rate_up_mbps'     => 'required|integer|min:1',
            'fup_rate_down_mbps'      => 'nullable|integer|min:0',
            'fup_rate_up_mbps'        => 'nullable|integer|min:0',
            'quota_bytes'             => 'nullable|integer|min:0',
            'session_timeout_seconds' => 'nullable|integer|min:0',

            // Region form
            'region_type'           => 'required|in:province,city,district,subdistrict',
            'region_province_id'    => 'required_if:region_type,province,city,district,subdistrict|nullable|integer',
            'region_city_id'        => 'required_if:region_type,city,district,subdistrict|nullable|integer',
            'region_district_id'    => 'required_if:region_type,district,subdistrict|nullable|integer',
            'region_subdistrict_id' => 'required_if:region_type,subdistrict|nullable|integer',
            'region_price'          => 'nullable|numeric|min:0',
            'region_price_nett'     => 'nullable|numeric|min:0',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $package = InternetPackage::with('regions')->findOrFail($id);
            $this->packageId        = $package->id;
            $this->name             = $package->name;
            $this->bandwidth        = $package->bandwidth;
            $this->type             = $package->type;
            $this->customer_type    = $package->customer_type ?? 'bisnis';
            $this->price            = $package->price;
            $this->price_nett       = $package->price_nett;
            $this->description      = $package->description;
            $this->is_active        = $package->is_active;
            $this->access_type      = $package->access_type;
            $this->rate_down_mbps   = $package->rate_down_mbps ?? $this->bandwidth;
            $this->rate_up_mbps     = $package->rate_up_mbps ?? $this->bandwidth;
            $this->fup_rate_down_mbps = $package->fup_rate_down_mbps;
            $this->fup_rate_up_mbps   = $package->fup_rate_up_mbps;
            $this->quota_bytes              = $package->quota_bytes ?? 0;
            $this->session_timeout_seconds  = $package->session_timeout_seconds ?? 0;

            // Init helper state untuk hotspot
            if ($package->access_type === 'hotspot') {
                $this->initTimeoutHelpers($this->session_timeout_seconds);
                $this->initQuotaHelpers($this->quota_bytes);
            }

            // Load existing regions
            $this->regions = $package->regions->map(function ($r) {
                return [
                    'id'                => $r->id,
                    'region_type'       => $r->region_type,
                    'region_id'         => $r->region_id,
                    'is_active'         => $r->is_active,
                    'region_label'      => $r->region_label,
                    'region_type_label' => $r->region_type_label,
                    'region_type_badge' => $r->region_type_badge_color,
                    'price'             => $r->price      !== null ? (string) $r->price      : '',
                    'price_nett'        => $r->price_nett !== null ? (string) $r->price_nett : '',
                ];
            })->toArray();
        }
    }

    // ============= HOTSPOT TIMEOUT/QUOTA HELPERS =============

    public function updatedAccessType(): void
    {
        if ($this->access_type !== 'hotspot') {
            $this->session_timeout_seconds = 0;
            $this->timeout_value = '0';
            $this->timeout_unit  = 'seconds';
            $this->quota_bytes   = 0;
            $this->quota_value   = '0';
            $this->quota_unit    = 'MB';
        }
    }

    public function updatedTimeoutValue(): void { $this->recalcTimeout(); }
    public function updatedTimeoutUnit(): void  { $this->recalcTimeout(); }
    public function updatedQuotaValue(): void   { $this->recalcQuota(); }
    public function updatedQuotaUnit(): void    { $this->recalcQuota(); }

    private function recalcTimeout(): void
    {
        $val = (int) $this->timeout_value;
        $this->session_timeout_seconds = match ($this->timeout_unit) {
            'minutes' => $val * 60,
            'hours'   => $val * 3600,
            'days'    => $val * 86400,
            default   => $val,
        };
    }

    private function recalcQuota(): void
    {
        $val = (float) $this->quota_value;
        $this->quota_bytes = (int) match ($this->quota_unit) {
            'GB' => $val * 1024 * 1024 * 1024,
            default => $val * 1024 * 1024,
        };
    }

    private function initTimeoutHelpers(int $seconds): void
    {
        if ($seconds <= 0) { return; }
        if ($seconds % 86400 === 0) {
            $this->timeout_value = (string) ($seconds / 86400);
            $this->timeout_unit  = 'days';
        } elseif ($seconds % 3600 === 0) {
            $this->timeout_value = (string) ($seconds / 3600);
            $this->timeout_unit  = 'hours';
        } elseif ($seconds % 60 === 0) {
            $this->timeout_value = (string) ($seconds / 60);
            $this->timeout_unit  = 'minutes';
        } else {
            $this->timeout_value = (string) $seconds;
            $this->timeout_unit  = 'seconds';
        }
    }

    private function initQuotaHelpers(int $bytes): void
    {
        if ($bytes <= 0) { return; }
        $gb = 1024 * 1024 * 1024;
        $mb = 1024 * 1024;
        if ($bytes % $gb === 0) {
            $this->quota_value = (string) ($bytes / $gb);
            $this->quota_unit  = 'GB';
        } else {
            $this->quota_value = (string) round($bytes / $mb, 2);
            $this->quota_unit  = 'MB';
        }
    }

    // ============= CASCADE WILAYAH =============

    public function updatedRegionProvinceId($value)
    {
        $this->region_city_id        = null;
        $this->region_district_id    = null;
        $this->region_subdistrict_id = null;
        $this->regionCities       = $value ? City::where('province_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->regionDistricts    = [];
        $this->regionSubdistricts = [];
    }

    public function updatedRegionCityId($value)
    {
        $this->region_district_id    = null;
        $this->region_subdistrict_id = null;
        $this->regionDistricts    = $value ? District::where('city_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
        $this->regionSubdistricts = [];
    }

    public function updatedRegionDistrictId($value)
    {
        $this->region_subdistrict_id = null;
        $this->regionSubdistricts = $value ? Subdistrict::where('district_id', $value)->orderBy('name')->get(['id', 'name'])->toArray() : [];
    }

    public function updatedRegionType($value)
    {
        $this->region_city_id        = null;
        $this->region_district_id    = null;
        $this->region_subdistrict_id = null;
    }

    // ============= REGION CRUD =============

    public function addRegion()
    {
        $this->validateOnly('region_type');
        $this->validateOnly('region_province_id');

        if (in_array($this->region_type, ['city', 'district', 'subdistrict'])) {
            $this->validateOnly('region_city_id');
        }
        if (in_array($this->region_type, ['district', 'subdistrict'])) {
            $this->validateOnly('region_district_id');
        }
        if ($this->region_type === 'subdistrict') {
            $this->validateOnly('region_subdistrict_id');
        }

        // Tentukan region_id sesuai tipe
        $regionId = match ($this->region_type) {
            'province'    => $this->region_province_id,
            'city'        => $this->region_city_id,
            'district'    => $this->region_district_id,
            'subdistrict' => $this->region_subdistrict_id,
        };

        if (!$regionId) {
            $this->addError('region_province_id', 'Wilayah harus dipilih.');
            return;
        }

        // Cek duplikat
        $isDuplicate = collect($this->regions)->contains(function ($r) use ($regionId) {
            return $r['region_type'] === $this->region_type && (int) $r['region_id'] === (int) $regionId;
        });

        if ($isDuplicate) {
            $this->addError('region_province_id', 'Wilayah ini sudah ditambahkan.');
            return;
        }

        // Buat label
        $regionLabel = match ($this->region_type) {
            'province' => Province::find($this->region_province_id)?->name ?? '-',
            'city'     => (function () {
                $city = City::with('province')->find($this->region_city_id);
                return $city ? "{$city->name} — {$city->province?->name}" : '-';
            })(),
            'district' => (function () {
                $d = District::with('city.province')->find($this->region_district_id);
                return $d ? "{$d->name} — {$d->city?->name} — {$d->city?->province?->name}" : '-';
            })(),
            'subdistrict' => (function () {
                $s = Subdistrict::with('district.city.province')->find($this->region_subdistrict_id);
                return $s ? "{$s->name} — {$s->district?->name} — {$s->district?->city?->name} — {$s->district?->city?->province?->name}" : '-';
            })(),
        };

        $typeBadge = match ($this->region_type) {
            'province'    => 'primary',
            'city'        => 'info',
            'district'    => 'success',
            'subdistrict' => 'warning',
        };

        $typeLabel = match ($this->region_type) {
            'province'    => 'Provinsi',
            'city'        => 'Kabupaten/Kota',
            'district'    => 'Kecamatan',
            'subdistrict' => 'Kelurahan/Desa',
        };

        $this->regions[] = [
            'id'                => null,
            'region_type'       => $this->region_type,
            'region_id'         => (int) $regionId,
            'is_active'         => true,
            'region_label'      => $regionLabel,
            'region_type_label' => $typeLabel,
            'region_type_badge' => $typeBadge,
            'price'             => $this->region_price      !== null && $this->region_price      !== '' ? $this->region_price      : '',
            'price_nett'        => $this->region_price_nett !== null && $this->region_price_nett !== '' ? $this->region_price_nett : '',
        ];

        // Reset form
        $this->region_province_id    = null;
        $this->region_city_id        = null;
        $this->region_district_id    = null;
        $this->region_subdistrict_id = null;
        $this->region_price          = null;
        $this->region_price_nett     = null;
        $this->regionCities       = [];
        $this->regionDistricts    = [];
        $this->regionSubdistricts = [];
    }

    public function removeRegion($index)
    {
        array_splice($this->regions, $index, 1);
        $this->regions = array_values($this->regions);
    }

    public function toggleRegionActive($index)
    {
        $this->regions[$index]['is_active'] = !$this->regions[$index]['is_active'];
    }

    // ============= SAVE =============

    public function save()
    {
        // $this->validate();

        $isHotspot = $this->access_type === 'hotspot';

        $data = [
            'company_id'              => Auth::user()->company_id,
            'name'                    => $this->name,
            'bandwidth'               => $this->bandwidth,
            'type'                    => $this->type,
            'customer_type'           => $this->customer_type,
            'price'                   => $this->price,
            'price_nett'              => $this->price_nett,
            'description'             => $this->description,
            'is_active'               => $this->is_active,
            'access_type'             => $this->access_type,
            'rate_down_mbps'          => $this->rate_down_mbps,
            'rate_up_mbps'            => $this->rate_up_mbps,
            'fup_rate_down_mbps'      => $this->fup_rate_down_mbps,
            'fup_rate_up_mbps'        => $this->fup_rate_up_mbps,
            'quota_bytes'             => $isHotspot ? $this->quota_bytes : 0,
            'session_timeout_seconds' => $isHotspot ? $this->session_timeout_seconds : null,
        ];
        
        $this->validate([
            'name'           => 'required|string|max:255',
            'bandwidth'      => 'required|integer|min:1',
            'type'           => 'required|in:dedicated,broadband',
            'customer_type'  => 'required|in:bisnis,rumah',
            'price'          => 'required|numeric|min:0',
            'price_nett'     => 'required|numeric|min:0',
            'description'    => 'nullable|string',
            'is_active'      => 'boolean',
            'access_type'    => 'required|in:pppoe,hotspot,ipoe',
            'rate_down_mbps' => 'required|integer|min:1',
            'rate_up_mbps'   => 'required|integer|min:1',
        ]);


        DB::transaction(function () use ($data) {
            if ($this->packageId) {
                $package = InternetPackage::find($this->packageId);
                $package->update($data);
            } else {
                $package = InternetPackage::create($data);
                $this->packageId = $package->id;
            }

            // Sync regions: hapus semua lalu insert ulang
            InternetPackageRegion::where('internet_package_id', $package->id)->delete();

            foreach ($this->regions as $r) {
                InternetPackageRegion::create([
                    'internet_package_id' => $package->id,
                    'region_type'         => $r['region_type'],
                    'region_id'           => $r['region_id'],
                    'is_active'           => $r['is_active'] ?? true,
                    // Kosong/null → ikut harga global paket
                    'price'               => isset($r['price']) && $r['price'] !== '' ? $r['price'] : null,
                    'price_nett'          => isset($r['price_nett']) && $r['price_nett'] !== '' ? $r['price_nett'] : null,
                ]);
            }
        });

        return redirect()->route('internet-package.index')->with('success', 'Paket berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.internet-package.internet-package-form', [
            'allProvinces' => Province::orderBy('name')->get(['id', 'name']),
        ])->extends('adminlte::page');
    }
}
<?php

namespace App\Http\Livewire\CoverageService;

use Livewire\Component;
use App\Models\CoverageService;
use App\Models\CoverageServiceOds;

use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\Subdistrict;
use App\Models\OpticalDistribution;
use App\Models\CoverageServiceDistribution;

class CoverageServiceForm extends Component
{
    public $coverageService;
    public $coverageServiceId;
    public $province_id, $province_other;
    public $city_id, $city_other;
    public $district_id, $district_other;
    public $subdistrict_id, $subdistrict_other;
    public $ods = [];
    public $allOds;

    protected $rules = [
        'province_id' => 'required',
        'city_id' => 'required',
        'district_id' => 'required',
        'subdistrict_id' => 'required',
        'province_other' => 'required_if:province_id,other',
        'city_other' => 'required_if:city_id,other',
        'district_other' => 'required_if:district_id,other',
        'subdistrict_other' => 'required_if:subdistrict_id,other',
        'ods' => 'required|array|min:1',
    ];

    
    protected $messages = [
        'province_id.required' => 'Provinsi harus diisi',
        'city_id.required' => 'Kota/Kabupaten harus diisi',
        'district_id.required' => 'Kecamatan harus diisi',
        'subdistrict_id.required' => 'Kelurahan harus diisi',
        'province_other.required_if' => 'Nama provinsi harus diisi',
        'city_other.required_if' => 'Nama kota/kabupaten harus diisi',
        'district_other.required_if' => 'Nama kecamatan harus diisi',
        'subdistrict_other.required_if' => 'Nama kelurahan harus diisi',
        'ods.required' => 'ODP harus diisi',
        'ods.array' => 'ODP harus berupa array',
        'ods.min' => 'Minimal 1 ODP harus dipilih',
    ];

    public function updatedProvinceId($value)
    {
        $this->city_id = null;
        $this->district_id = null;
        $this->subdistrict_id = null;
        
        if($value === 'other') {
            $this->city_id = 'other';
            $this->district_id = 'other';
            $this->subdistrict_id = 'other';
        }
        
        // Reload ODS ketika province berubah
        $this->loadAvailableOds();
    }

    public function updatedCityId($value)
    {
        $this->district_id = null;
        $this->subdistrict_id = null;

        if($value === 'other') {
            $this->district_id = 'other';
            $this->subdistrict_id = 'other';
        }
        
        // Reload ODS ketika city berubah
        $this->loadAvailableOds();
    }

    public function updatedDistrictId($value)
    {
        $this->subdistrict_id = null;
        if($value === 'other') {
            $this->subdistrict_id = 'other';
        }
        
        // Reload ODS ketika district berubah
        $this->loadAvailableOds();
    }
    
    public function mount($id = null)
    {
        if ($id) {
            $this->coverageServiceId = $id;
            $this->coverageService = CoverageService::byCompany(auth()->user()->company_id)->with('coverageServiceOds')->findOrFail($id);
            
            $this->province_id = $this->coverageService->province_id;
            $this->city_id = $this->coverageService->city_id;
            $this->district_id = $this->coverageService->district_id;
            $this->subdistrict_id = $this->coverageService->subdistrict_id;
            
            $this->ods = $this->coverageService->coverageServiceOds->pluck('optical_distribution_id')->toArray();
        }
        
        $this->loadAvailableOds();
    }

    /**
     * Load ODS yang tersedia berdasarkan district yang dipilih
     */
    protected function loadAvailableOds()
    {
        $query = OpticalDistribution::byCompany(auth()->user()->company_id);
        
        // Jika district sudah dipilih dan bukan 'other'
        if ($this->district_id && $this->district_id !== 'other') {
            $query->where(function ($q) {
                // ODS yang belum digunakan sama sekali
                $q->whereDoesntHave('coverageServicesDistribution')
                  // ATAU ODS yang sudah digunakan tapi di Coverage Service dengan district yang SAMA
                  ->orWhereHas('coverageServicesDistribution', function ($subQuery) {
                      $subQuery->whereHas('coverageService', function ($csQuery) {
                          $csQuery->where('district_id', $this->district_id);
                      });
                  });
                  
                // Jika sedang edit, tambahkan ODS yang digunakan di Coverage Service ini
                if ($this->coverageServiceId) {
                    $q->orWhereHas('coverageServicesDistribution', function ($subQuery) {
                        $subQuery->where('coverage_service_id', $this->coverageServiceId);
                    });
                }
            });
        } else {
            // Jika district belum dipilih, tampilkan semua ODS yang belum digunakan
            $query->where(function ($q) {
                $q->whereDoesntHave('coverageServicesDistribution');
                
                // Jika sedang edit, tambahkan ODS yang digunakan di Coverage Service ini
                if ($this->coverageServiceId) {
                    $q->orWhereHas('coverageServicesDistribution', function ($subQuery) {
                        $subQuery->where('coverage_service_id', $this->coverageServiceId);
                    });
                }
            });
        }
        
        $this->allOds = $query->get();
    }

    public function save()
    {
        $this->validate();
        $this->createDistrict();

        $data = [
            'company_id' => auth()->user()->company_id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'district_id' => $this->district_id,
            'subdistrict_id' => $this->subdistrict_id,
        ];
        
        if ($this->coverageServiceId) {
            $coverageService = CoverageService::find($this->coverageServiceId);
            $coverageService->update($data);
        } else {
            $coverageService = CoverageService::create($data);
        }

        // Sync ODS
        if($coverageService->coverageServiceOds()->exists())
        {
            $coverageService->coverageServiceOds()->delete();
        }    
        foreach ($this->ods as $odsId) {
            CoverageServiceDistribution::create([
                'coverage_service_id' => $coverageService->id,
                'optical_distribution_id' => $odsId
            ]);
        }

        session()->flash('message', $this->coverageServiceId ? 'Coverage service updated.' : 'Coverage service created.');
        return redirect()->route('coverage-service.index');
    }

    public function render()
    {
        $provinces = Province::all();
        $cities = $this->province_id ? City::where('province_id', $this->province_id)->get() : [];
        $districts = $this->city_id ? District::where('city_id', $this->city_id)->get() : [];
        $subdistricts = $this->district_id ? Subdistrict::where('district_id', $this->district_id)->get() : [];

        return view('livewire.coverage-service.coverage-service-form', compact('provinces', 'cities', 'districts', 'subdistricts'))->extends('adminlte::page');
    }

    protected function createDistrict()
    {
        if($this->province_id === 'other' && $this->province_other)
        {
            $province = Province::create(['name' => strtoupper($this->province_other)]);
            $this->province_id = $province->id;
        }
        if($this->city_id === 'other' && $this->city_other)
        {
            $city = City::create(['name' => strtoupper($this->city_other), 'province_id' => $this->province_id]);
            $this->city_id = $city->id;
        }
        if($this->district_id === 'other')
        {
            $district = District::create(['name' => strtoupper($this->district_other), 'city_id' => $this->city_id]);
            $this->district_id = $district->id;
        }
        if($this->subdistrict_id === 'other' && $this->subdistrict_other)
        {
            $subdistrict = Subdistrict::create(['name' => strtoupper($this->subdistrict_other), 'district_id' => $this->district_id]);
            $this->subdistrict_id = $subdistrict->id;
        }
    }
}
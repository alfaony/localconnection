<?php

namespace App\Http\Livewire\InternetCustomer;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\CoverageService;
use App\Models\InternetPackage;
use App\Models\InternetCustomer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InternetCustomerForm extends Component
{
    use WithFileUploads;

    public $step = 1;
    
    // Step 1: Alamat & Paket
    public $province_id;
    public $city_id;
    public $district_id;
    public $subdistrict_id;
    public $internet_package_id;
    public $coverageMessage = '';
    public $coverageAvailable = false;
    public $isAvailableArea = false;
    
    // Step 2: Data Pribadi
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $address;
    public $ktp_number;
    public $ktp_photo;
    public $terms = false;
    
    // Step 3: Pembayaran
    public $payment_method;
    public $payment_proof;
    public $selectedPackage;
    
    // Data Tambahan
    public $device_serial_number;
    public $company_id = 1; // Sesuaikan dengan company user

    protected $listeners = ['coverageChecked'];

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
    }

    public function updatedCityId($value)
    {
        $this->district_id = null;
        $this->subdistrict_id = null;

        if($value === 'other') {
            $this->district_id = 'other';
            $this->subdistrict_id = 'other';
        }
    }

    public function updatedDistrictId($value)
    {
        $this->subdistrict_id = null;
        if($value === 'other') 
            {
            $this->subdistrict_id = 'other';
        }
    }

    public function updatedSubdistrictId($value)
    {
        if($value)
        {
            $this->isAvailableArea = CoverageService::where('province_id', $this->province_id)->where('city_id', $this->city_id)->where('district_id', $this->district_id)->where('subdistrict_id', $this->subdistrict_id)->first() ? true : false;
        }
    }

    public function mount()
    {
        $this->provinces = Province::all();
        $this->internetPackages = InternetPackage::all();
    }

    public function render()
    {
        return view('livewire.internet-customer.internet-customer-form', [
            'provinces' => Province::all(),
            'cities' => $this->province_id ? City::where('province_id', $this->province_id)->get() : [],
            'districts' => $this->city_id ? District::where('city_id', $this->city_id)->get() : [],
            'subdistricts' => $this->district_id ? Subdistrict::where('district_id', $this->district_id)->get() : [],
            'internetPackages' => InternetPackage::all(),
        ])->extends('layouts.app_customer');
    }

    // Step Navigation
    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validateStep1();
            $this->checkCoverage();
        } elseif ($this->step === 2) {
            $this->validateStep2();
        } elseif ($this->step === 3) {
            $this->validateStep3();
            $this->submitForm();
        }
    }

    public function prevStep()
    {
        $this->step--;
    }

    // Step 1 Validation and Logic
    private function validateStep1()
    {
        $this->validate([
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:cities,id',
            'district_id' => 'required|exists:districts,id',
            'subdistrict_id' => 'required|exists:subdistricts,id',
            'internet_package_id' => 'required|exists:internet_packages,id',
        ]);
    }

    public function checkCoverage()
    {
        $coverage = CoverageService::where('subdistrict_id', $this->subdistrict_id)->exists();
        
        if ($coverage) {
            $this->coverageAvailable = true;
            $this->coverageMessage = 'Layanan tersedia di wilayah Anda!';
            $this->step++;
        } else {
            $this->coverageMessage = 'Maaf, layanan belum tersedia di wilayah Anda';
            $this->coverageAvailable = false;
        }
    }

    // Step 2 Validation
    private function validateStep2()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'address' => 'required|min:10',
            'ktp_number' => 'required|digits:16',
            'ktp_photo' => 'required|image|max:2048',
            'terms' => 'accepted',
        ]);
        
        $this->step++;
        $this->selectedPackage = InternetPackage::find($this->internet_package_id);
    }

    // Step 3 Validation and Submission
    private function validateStep3()
    {
        $this->validate([
            'payment_method' => 'required|in:transfer,qris,e-wallet',
            'payment_proof' => 'required_if:payment_method,transfer|image|max:2048',
        ]);
    }

    private function submitForm()
    {
        // Simpan file KTP
        $ktpPath = $this->ktp_photo->store('public/ktp_photos');
        
        // Simpan bukti pembayaran jika ada
        $paymentProofPath = null;
        if ($this->payment_method === 'transfer' && $this->payment_proof) {
            $paymentProofPath = $this->payment_proof->store('public/payment_proofs');
        }

        // Simpan data pelanggan
        InternetCustomer::create([
            'company_id' => $this->company_id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'district_id' => $this->district_id,
            'subdistrict_id' => $this->subdistrict_id,
            'technical_user_id' => $this->assignTechnicalUser(),
            'internet_package_id' => $this->internet_package_id,
            'name' => $this->name,
            'address' => $this->address,
            'payment_method' => $this->payment_method,
            'payment_proof' => $paymentProofPath ? Storage::url($paymentProofPath) : null,
            'ktp_number' => $this->ktp_number,
            'ktp_photo' => Storage::url($ktpPath),
            'is_paid' => false,
            'status' => 'pending',
            'amount_paid' => $this->selectedPackage->price,
            'device_serial_number' => $this->device_serial_number,
        ]);

        // Reset form
        $this->reset();
        session()->flash('success', 'Pendaftaran berhasil!');
        $this->step = 1;
    }

    private function assignTechnicalUser()
    {
        // Logika penugasan technical user berdasarkan wilayah
        // Contoh sederhana:
        return \App\Models\User::where('role', 'technical')
            ->where('province_id', $this->province_id)
            ->first()->id;
    }
}

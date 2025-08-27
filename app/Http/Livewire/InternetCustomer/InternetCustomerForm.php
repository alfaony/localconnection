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
use App\Models\Company;
use App\Models\UserCustomer;
use App\Models\Role;
use App\Models\PartnershipAgreement;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\PartnershipAgreementType;
use App\Models\AgreementSignature;
use App\Models\InternetCustomerPurchase;


use App\Schemas\RoleSchema;
use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\InboxHelper;

class InternetCustomerForm extends Component
{
    use WithFileUploads;

    public $step = 1;
    
    // Step 1: Alamat & Paket
    public $provinces;
    // public $cities;
    // public $districts;
    // public $subdistricts;
    public $internetPackages;

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
    public $phone_number;
    public $email;
    public $password;
    public $password_confirmation;
    public $address;
    public $ktp_number;
    public $ktp_photo;
    public $terms = false;
    public $agreement;
    
    // Step 3: Pembayaran
    public $nama_bank;
    public $holder_name;
    public $account_number;
    public $branch_office;
    public $payment_method;
    public $payment_proof;
    public $selectedPackage;
    public $selectedBankId;
    public $bankAccounts = [];
    public $code;

    // Step 4 
    public $signature;
    public $signaturePreview; // Tambahkan untuk preview
    public $agreeTerms = false;
    
    // Data Tambahan
    public $device_serial_number;
    public $company_id = null; // Sesuaikan dengan company user
    public $company_name = null; // Sesuaikan dengan company user
    public $company_slug = null; // Sesuaikan dengan company user
    public $internet_customer_id = null;

    // Di dalam class InternetCustomerForm
    public $hasFreeMonthsPromo = false;
    public $freeMonthsDetails = null;
    public $paymentStartMonth = null;
    public $start_billing_date = null;
    public $end_billing_date = null;


    protected $rules = 
    [
        'signature' => 'nullable|string',
    ];

    protected $listeners = [
        'coverageChecked',
        'saveSignatureStep4' => 'handleSaveSignature' // Tambahkan listener baru
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
            $this->subdistrict_id = $value;
            $this->isAvailableArea = CoverageService::where('province_id', $this->province_id)->where('city_id', $this->city_id)->where('district_id', $this->district_id)->where('subdistrict_id', $this->subdistrict_id)->first() ? true : false;
        }
    }

    public function saveSignature($signatureData)
    {
        if($signatureData) 
            {
            $this->dispatchBrowserEvent('signature-saved'); // Panggil event JS
        }
        $this->signature = $signatureData;
        $this->generateAgreementPreviewJson();
    }

        // Method untuk handle setelah signature disimpan
    public function saveSignatureAndProceed($signatureData)
    {
        $this->signature = $signatureData;
        $this->validate([
            'signature' => 'required',
            // 'agreeTerms' => 'accepted'
        ]);
        $this->step++; // Lanjut ke step 5
    }

    public function handleSaveSignature()
    {        
        $this->validate([
            'signature' => 'required',
        ]);
        
        $this->submitForm();
    }
    public function mount($companyId)
    {
        $companyId = Company::where('slug', $companyId)->first();

        if(!$companyId) 
            {
            return redirect()->route('public.error', ['code' => 403])->with([
                'title' => 'Akses Ditolak',
                'message' => 'Terdapat Kesalahan pada Form Pendaftaran, Silahkan Hubungi Admin',
                'icon' => 'fas fa-ban'
            ]);
        }

        $this->company_id = $companyId->id;
        $this->company_name = $companyId->name;
        $this->company_slug = $companyId->slug;
        $this->provinces = Province::all();
        $this->internetPackages = InternetPackage::all();
    }

    public function render()
    {
        return view('livewire.internet-customer.internet-customer-form', [
            'settingCompany' => SettingCompany::byCompany($this->company_id)->where('menu','bank')->orWhere('menu','profile')->get()->pluck('field_value','field_title'),
            'agreement' => new PartnershipAgreement(),
            'provinces' => Province::all(),
            'cities' => $this->province_id ? City::where('province_id', $this->province_id)->get() : [],
            'districts' => $this->city_id ? District::where('city_id', $this->city_id)->get() : [],
            'subdistricts' => $this->district_id ? Subdistrict::where('district_id', $this->district_id)->get() : [],
            'internetPackages' => InternetPackage::where('company_id',$this->company_id)->where('is_active', true)->get(),
        ])->extends('layouts.app_customer');
    }

    // Step Navigation
    public function nextStep()
    {
        $this->generateAgreementPreviewJson();

        if ($this->step === 1) {
            $this->validateStep1();
            $this->checkCoverage();
            
            // Cek promo setelah validasi step 1
        } elseif ($this->step === 2) {
            $this->validateStep2();
            $this->checkPromo();
        } elseif ($this->step === 3) {
            $this->validateStep3();
        } elseif ($this->step === 4) {
            $this->handleSaveSignature();
        } elseif ($this->step === 5) {  
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
            'email' => 'required|email|unique:user_customers,email',
            // 'password' => 'required|min:8|confirmed',
            'phone_number' => 'required|string',
            'address' => 'required|min:10',
            'ktp_number' => 'required|digits:16',
            'ktp_photo' => 'required|image|max:2048',
        ]);
        
        $this->step++;
        $this->selectedPackage = InternetPackage::find($this->internet_package_id);
    }

    // Step 3 Validation and Submission
    private function validateStep3()
    {
        if (!$this->hasFreeMonthsPromo) 
        {
            $this->validate([
                'payment_method' => 'required|in:transfer,qris,e-wallet',
                'payment_proof' => 'required_if:payment_method,transfer|image|max:2048',
                'nama_bank' => 'required_if:payment_method,transfer',
                'holder_name' => 'required_if:payment_method,transfer',
                'account_number' => 'required_if:payment_method,transfer',
                'branch_office' => 'required_if:payment_method,transfer',
            ]);
        }

        $this->generateAgreementPreviewJson();
        $this->step++;
    }

    // private function validateStep4()
    // {
    //     $this->dispatchBrowserEvent('save-signature');

    //     $this->validate([
    //         'signature' => 'required',
    //     ]);
        
    //     // Simpan signature
        
    //     // Lanjutkan ke step berikutnya setelah signature disimpan
    //     $this->step++;
    // }
    

    private function submitForm()
    {
        // dd($this->signature);
        // Simpan file KTP
        $ktpPath = $this->ktp_photo->store('ktps', 'public');
        
        // Simpan bukti pembayaran jika ada
        $paymentProofPath = null;
        if ($this->payment_method === 'transfer' && $this->payment_proof) {
            $paymentProofPath = $this->payment_proof->store('public/payment_proofs');
        }
        
        DB::beginTransaction();
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $this->signature, $type)) 
                {
                $imageType = $type[1]; // Dapatkan tipe gambar (png, jpeg, dll)
                $data = substr($this->signature, strpos($this->signature, ',') + 1);
                $data = base64_decode($data);
                
                // Validasi decode berhasil
                if ($data === false) {
                    throw new \Exception('Base64 decode failed');
                }
                
                $signaturePath = 'signatures/' . uniqid() . '.' . $imageType;
                Storage::disk('public')->put($signaturePath, $data);
            } else {
                throw new \Exception('Invalid image data URL');
            }
            // Simpan data pelanggan
            $internetCustomer = InternetCustomer::create([
                'company_id' => $this->company_id,
                'province_id' => $this->province_id,
                'city_id' => $this->city_id,
                'district_id' => $this->district_id,
                'code' => $this->code(),
                'subdistrict_id' => $this->subdistrict_id,
                'internet_package_id' => $this->internet_package_id,
                'name' => $this->name,
                'address' => $this->address,
                'ktp_number' => $this->ktp_number,
                'ktp_photo' => Storage::url($ktpPath),
                'is_paid' => false,
                'status' => ParamSchema::WAITING_PAYMENT_CONFIRMATION,
            ]);
            
            $agreement = $this->createPartnershipAgreement($ktpPath);
        
            $userCustomer = UserCustomer::create([
                'start_billing_date' => $this->start_billing_date,
                'name' => $this->name,
                'phone_number' => $this->phone_number,
                'email' => $this->email,
                'company_id' => $this->company_id,
                'role' => Role::where('name',RoleSchema::CUSTOMER_INTERNET)->first()->id,
                'start_billing_date' => $this->start_billing_date,
                'end_billing_date' => $this->end_billing_date,
                // 'password' => Hash::make($this->password),
            ]);


            $internetCustomer->update([
                'partnership_agreement_id' => $agreement->id,
                'user_customer_id' => $userCustomer->id
            ]);

            if($this->freeMonthsDetails && $this->freeMonthsDetails->type === ParamSchema::PROMO_FREE_MONTH)
            {
                $internetCustomer->update([
                    'promo_id' => $this->freeMonthsDetails->id
                ]);
                $this->installation($internetCustomer);
            }else
            {
                if($this->payment_method == 'transfer')
                    $internetCustomerPurchase = InternetCustomerPurchase::create([
                    'amount_paid' => $this->selectedPackage->price_nett,
                    'internet_customer_id' => $internetCustomer->id,
                    'payment_method' => $this->payment_method,
                    'payment_proof' => $paymentProofPath ? Storage::url($paymentProofPath) : null,
                ]);

                $userFinance = User::whereHas('role.permissions', function ($q) 
                {
                    $q->where('method', 'as_finance')
                    ->where('table', 'internet_customers');
                })
                ->where(function ($q) use ($internetCustomer) {
                    $q->where('company_id', $internetCustomer->company_id)
                    ->orWhereHas('accessibleCompanies', function ($sub) use ($internetCustomer) {
                        $sub->where('companies.id', $internetCustomer->company_id);
                    });
                })->get();

                if($userFinance)
                {
                    $from = User::where('company_id', $internetCustomer->company_id)
                    ->where(function ($query) {
                        $query->whereHas('role', function ($q)  {
                            $q->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                        });
                    })
                    ->first();
                    
                    $message = "Pelanggan dengan kode ".$internetCustomer->code." telah berhasil mendaftar. Silakan periksa detail pendaftaran dan tindak lanjuti.";
                    $directUrl = route('internet-customer.index');
                    foreach($userFinance as $finance)
                    {
                        $this->sentInbox($finance->id, $from->id, $message, $directUrl);
                    }   
                }
            }

            $this->internet_customer_id = $internetCustomer->id;
            DB::commit();
            $this->step = 5;

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            // dd($th);
            session()->flash('error', 'Terjadi kesalahan: Konfirmasikan ke Admin');
            $this->step = 1;
        }
    }

    private function code()
    {
        $prefix = 'PLG'; // Ganti sesuai kebutuhan
        do {
            $randomCode = $prefix . '-' . strtoupper(Str::random(8));
            $exists = InternetCustomer::withTrashed()
                ->where('code', $randomCode)
                ->exists();
        } while ($exists);

        $this->code = $randomCode;
        return $randomCode;
    }

     private function generateAgreementPreviewJson()
    {
        $this->agreement = new PartnershipAgreement();
        $this->agreement->fields = json_encode([
            'nama' => $this->name,
            'ktp' => $this->ktp_number,
            'alamat' => $this->address,
            'telephon' => $this->phone_number,
            'email' => $this->email,
            'nama_bank' => $this->nama_bank,
            'holder_name' => $this->holder_name,
            'account_number' => $this->account_number, // kalau belum ada
            'branch_office' => $this->branch_office,
            'alamat_pemasangan' => $this->address,
            'jangka_waktu' => '-',
            'nama_paket' => $this->selectedPackage->name ?? '',
            'detail_paket' => $this->selectedPackage->description ?? '',
        ]);
    }

    private function createPartnershipAgreement($ktpPath)
    {
        try {
            $letter_number = PartnershipAgreement::where('company_id',$this->company_id)->withTrashed()->max('letter_number') + 1;
            $date = Carbon::now()->format('m/Y');
            $numberResult = $letter_number.'/'.$date;
            $type = PartnershipAgreementType::where('name_format', ParamSchema::PERJANJIAN_INTERNET)->first();
            
            $dataArray = json_encode([
                'nama' => $this->name,
                'ktp' => $this->ktp_number,
                'alamat' => $this->address,
                'telephon' => $this->phone_number,
                'email' => $this->email,
                'nama_bank' => $this->nama_bank,
                'holder_name' => $this->holder_name,
                'account_number' => $this->account_number, // kalau belum ada
                'branch_office' => $this->branch_office,
                'alamat_pemasangan' => $this->address,
                'jangka_waktu' => '-',
                'nama_paket' => $this->selectedPackage->name ?? '',
                'detail_paket' => $this->selectedPackage->description ?? '',
            ]);
            $admin = User::with('role')
                    ->whereHas('role', fn ($query) => $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR]))
                    ->where('company_id', $this->company_id)
                    ->first();
    
            $signaturePath = null;
            if ($this->signature) 
                {
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $this->signature));
                $signaturePath = 'signatures/' . uniqid() . '.png';
                file_put_contents(storage_path('app/public/' . $signaturePath), $imageData);
            }

            $data['partnership_agreement_type_id'] = $type->id;
            $data['status'] = ParamSchema::SIGNATURE;
            $data['letter_number'] = $letter_number;
            $data['date_agreement'] = Carbon::now()->format('Y-m-d');
            $data['number_result'] = $numberResult;
            $data['fields'] = $dataArray;
            $data['company_id'] = $this->company_id;
            $data['user_created_id'] = $admin->id;
            $data['user_updated_id'] = $admin->id;
    
            $partnershipAgreement = PartnershipAgreement::create($data);
    
            // Proceed to store the rest of the data (e.g., save the KTP path, signature path, etc.)
            // Example: save the data in the database
            $agreement = new AgreementSignature();
            $agreement->partnership_agreement_id = $partnershipAgreement->id;
            $agreement->signature = $signaturePath;
            $agreement->image_ktp = $ktpPath ?? null;
            $agreement->order = $partnershipAgreement->getNextSignatureNumber();
            $agreement->save();

            $partnershipAgreement->status = ParamSchema::ONREVIEW;
            $partnershipAgreement->save();

            return $partnershipAgreement;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function sentInbox($to,$from, $message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            $from,
            $message, 
            $directUrl
        );
        return true;
    }

    private function checkPromo()
    {
        $this->hasFreeMonthsPromo = false;
        $this->freeMonthsDetails = null;
        $this->paymentStartMonth = null;
        $this->start_billing_date = Carbon::now()->format('Y-m-d');
        $this->end_billing_date = Carbon::now()->addDays(config('service.internet_custom.end_billing_of_days'))->format('Y-m-d');

        // Pastikan paket sudah dipilih
        if ($this->internet_package_id) {
            $package = InternetPackage::find($this->internet_package_id);
            
            if ($package && $package->promo_active) {
                $activePromo = $package->promo_active;
                
                if ($activePromo && $activePromo->type === 'free_months') {
                    $this->hasFreeMonthsPromo = true;
                    $this->freeMonthsDetails = $activePromo;
                    
                    // Tentukan kapan pembayaran dimulai
                    $now = now();
                    $registerDate = Carbon::parse($activePromo->register_date);

                    if (now()->lt($registerDate)) {
                        // Pendaftaran sebelum register_date: bayar bulan depan
                        $this->paymentStartMonth = now()->addMonth($activePromo->value)->format('F Y');
                        $this->start_billing_date = now()->addMonth($activePromo->value)->firstOfMonth()->format('Y-m-d');
                        $this->end_billing_date = now()->addMonth($activePromo->value)->firstOfMonth()->addDays(config('service.internet_custom.end_billing_of_days'))->format('Y-m-d');
                    } else {
                        // Pendaftaran pada/ setelah register_date: bayar 2 bulan dari sekarang
                        $this->paymentStartMonth = now()->addMonths($activePromo->value + 1)->format('F Y');
                        $this->start_billing_date = now()->addMonths($activePromo->value + 1)->firstOfMonth()->format('Y-m-d');
                        $this->end_billing_date = now()->addMonths($activePromo->value + 1)->firstOfMonth()->addDays(config('service.internet_custom.end_billing_of_days'))->format('Y-m-d');
                    }
                }
            }
        }
    }

    private function installation($customer)
    {
        try {
            $customer->update([
                'status' => ParamSchema::PROCESS_INSTALLATION,
            ]);
            
            $userTechnical = optional($customer->subdistrict?->coverageService?->coverageServiceOds)
            ->pluck('ods.user_assign_id')
            ->unique()
            ->all();
            
            $from = User::where('company_id', $customer->company_id)
                    ->where(function ($query) {
                        $query->whereHas('role', function ($q)  {
                            $q->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                        });
                    })
                    ->first();

            if(count($userTechnical) > 0)
            {
                $message = "Pembayaran Langganan Internet Untuk Kode ".$customer->code." Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
                $directUrl = route('internet-customer.show',$customer->id);
                foreach($userTechnical as $tech)
                {
                    $this->sentInbox($tech,$from->id, $message, $directUrl);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

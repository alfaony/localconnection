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
use App\Services\XenditService;
use Illuminate\Support\Facades\Log;

class InternetCustomerForm extends Component
{
    use WithFileUploads;

    public $step = 1;
    
    // Step 1: Alamat & Paket
    public $provinces;
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
    public $ktp_photo_path; // Tambahan: simpan path sementara
    public $terms = false;
    public $agreement;
    
    // Step 3: Tanda Tangan
    public $signature;
    public $signaturePreview;
    public $agreeTerms = false;
    
    // Step 4: Pembayaran
    public $payment_months = 1;
    public $payment_method = null;
    public $payment_proof;
    public $payment_proof_path; // Tambahan: simpan path sementara
    public $selectedPackage;
    public $nama_bank;
    public $holder_name;
    public $account_number;
    public $branch_office;
    
    // Perhitungan
    public $monthlyPrice = 0;
    public $subtotal = 0;
    public $discountPercentage = 0;
    public $discountAmount = 0;
    public $totalAmount = 0;
    
    // Data Tambahan
    public $device_serial_number;
    public $company_id = null;
    public $company_name = null;
    public $company_slug = null;
    public $internet_customer_id = null;
    public $purchase_id = null;
    public $code;

    // Promo
    public $hasFreeMonthsPromo = false;
    public $freeMonthsDetails = null;
    public $paymentStartMonth = null;
    public $start_billing_date = null;
    public $end_billing_date = null;

    // Xendit
    public $xenditActive = false;

    protected $rules = [
        'signature' => 'nullable|string',
    ];

    protected $listeners = [
        'coverageChecked',
        'saveSignatureStep3' => 'handleSaveSignature'
    ];

    // FIX 1: Handle file upload completion
    public function updatedKtpPhoto($value)
    {
        // Validasi segera setelah upload
        $this->validateOnly('ktp_photo', [
            'ktp_photo' => 'required|image|max:2048',
        ]);
        
        // Simpan sementara untuk menghindari validasi ulang
        if ($value) {
            $this->ktp_photo_path = $value->store('temp');
        }
    }

    public function updatedPaymentProof($value)
    {
        // Validasi segera setelah upload
        $this->validateOnly('payment_proof', [
            'payment_proof' => 'required|image|max:2048',
        ]);
        
        // Simpan sementara
        if ($value) {
            $this->payment_proof_path = $value->store('temp');
        }
    }

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
        if($value === 'other') {
            $this->subdistrict_id = 'other';
        }
    }

    public function updatedSubdistrictId($value)
    {
        if($value) {
            $this->subdistrict_id = $value;
            $this->isAvailableArea = CoverageService::where('province_id', $this->province_id)
                ->where('city_id', $this->city_id)
                ->where('district_id', $this->district_id)
                ->where('subdistrict_id', $this->subdistrict_id)
                ->exists();
        }
    }

    public function updatedPaymentMonths($value)
    {
        $this->payment_months = max(1, min(24, (int)$value));
        $this->calculatePayment();
    }

    // FIX 2: Method untuk save signature dari JavaScript
    public function saveSignature($signatureData)
    {
        if($signatureData) {
            $this->signature = $signatureData;
            $this->dispatchBrowserEvent('signature-saved', ['success' => true]);
        }
        $this->generateAgreementPreviewJson();
    }

    // FIX 2: Method untuk clear signature
    public function clearSignature()
    {
        $this->signature = null;
        $this->dispatchBrowserEvent('signature-cleared');
    }

    public function handleSaveSignature()
    {        
        $this->validate([
            'signature' => 'required',
        ], [
            'signature.required' => 'Tanda tangan wajib diisi. Silakan berikan tanda tangan Anda.'
        ]);
        
        $this->step++;
    }

    public function mount($companyId)
    {
        $company = Company::where('slug', $companyId)->first();

        if(!$company) {
            return redirect()->route('public.error', ['code' => 403])->with([
                'title' => 'Akses Ditolak',
                'message' => 'Terdapat Kesalahan pada Form Pendaftaran, Silahkan Hubungi Admin',
                'icon' => 'fas fa-ban'
            ]);
        }

        $this->company_id = $company->id;
        $this->company_name = $company->name;
        $this->company_slug = $company->slug;
        $this->provinces = Province::whereHas('provinceCoverages')->get();
        $this->internetPackages = InternetPackage::where('company_id', $this->company_id)
            ->where('is_active', true)
            ->get();

        // Check Xendit status
        try {
            $xenditService = new XenditService($this->company_id);
            $this->xenditActive = $xenditService->isActive();
        } catch (\Exception $e) {
            $this->xenditActive = false;
            Log::warning('Xendit not configured for company', [
                'company_id' => $this->company_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.internet-customer.internet-customer-form', [
            'settingCompany' => SettingCompany::byCompany($this->company_id)
                ->where('menu','bank')
                ->orWhere('menu','profile')
                ->get()
                ->pluck('field_value','field_title'),
            'agreement' => new PartnershipAgreement(),
            'provinces' => Province::whereHas('provinceCoverages')->get(),
            'cities' => $this->province_id ? City::where('province_id', $this->province_id)->whereHas('cityCoverages')->get() : [],
            'districts' => $this->city_id ? District::where('city_id', $this->city_id)->whereHas('districtCoverages')->get() : [],
            'subdistricts' => $this->district_id ? Subdistrict::where('district_id', $this->district_id)->whereHas('subdistrictCoverages')->get() : [],
            'internetPackages' => InternetPackage::where('company_id',$this->company_id)->where('is_active', true)->get(),
        ])->extends('layouts.app_customer');
    }

    protected function calculatePayment()
    {
        if (!$this->selectedPackage) return;

        $this->monthlyPrice = $this->selectedPackage->price_nett;
        
        $calculation = InternetCustomerPurchase::calculateTotal(
            $this->monthlyPrice,
            $this->payment_months
        );

        $this->subtotal = $calculation['subtotal'];
        $this->discountPercentage = $calculation['discount_percentage'];
        $this->discountAmount = $calculation['discount_amount'];
        $this->totalAmount = $calculation['total'];
    }

    protected function calculateSubscriptionPeriod($months)
    {
        $startDate = $this->start_billing_date 
            ? Carbon::parse($this->start_billing_date)
            : now()->startOfDay();

        $endDate = $startDate->copy()->addMonths($months)->subDay();

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    public function nextStep()
    {
        $this->generateAgreementPreviewJson();

        if ($this->step === 1) {
            $this->validateStep1();
            $this->checkCoverage();
        } elseif ($this->step === 2) {
            $this->validateStep2();
            $this->checkPromo();
        } elseif ($this->step === 3) {
            $this->handleSaveSignature();
        } elseif ($this->step === 4) {
            $this->validateStep4();
        }
    }

    public function prevStep()
    {
        $this->step--;
    }

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

    private function validateStep2()
    {
        // FIX 1: Validasi yang lebih robust untuk file upload
        $rules = [
            'name' => 'required|min:3',
            'email' => [
                'required',
                'email',
                'unique:user_customers,email',
            ],
            'phone_number' => 'required|string',
            'address' => 'required|min:10',
            'ktp_number' => [
                'required',
                'digits:16',
            ],
        ];

        // Cek apakah file sudah di-upload sebelumnya
        if ($this->ktp_photo_path) {
            // File sudah ada di temp, skip validasi
            $rules['ktp_photo'] = 'nullable';
        } else {
            // Validasi file baru
            $rules['ktp_photo'] = 'required|image|max:2048';
        }

        $this->validate($rules, [
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau hubungi admin jika ini adalah akun Anda.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'ktp_number.digits' => 'Nomor KTP harus 16 digit.',
            'ktp_number.required' => 'Nomor KTP wajib diisi.',
            'ktp_photo.required' => 'Foto KTP wajib diupload.',
            'ktp_photo.image' => 'File KTP harus berupa gambar.',
            'ktp_photo.max' => 'Ukuran file KTP maksimal 2MB.',
        ]);
        
        $this->step++;
        $this->selectedPackage = InternetPackage::find($this->internet_package_id);
        $this->calculatePayment();
    }

    private function validateStep4()
    {
        if (!$this->hasFreeMonthsPromo) {
            $rules = [
                'payment_method' => 'required|in:manual_transfer,xendit',
                'payment_months' => 'required|integer|min:1|max:24',
            ];

            if ($this->payment_method === 'manual_transfer') {
                // FIX 1: Cek apakah sudah ada path tersimpan
                if ($this->payment_proof_path) {
                    $rules['payment_proof'] = 'nullable';
                } else {
                    $rules['payment_proof'] = 'required|image|max:2048';
                }
                
                $rules['nama_bank'] = 'required';
                $rules['holder_name'] = 'required';
                $rules['account_number'] = 'required';
                $rules['branch_office'] = 'required';
            }

            $this->validate($rules);
        }

        $this->submitForm();
    }

    private function submitForm()
    {
        // FIX 1: Gunakan path yang sudah disimpan atau upload baru
        $ktpPath = null;
        if ($this->ktp_photo_path) {
            // Pindahkan dari temp ke ktps
            $ktpPath = str_replace('temp/', 'ktps/', $this->ktp_photo_path);
            Storage::move($this->ktp_photo_path, $ktpPath);
        } elseif ($this->ktp_photo) {
            $ktpPath = $this->ktp_photo->store('ktps');
        }
        
        $paymentProofPath = null;
        if ($this->payment_method === 'manual_transfer') {
            if ($this->payment_proof_path) {
                // Pindahkan dari temp ke payment_proofs
                $paymentProofPath = str_replace('temp/', 'payment_proofs/', $this->payment_proof_path);
                Storage::move($this->payment_proof_path, $paymentProofPath);
            } elseif ($this->payment_proof) {
                $paymentProofPath = $this->payment_proof->store('payment_proofs');
            }
        }
        
        DB::beginTransaction();
        try {
            // Save signature
            $signaturePath = null;
            if ($this->signature && (preg_match('/^data:image\/(\w+);base64,/', $this->signature, $type))) {
                $imageType = $type[1];
                $data = substr($this->signature, strpos($this->signature, ',') + 1);
                $data = base64_decode($data);
                
                if ($data === false) {
                    throw new \Exception('Base64 decode failed');
                }
                
                $signaturePath = 'signatures/' . uniqid() . '.' . $imageType;
                Storage::put($signaturePath, $data);
            }

            // Create internet customer
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
                'ktp_photo' => $ktpPath,
                'is_paid' => false,
                'status' => $this->hasFreeMonthsPromo 
                    ? ParamSchema::PROCESS_INSTALLATION 
                    : ParamSchema::WAITING_PAYMENT_CONFIRMATION,
            ]);
            
            $agreement = $this->createPartnershipAgreement($ktpPath, $signaturePath);
        
            $userCustomer = UserCustomer::create([
                'start_billing_date' => $this->start_billing_date,
                'name' => $this->name,
                'phone_number' => $this->phone_number,
                'email' => $this->email,
                'company_id' => $this->company_id,
                'role' => Role::where('name',RoleSchema::CUSTOMER_INTERNET)->first()->id,
                'start_billing_date' => $this->start_billing_date,
                'end_billing_date' => $this->end_billing_date,
            ]);

            $internetCustomer->update([
                'partnership_agreement_id' => $agreement->id,
                'user_customer_id' => $userCustomer->id
            ]);

            // Handle payment based on promo
            if($this->freeMonthsDetails && $this->freeMonthsDetails->type === ParamSchema::PROMO_FREE_MONTH) {
                $internetCustomer->update([
                    'promo_id' => $this->freeMonthsDetails->id
                ]);
                $this->installation($internetCustomer);
            } else {
                // Create purchase record
                $subscriptionPeriod = $this->calculateSubscriptionPeriod($this->payment_months);
                
                $internetCustomerPurchase = InternetCustomerPurchase::create([
                    'internet_customer_id' => $internetCustomer->id,
                    'amount_paid' => $this->totalAmount,
                    'payment_months' => $this->payment_months,
                    'period_start' => $subscriptionPeriod['start'],
                    'period_end' => $subscriptionPeriod['end'],
                    'total_before_discount' => $this->subtotal,
                    'discount_amount' => $this->discountAmount,
                    'payment_method' => $this->payment_method,
                    'payment_proof' => $paymentProofPath,
                    'generate_coupons' => true,
                ]);

                $this->purchase_id = $internetCustomerPurchase->id;

                // Handle Xendit payment
                if ($this->payment_method === 'xendit') {
                    $this->processXenditPayment($internetCustomerPurchase, $internetCustomer);
                } else {
                    // Manual transfer - notify finance
                    $this->notifyFinanceTeam($internetCustomer);
                }
            }

            $this->internet_customer_id = $internetCustomer->id;
            
            // Clean up temp files
            if ($this->ktp_photo_path && Storage::exists($this->ktp_photo_path)) {
                Storage::delete($this->ktp_photo_path);
            }
            if ($this->payment_proof_path && Storage::exists($this->payment_proof_path)) {
                Storage::delete($this->payment_proof_path);
            }
            
            DB::commit();
            $this->step = 5;

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Registration form submission failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            session()->flash('error', 'Terjadi kesalahan: ' . $th->getMessage());
            $this->step = 1;
        }
    }

    protected function processXenditPayment($purchase, $customer)
    {
        try {
            $xenditService = new XenditService($customer->company_id);

            if (!$xenditService->isActive()) {
                throw new \Exception('Pembayaran Xendit tidak tersedia untuk saat ini.');
            }

            $subscriptionPeriod = $this->calculateSubscriptionPeriod($this->payment_months);

            $result = $xenditService->createInvoice($purchase, $customer, [
                'payment_months' => $this->payment_months,
                'total_amount' => $this->totalAmount,
                'discount_amount' => $this->discountAmount,
                'subscription_period' => $subscriptionPeriod
            ]);

            if ($result['success']) {
                $invoice = $result['invoice'];

                $purchase->update([
                    'xendit_raw_response' => json_encode($invoice),
                ]);

                Log::info('Xendit invoice created successfully', [
                    'invoice_id' => $invoice['id'],
                    'purchase_id' => $purchase->id,
                    'customer_code' => $customer->code
                ]);

                // Redirect to Xendit payment page
                $this->dispatchBrowserEvent('redirect-to-xendit', [
                    'url' => $invoice['invoice_url']
                ]);

            } else {
                throw new \Exception('Gagal membuat invoice pembayaran: ' . $result['message']);
            }

        } catch (\Exception $e) {
            Log::error('Xendit payment processing failed', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->id
            ]);
            
            session()->flash('warning', 'Pembayaran digital tidak tersedia. Silakan gunakan transfer manual.');
        }
    }

    private function code()
    {
        $prefix = 'PLG';
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
            'account_number' => $this->account_number,
            'branch_office' => $this->branch_office,
            'alamat_pemasangan' => $this->address,
            'jangka_waktu' => '-',
            'nama_paket' => $this->selectedPackage->name ?? '',
            'detail_paket' => $this->selectedPackage->description ?? '',
        ]);
    }

    private function createPartnershipAgreement($ktpPath, $signaturePath = null)
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
                'account_number' => $this->account_number,
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
    
            $agreementSignature = new AgreementSignature();
            $agreementSignature->partnership_agreement_id = $partnershipAgreement->id;
            $agreementSignature->signature = $signaturePath;
            $agreementSignature->image_ktp = $ktpPath;
            $agreementSignature->order = $partnershipAgreement->getNextSignatureNumber();
            $agreementSignature->save();

            $partnershipAgreement->status = ParamSchema::ONREVIEW;
            $partnershipAgreement->save();

            return $partnershipAgreement;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    protected function notifyFinanceTeam($internetCustomer)
    {
        $userFinance = User::whereHas('role.permissions', function ($q) {
            $q->where('method', 'as_finance')
              ->where('table', 'internet_customers');
        })
        ->where(function ($q) use ($internetCustomer) {
            $q->where('company_id', $internetCustomer->company_id)
              ->orWhereHas('accessibleCompanies', function ($sub) use ($internetCustomer) {
                  $sub->where('companies.id', $internetCustomer->company_id);
              });
        })->get();

        if($userFinance->isNotEmpty()) {
            $from = User::where('company_id', $internetCustomer->company_id)
                ->whereHas('role', function ($q) {
                    $q->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                })
                ->first();
            
            $message = "Pelanggan dengan kode ".$internetCustomer->code." telah berhasil mendaftar untuk {$this->payment_months} bulan. Silakan periksa detail pendaftaran.";
            $directUrl = route('internet-customer.show', $internetCustomer->id);
            
            foreach($userFinance as $finance) {
                $this->sentInbox($finance->id, $from->id, $message, $directUrl);
            }   
        }
    }

    private function sentInbox($to,$from, $message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }

    private function checkPromo()
    {
        $this->hasFreeMonthsPromo = false;
        $this->freeMonthsDetails = null;
        $this->paymentStartMonth = null;
        $this->start_billing_date = Carbon::now()->format('Y-m-d');
        $this->end_billing_date = Carbon::now()->addDays(config('services.internet_custom.end_billing_of_days'))->format('Y-m-d');

        if ($this->internet_package_id) {
            $package = InternetPackage::find($this->internet_package_id);
            
            if ($package && $package->promo_active) {
                $activePromo = $package->promo_active;
                
                if ($activePromo && $activePromo->type === 'free_months') {
                    $this->hasFreeMonthsPromo = true;
                    $this->freeMonthsDetails = $activePromo;
                    
                    $now = now();
                    $registerDate = Carbon::parse($activePromo->register_date);

                    if (now()->lt($registerDate)) {
                        $this->paymentStartMonth = now()->addMonth($activePromo->value)->format('F Y');
                        $this->start_billing_date = now()->addMonth($activePromo->value)->firstOfMonth()->format('Y-m-d');
                        $this->end_billing_date = now()->addMonth($activePromo->value)->firstOfMonth()->addDays(config('services.internet_custom.end_billing_of_days'))->format('Y-m-d');
                    } else {
                        $this->paymentStartMonth = now()->addMonths($activePromo->value + 1)->format('F Y');
                        $this->start_billing_date = now()->addMonths($activePromo->value + 1)->firstOfMonth()->format('Y-m-d');
                        $this->end_billing_date = now()->addMonths($activePromo->value + 1)->firstOfMonth()->addDays(config('services.internet_custom.end_billing_of_days'))->format('Y-m-d');
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
                    ->whereHas('role', function ($q) {
                        $q->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                    })
                    ->first();

            if(count($userTechnical) > 0) {
                $message = "Pembayaran Langganan Internet Untuk Kode ".$customer->code." Telah di Setujui. Silahkan segera lakukan Pemasangan";
                $directUrl = route('internet-customer.show',$customer->id);
                foreach($userTechnical as $tech) {
                    $this->sentInbox($tech,$from->id, $message, $directUrl);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
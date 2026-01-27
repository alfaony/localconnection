<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;
use App\Models\InternetPackage;
use App\Models\InternetCustomer;
use App\Models\UserCustomer;
use App\Models\Company;
use App\Models\Role;
use App\Schemas\RoleSchema;
use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\CoverageService;
use App\Models\PartnershipAgreement;
use App\Models\PartnershipAgreementType;
use App\Models\User;
use App\Models\AgreementSignature;
use App\Helpers\InboxHelper;
use App\Models\InternetCustomerPurchase;
use Illuminate\Support\Facades\Log;

class ImportInternetCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internet-customer:import 
                            {file : Nama file Excel di storage/internet_customer/} 
                            {slug : Slug perusahaan}
                            {type : create atau update (default: create, nil = create)}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data internet customer dari file Excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('file');
        $companySlug = $this->argument('slug');
        $type = $this->argument('type') ?? "create";

        
        $filePath = public_path('internet_customer/' . $filename);
        
        // Validasi file exists
        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return;
        }
        
        // Validasi company exists
        $company = Company::where('slug', $companySlug)->first();
        if (!$company) {
            $this->error("Perusahaan dengan slug '{$companySlug}' tidak ditemukan");
            return;
        }
        
        $this->info("Memproses file: {$filename}");
        $this->info("Perusahaan: {$company->name}");
        
        try {
            // Load spreadsheet
            $rows = $this->readCSV($filePath);
            
            $successCount = 0;
            $failures = [];
            
            $rowNumber = 1;
            foreach ($rows as $row) {
                // $rowNumber = $rowNumber + 2; // +2 karena header di row 1 dan array dimulai dari 0
                $rowNumber = trim($row["No"]) ?? null;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row data
                $data = [
                    'province' => trim($row["Provinsi"]) ?? null,
                    'city' => trim($row["Kota/Kabupaten"]) ?? null,
                    'district' => trim($row["Kecamatan"] ?? null),
                    'subdistrict' => trim($row["Kelurahan"] ?? null),
                    'package_name' => trim($row["Paket Internet"] ?? null),
                    'price' => trim($row["Harga"] ?? null),
                    'name' => trim($row["Nama Lengkap"] ?? null),
                    'phone' => trim($row["Nomer Telpon"] ?? null),
                    'email' => trim($row["Email"] ?? null),
                    'address' => trim($row["Alamat"] ?? null),
                ];

                
                // Validate required fields
                // if (empty($data['email'])) {
                //     $failures[] = [
                //         'row' => $rowNumber,
                //         'email' => $data['email'],
                //         'reason' => 'Email tidak boleh kosong'
                //     ];
                //     continue;
                // }
                
                // Check if email already exists
                
                // Process the row
                if($type == "create")
                {
                    if (!empty($data['email'])) {
                        $existingCustomer = UserCustomer::where('email', $data['email'])->first();
                        if ($existingCustomer) {
                            $failures[] = [
                                'row' => $rowNumber,
                                'email' => $data['email'],
                                'reason' => 'Email sudah terdaftar'
                            ];
                            continue;
                        }
                    } else {
                        $data['email'] = null;
                    }

                    if (!empty($data['phone'])) {
                        $existingCustomer = UserCustomer::where('phone_number', $data['phone'])->first();
                        if ($existingCustomer) {
                            $failures[] = [
                                'row' => $rowNumber,
                                'phone' => $data['phone'],
                                'reason' => 'Nomor telepon sudah terdaftar'
                            ];
                            continue;
                        }
                    } else {
                        $data['phone'] = null;
                    }

                    if (!empty($data['name']) && (empty($data['phone']) && empty($data['email']))) {
                        $existingCustomer = UserCustomer::where('name', $data['name'])->first();
                        if ($existingCustomer) {
                            $failures[] = [
                                'row' => $rowNumber,
                                'name' => $data['name'],
                                'reason' => 'Nama sudah terdaftar'
                            ];
                            continue;
                        }
                    }

                    
                    $result = $this->processRow($data, $company, $rowNumber);
                }else if($type == "update")
                {
                    $result = $this->processRowUpdate($data, $company, $rowNumber);
                }
                else if($type == "check")
                {
                    dd($data);
                }
                
                if ($result['success']) {
                    $successCount++;
                    $this->info("Row {$rowNumber} berhasil diproses: {$data['email']}");
                } else {
                    $failures[] = [
                        'row' => $rowNumber,
                        'email' => $data['email'],
                        'reason' => $result['reason']
                    ];
                    $this->error("Row {$rowNumber} gagal: {$result['reason']}");
                }
            }
            
            // Display results
            $this->info("\nHasil Import:");
            $this->info("✅ Berhasil: {$successCount}");
            $this->info("❌ Gagal: " . count($failures));
            
            if (!empty($failures)) {
                $this->info("\nDetail yang gagal:");
                $this->table(['Row', 'Email', 'Alasan'], $failures);
            }
            
        } catch (\Exception $e) {
            // dd($e);
            $this->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
    
    /**
     * Process a single row from the Excel file
     */
    private function processRow($data, $company, $rowNumber)
    {
        DB::beginTransaction();
        
        try {
            // Cari provinsi
            $province = Province::where('name', 'like', '%' . $data['province'] . '%')->first();
            if (!$province) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Provinsi tidak ditemukan: ' . $data['province']
                ];
            }
            
            // Cari kota
            $city = City::where('province_id', $province->id)
                        // ->where('name', 'like', '%' . $data['city'] . '%')
                        ->where('name', $data['city'])
                        ->first();
            if (!$city) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kota tidak ditemukan: ' . $data['city']
                ];
            }
            
            // Cari kecamatan
            $district = District::where('city_id', $city->id)
                            //    ->where('name', 'like', '%' . $data['district'] . '%')
                                ->where('name',$data['district'])
                               ->first();
            if (!$district) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kecamatan tidak ditemukan: ' . $data['district']
                ];
            }
            
            // Cari kelurahan
            $subdistrict = Subdistrict::where('district_id', $district->id)
                                    //  ->where('name', 'like', '%' . $data['subdistrict'] . '%')
                                    ->where('name',$data['subdistrict'])
                                     ->first();
            if (!$subdistrict) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Kelurahan tidak ditemukan: ' . $data['subdistrict']
                ];
            }
            
            // Cari paket internet
            $internetPackage = InternetPackage::where('name', 'like', '%' . $data['package_name'] . '%')
                                             ->first();
            if (!$internetPackage || $data['package_name'] == '') {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Paket internet tidak ditemukan: ' . $data['package_name']
                ];
            }
            
            // Generate kode pelanggan
            // $code = $this->generateCustomerCode();


            $promoData = $this->checkPromo($internetPackage->id);
            
            // Buat user customer
            $userCustomer = UserCustomer::create([
                'name' => $data['name'],
                'phone_number' => $data['phone'],
                'email' => $data['email'],
                'company_id' => $company->id,
                'role_id' => Role::where('name', RoleSchema::CUSTOMER_INTERNET)->first()->id,
                'start_billing_date' => $promoData['start_billing_date'],
                'end_billing_date' => $promoData['end_billing_date'],
            ]);
            

            $checkCoverage = $this->checkCoverage($subdistrict->id);
            if (!$checkCoverage) 
            {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Coverage service tidak tersedia berdasarkan: ' . $data['subdistrict'] . ' - ' . $data['district'] . ' - ' . $data['city'] . ' - ' . $data['province']
                ];
            }

            // Buat internet customer
            $internetCustomer = InternetCustomer::create([
                'company_id' => $company->id,
                'province_id' => $province->id,
                'city_id' => $city->id,
                'district_id' => $district->id,
                'subdistrict_id' => $subdistrict->id,
                'internet_package_id' => $internetPackage->id,
                'user_customer_id' => $userCustomer->id,
                // 'code' => $code,
                'name' => $data['name'],
                'address' => $data['address'],
                'ktp_number' => null,
                'ktp_photo' => null,
                'is_paid' => $promoData['has_free_months'], // Jika ada promo, langsung dianggap sudah bayar
                'status' => $promoData['has_free_months'] ? ParamSchema::CUSTOMER_EXISTING : ParamSchema::WAITING_PAYMENT_CONFIRMATION,
            ]);

             $agreement = $this->createPartnershipAgreement(
                $company, 
                $internetCustomer, 
                $data, 
                null,
                null,
            );

            // Buat user customer
            // $userCustomer = UserCustomer::create([
            //     'name' => $data['name'],
            //     'phone_number' => $data['phone'],
            //     'email' => $data['email'],
            //     'company_id' => $company->id,
            //     'role' => Role::where('name', RoleSchema::CUSTOMER_INTERNET)->first()->id,
            // ]);

            // Update relasi
            $internetCustomer->update([
                'partnership_agreement_id' => $agreement->id,
                'user_customer_id' => $userCustomer->id
            ]);

            // Jika ada promo, simpan data promo
            if ($promoData['has_free_months']) {
                $internetCustomer->update([
                    'promo_id' => $promoData['free_months_details']->id
                ]);
                $this->processInstallation($internetCustomer);
            } else 
            {
                // Simpan data pembayaran jika tidak ada promo
                $this->processPayment($internetCustomer, $internetPackage);
                
                // Kirim notifikasi ke finance
                // $this->sendFinanceNotifications($internetCustomer);c
            }
            
            DB::commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error($e);
            return [
                'success' => false,
                'reason' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    private function processPayment($internetCustomer, $internetPackage)
    {
        $internetCustomerPurchase = InternetCustomerPurchase::create([
            'internet_package_id' => $internetPackage->id,
            'amount_paid' => $internetPackage->price_nett,
            'internet_customer_id' => $internetCustomer->id,
            'payment_method' => "transfer",
        ]);
    }

    private function processRowUpdate($data, $company, $rowNumber)
    {
        try {
            DB::beginTransaction();
            $existingCustomer = UserCustomer::where('email', $data['email'])->first();
            $existingInternetCustomer = InternetCustomer::where('name', $data['name'])
                                        ->when($existingCustomer, function ($query) use ($existingCustomer) {
                                            $query->where('user_customer_id', $existingCustomer->id);
                                        })
                                        ->first();

            if (!$existingInternetCustomer && !$existingCustomer) 
            {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Pelanggan tidak ditemukan: ' . $data['name']
                ];
            }else
            {
                if(!$existingCustomer)
                {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'reason' => 'Pelanggan tidak ditemukan: ' . $data['name']
                    ];
                }else
                {
                    $existingInternetCustomer = InternetCustomer::where('user_customer_id', $existingCustomer->id)->first();
                }
            }

             $internetPackage = InternetPackage::where('name', 'like', '%' . $data['package_name'] . '%')
                                             ->first();
            if (!$internetPackage || $data['package_name'] == '') {
                DB::rollBack();
                return [
                    'success' => false,
                    'reason' => 'Paket internet tidak ditemukan: ' . $data['package_name']
                ];
            }
            
            // Generate kode pelanggan
            // dd($internetPackage, $data['package_name']);

            $promoData = $this->checkPromo($internetPackage->id);

            $existingInternetCustomer->update([
                'internet_package_id' => $internetPackage->id,
            ]);

            if($existingCustomer && $promoData['has_free_months'])
            {
                $existingCustomer->update([
                    'start_billing_date' => $promoData['start_billing_date'],
                    'end_billing_date' => $promoData['end_billing_date'],
                ]);  
            }
            DB::commit();
            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error($e);
            return [
                'success' => false,
                'reason' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate unique customer code
     */
    private function generateCustomerCode()
    {
        $prefix = 'PLG';
        do {
            $randomCode = $prefix . '-' . strtoupper(Str::random(8));
            $exists = InternetCustomer::withTrashed()
                ->where('code', $randomCode)
                ->exists();
        } while ($exists);

        return $randomCode;
    }

    private function checkPromo($packageId)
    {
        $hasFreeMonthsPromo = false;
        $freeMonthsDetails = null;
        $paymentStartMonth = null;
        $startBillingDate = Carbon::now()->format('Y-m-d');
        $endBillingDate = Carbon::now()->addDays(config('services.internet_custom.end_billing_of_days', 30))->format('Y-m-d');

        // Pastikan paket sudah dipilih
        if ($packageId) {
            $package = InternetPackage::find($packageId);
            
            if ($package) {
                // Cek apakah paket memiliki promo aktif
                $activePromo = $package->promo_active;
                
                if ($activePromo && $activePromo->type === 'free_months') {
                    $hasFreeMonthsPromo = true;
                    $freeMonthsDetails = $activePromo;
                    
                    // Tentukan kapan pembayaran dimulai
                    $now = now();
                    $registerDate = Carbon::parse($activePromo->register_date);

                    if ($now->lt($registerDate)) {
                        // Pendaftaran sebelum register_date: bayar bulan depan
                        $paymentStartMonth = $now->addMonth($activePromo->value)->format('F Y');
                        $startBillingDate = $now->copy()->addMonth($activePromo->value)->firstOfMonth()->format('Y-m-d');
                        $endBillingDate = $now->copy()->addMonth($activePromo->value)->firstOfMonth()->addDays(config('services.internet_custom.end_billing_of_days', 30))->format('Y-m-d');
                    } else {
                        // Pendaftaran pada/ setelah register_date: bayar 2 bulan dari sekarang
                        $paymentStartMonth = $now->addMonths($activePromo->value + 1)->format('F Y');
                        $startBillingDate = $now->copy()->addMonths($activePromo->value + 1)->firstOfMonth()->format('Y-m-d');
                        $endBillingDate = $now->copy()->addMonths($activePromo->value + 1)->firstOfMonth()->addDays(config('services.internet_custom.end_billing_of_days', 30))->format('Y-m-d');
                    }
                }
            }
        }

        return [
            'has_free_months' => $hasFreeMonthsPromo,
            'free_months_details' => $freeMonthsDetails,
            'payment_start_month' => $paymentStartMonth,
            'start_billing_date' => $startBillingDate,
            'end_billing_date' => $endBillingDate,
        ];
    }

    public function checkCoverage($subdistrictId)
    {
        $coverage = CoverageService::where('subdistrict_id', $subdistrictId)->exists();
        return $coverage ? true : false;
    }

    private function readCSV($filePath)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error("File not found or unreadable: $filePath");
            return [];
        }

        $header = null;
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    private function createPartnershipAgreement($company, $internetCustomer, $step2Data, $step3Data, $signatureData)
    {
        $letter_number = PartnershipAgreement::where('company_id', $company->id)
            ->withTrashed()
            ->max('letter_number') + 1;
            
        $date = Carbon::now()->format('m/Y');
        $numberResult = $letter_number . '/' . $date;
        
        $type = PartnershipAgreementType::where('name_format', ParamSchema::PERJANJIAN_INTERNET)->first();
        
        $admin = User::with('role')
            ->whereHas('role', function ($query) {
                $query->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR]);
            })
            ->where('company_id', $company->id)
            ->first();

        // Simpan tanda tangan
        $signaturePath = null;
        if ($signatureData) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
            $signaturePath = 'signatures/' . uniqid() . '.png';
            Storage::put($signaturePath, $imageData);
        }

        if(isset($step2Data['ktp_photo']))
        {
            $ktpPath = $step2Data['ktp_photo'] ? $step2Data['ktp_photo']->store('ktps') : null;   
        }else
        {
            $ktpPath = null;
        }

        // Buat perjanjian
        $agreement = PartnershipAgreement::create([
            'partnership_agreement_type_id' => $type->id,
            'status' => ParamSchema::SIGNATURE,
            'letter_number' => $letter_number,
            'date_agreement' => Carbon::now()->format('Y-m-d'),
            'number_result' => $numberResult,
            'fields' => json_encode([
                'nama' => $step2Data['name'],
                'ktp' => null,
                'alamat' => $step2Data['address'],
                'telephon' => $step2Data['phone'],
                'email' => $step2Data['email'],
                'nama_bank' => $step3Data['nama_bank'] ?? null,
                'holder_name' => $step3Data['holder_name'] ?? null,
                'account_number' => $step3Data['account_number'] ?? null,
                'branch_office' => $step3Data['branch_office'] ?? null,
                'alamat_pemasangan' => $step2Data['address'],
                'jangka_waktu' => '-',
                'nama_paket' => $internetCustomer->internetPackage->name ?? '',
                'detail_paket' => $internetCustomer->internetPackage->description ?? '',
            ]),
            'company_id' => $company->id,
            'user_created_id' => $admin->id,
            'user_updated_id' => $admin->id,
        ]);

        // Simpan tanda tangan perjanjian
        $agreementSignature = AgreementSignature::create([
            'partnership_agreement_id' => $agreement->id,
            'signature' => null,
            'image_ktp' => null,
            'order' => 1,
        ]);

        $agreement->status = ParamSchema::ONREVIEW;
        $agreement->save();

        return $agreement;
    }

    private function processInstallation($customer)
    {
        try {
            $customer->update([
                'status' => ParamSchema::CUSTOMER_EXISTING,
            ]);
            
            $userTechnical = optional($customer->subdistrict->coverageService->coverageServiceOds)
                ->pluck('ods.user_assign_id')
                ->unique()
                ->all();
                
            $from = User::where('company_id', $customer->company_id)
                ->where(function ($query) {
                    $query->whereHas('role', function ($q) {
                        $q->whereIn('name', [RoleSchema::ROOT, RoleSchema::ADMIN]);
                    });
                })
                ->first();

            if (count($userTechnical) > 0) {
                $message = "Pembayaran Langganan Internet Untuk Kode " . $customer->code . " Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
                $directUrl = route('internet-customer.show', $customer->id);
                
                foreach ($userTechnical as $tech) {
                    $this->sendInbox($tech, $from->id, $message, $directUrl);
                }
            }
        } catch (\Exception $e) {
            // dd($e);
            throw $e;
        }
    }

     private function sendInbox($to, $from, $message, $directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }
}
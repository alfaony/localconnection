<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
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
use Carbon\Carbon;
use App\Models\CoverageService;
use App\Models\PartnershipAgreement;
use App\Models\PartnershipAgreementType;
use App\Models\User;
use App\Models\AgreementSignature;
use App\Helpers\InboxHelper;
use App\Models\InternetCustomerPurchase;

class ImportSkamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvData;
    protected $userId;
    protected $companyId;
    protected $batchId;
    protected $responseUrl;
    protected $callbackToken;

    /**
     * Create a new job instance.
     */
    public function __construct($csvData, $userId, $companyId, $batchId, $responseUrl, $callbackToken)
    {
        $this->csvData = $csvData;
        $this->userId = $userId;
        $this->companyId = $companyId;
        $this->batchId = $batchId;
        $this->responseUrl = $responseUrl;
        $this->callbackToken = $callbackToken;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $total = count($this->csvData) - 1; // Exclude header
        $successCount = 0;
        $errors = [];

        Log::info("Starting Internet Customer import", [
            'batch_id' => $this->batchId,
            'company_id' => $this->companyId,
            'total_rows' => $total
        ]);

        try {
            // Get company
            $company = Company::find($this->companyId);
            if (!$company) {
                throw new \Exception("Company not found: {$this->companyId}");
            }

            foreach ($this->csvData as $index => $row) {
                // Skip header row
                if ($index === 0) {
                    continue;
                }

                $rowNumber = $index + 1;

                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Map row data
                    $data = [
                        'province' => trim($row["Provinsi"] ?? ''),
                        'city' => trim($row["Kota/Kabupaten"] ?? ''),
                        'district' => trim($row["Kecamatan"] ?? ''),
                        'subdistrict' => trim($row["Kelurahan"] ?? ''),
                        'package_name' => trim($row["Paket Internet"] ?? ''),
                        'price' => trim($row["Harga"] ?? ''),
                        'name' => trim($row["Nama Lengkap"] ?? ''),
                        'phone' => trim($row["Nomer Telpon"] ?? ''),
                        'email' => trim($row["Email"] ?? ''),
                        'address' => trim($row["Alamat"] ?? ''),
                    ];

                    // Validate and check for duplicates
                    // Logic: Email atau phone yang sama diperbolehkan HANYA jika namanya berbeda
                    // Jika email/phone DAN nama sama -> reject (duplicate)
                    
                    if (!empty($data['email'])) {
                        $existingCustomer = UserCustomer::where('email', $data['email'])
                            ->where('name', $data['name'])
                            ->first();
                        if ($existingCustomer) {
                            $errors[] = [
                                'row' => $rowNumber,
                                'email' => $data['email'],
                                'name' => $data['name'],
                                'message' => 'Email dan nama yang sama sudah terdaftar'
                            ];
                            continue;
                        }
                    } else {
                        $data['email'] = null;
                    }

                    if (!empty($data['phone'])) {
                        $existingCustomer = UserCustomer::where('phone_number', $data['phone'])
                            ->where('name', $data['name'])
                            ->first();
                        if ($existingCustomer) {
                            $errors[] = [
                                'row' => $rowNumber,
                                'phone' => $data['phone'],
                                'name' => $data['name'],
                                'message' => 'Nomor telepon dan nama yang sama sudah terdaftar'
                            ];
                            continue;
                        }
                    } else {
                        $data['phone'] = null;
                    }

                    // Jika tidak ada email dan phone, cek nama saja
                    if (!empty($data['name']) && (empty($data['phone']) && empty($data['email']))) {
                        $existingCustomer = UserCustomer::where('name', $data['name'])->first();
                        if ($existingCustomer) {
                            $errors[] = [
                                'row' => $rowNumber,
                                'name' => $data['name'],
                                'message' => 'Nama sudah terdaftar'
                            ];
                            continue;
                        }
                    }

                    // Process the row
                    $result = $this->processRow($data, $company, $rowNumber);
                    
                    if ($result['success']) {
                        $successCount++;
                        Log::info("Row {$rowNumber} processed successfully", [
                            'batch_id' => $this->batchId,
                            'email' => $data['email']
                        ]);
                    } else {
                        $errors[] = [
                            'row' => $rowNumber,
                            'email' => $data['email'],
                            'message' => $result['reason']
                        ];
                        Log::error("Row {$rowNumber} failed", [
                            'batch_id' => $this->batchId,
                            'reason' => $result['reason']
                        ]);
                    }

                } catch (\Exception $e) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'message' => $e->getMessage()
                    ];
                    
                    Log::error("Error processing row {$rowNumber}", [
                        'batch_id' => $this->batchId,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ]);
                }
            }

            $status = 'completed';

        } catch (\Exception $e) {
            $status = 'failed';
            $errors[] = [
                'row' => 'System',
                'message' => $e->getMessage()
            ];
            
            Log::error("Import job failed", [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage()
            ]);
        }

        // Send webhook callback
        $this->sendWebhookCallback($status, $total, $successCount, $errors);
    }

    /**
     * Process a single row from the CSV file
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
                               ->where('name', $data['district'])
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
                                     ->where('name', $data['subdistrict'])
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
                'name' => $data['name'],
                'address' => $data['address'],
                'ktp_number' => null,
                'ktp_photo' => null,
                'is_paid' => $promoData['has_free_months'],
                'status' => $promoData['has_free_months'] ? ParamSchema::CUSTOMER_EXISTING : ParamSchema::WAITING_PAYMENT_CONFIRMATION,
            ]);

            $agreement = $this->createPartnershipAgreement(
                $company, 
                $internetCustomer, 
                $data, 
                null,
                null,
            );

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
            }
            
            DB::commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Process row error", [
                'batch_id' => $this->batchId,
                'row' => $rowNumber,
                'error' => $e->getMessage()
            ]);
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

    private function checkPromo($packageId)
    {
        $hasFreeMonthsPromo = false;
        $freeMonthsDetails = null;
        $paymentStartMonth = null;
        $startBillingDate = Carbon::now()->format('Y-m-d');
        $endBillingDate = Carbon::now()->addDays(config('services.internet_custom.end_billing_of_days', 30))->format('Y-m-d');

        if ($packageId) {
            $package = InternetPackage::find($packageId);
            
            if ($package) {
                $activePromo = $package->promo_active;
                
                if ($activePromo && $activePromo->type === 'free_months') {
                    $hasFreeMonthsPromo = true;
                    $freeMonthsDetails = $activePromo;
                    
                    $now = now();
                    $registerDate = Carbon::parse($activePromo->register_date);

                    if ($now->lt($registerDate)) {
                        $paymentStartMonth = $now->addMonth($activePromo->value)->format('F Y');
                        $startBillingDate = $now->copy()->addMonth($activePromo->value)->firstOfMonth()->format('Y-m-d');
                        $endBillingDate = $now->copy()->addMonth($activePromo->value)->firstOfMonth()->addDays(config('services.internet_custom.end_billing_of_days', 30))->format('Y-m-d');
                    } else {
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
            throw $e;
        }
    }

    private function sendInbox($to, $from, $message, $directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, $from, $message, $directUrl);
        return true;
    }

    /**
     * Send webhook callback to response URL
     */
    private function sendWebhookCallback($status, $total, $successful, $errors)
    {
        try {
            $payload = [
                'batch_id' => $this->batchId,
                'status' => $status,
                'total_rows' => $total,
                'successful' => $successful,
                'failed' => count($errors),
                'errors' => $errors,
                'processed_at' => now()->toIso8601String()
            ];

            Log::info("Sending webhook callback", [
                'batch_id' => $this->batchId,
                'url' => $this->responseUrl,
                'payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->callbackToken,
                'Content-Type' => 'application/json'
            ])->post($this->responseUrl, $payload);

            if ($response->successful()) {
                Log::info("Webhook callback sent successfully", [
                    'batch_id' => $this->batchId,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::error("Webhook callback failed", [
                    'batch_id' => $this->batchId,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to send webhook callback", [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error("Import job failed completely", [
            'batch_id' => $this->batchId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Send failure webhook
        $this->sendWebhookCallback('failed', 0, 0, [
            [
                'row' => 'System',
                'message' => $exception->getMessage()
            ]
        ]);
    }
}

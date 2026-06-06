<?php

namespace App\Jobs;

use App\Models\AddressPool;
use App\Models\City;
use App\Models\CoverageService;
use App\Models\District;
use App\Models\ImportProgress;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerInstallation;
use App\Models\InternetPackage;
use App\Models\JobsProvisioning;
use App\Models\Province;
use App\Models\Role;
use App\Models\Router;
use App\Models\Subdistrict;
use App\Models\UserCustomer;
use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ImportRegisterAndActivateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 600;

    protected array   $csvData;
    protected string  $userId;
    protected string  $companyId;
    protected string  $batchId;
    protected string  $opticalDistributionId;
    protected ?string $groupId;

    public function __construct(
        array   $csvData,
        string  $userId,
        string  $companyId,
        string  $batchId,
        string  $opticalDistributionId,
        ?string $groupId = null
    ) {
        $this->csvData               = $csvData;
        $this->userId                = $userId;
        $this->companyId             = $companyId;
        $this->batchId               = $batchId;
        $this->opticalDistributionId = $opticalDistributionId;
        $this->groupId               = $groupId;
    }

    public function handle(): void
    {
        $total     = count($this->csvData) - 1;
        $processed = 0;
        $imported  = 0;
        $errors    = [];

        $this->updateProgress($processed, $total, $imported, $errors);

        foreach ($this->csvData as $index => $row) {
            if ($index === 0) {
                continue;
            }

            // ── COLUMN MAPPING ──────────────────────────────────────────────
            // provinsi, kota, kecamatan, kelurahan, paket, nama, phone, email,
            // alamat, username, password, serial_number, router,
            // start_billing_date, end_billing_date, pppoe_pool, grouping
            $province         = trim($row[0]  ?? '');
            $city             = trim($row[1]  ?? '');
            $district         = trim($row[2]  ?? '');
            $subdistrict      = trim($row[3]  ?? '');
            $packageName      = trim($row[4]  ?? '');
            $name             = trim($row[5]  ?? '');
            $phone            = trim($row[6]  ?? '');
            $email            = trim($row[7]  ?? '');
            $address          = trim($row[8]  ?? '');
            $username         = trim($row[9]  ?? '');
            $plainPassword    = trim($row[10] ?? '');
            $serialNumber     = trim($row[11] ?? '');
            $routerName       = trim($row[12] ?? '');
            $startBillingDate = trim($row[13] ?? '');
            $endBillingDate   = trim($row[14] ?? '');
            $poolName         = trim($row[15] ?? '') ?: null;
            $grouping         = trim($row[16] ?? '') ?: null;

            $identifier = $email ?: ($phone ?: ($name ?: 'Row ' . ($index + 1)));

            try {
                DB::beginTransaction();

                $this->processRow(
                    $province, $city, $district, $subdistrict, $packageName,
                    $name, $phone, $email, $address,
                    $username, $plainPassword, $serialNumber, $routerName,
                    $startBillingDate, $endBillingDate, $poolName, $grouping,
                    $identifier
                );

                DB::commit();
                $imported++;

            } catch (\Exception $e) {
                DB::rollBack();

                $errors[] = [
                    'row'     => $index + 1,
                    'message' => $e->getMessage(),
                    'data'    => $identifier,
                ];

                Log::warning('ImportRegisterAndActivateJob: baris ' . ($index + 1), [
                    'batch_id' => $this->batchId,
                    'error'    => $e->getMessage(),
                ]);
            }

            $processed++;
            $this->updateProgress($processed, $total, $imported, $errors);
        }

        $this->updateProgress($processed, $total, $imported, $errors);

        Log::info('ImportRegisterAndActivateJob selesai', [
            'batch_id' => $this->batchId,
            'total'    => $total,
            'imported' => $imported,
            'failed'   => count($errors),
        ]);
    }

    protected function processRow(
        string  $provinceName,
        string  $cityName,
        string  $districtName,
        string  $subdistrictName,
        string  $packageName,
        string  $name,
        string  $phone,
        string  $email,
        string  $address,
        string  $username,
        string  $plainPassword,
        string  $serialNumber,
        string  $routerName,
        string  $startBillingDate,
        string  $endBillingDate,
        ?string $poolName,
        ?string $grouping,
        string  $identifier
    ): void {
        // ── VALIDATION ───────────────────────────────────────────────────────
        if (empty($name)) {
            throw new \Exception("Nama pelanggan wajib diisi");
        }

        if (empty($email) && empty($phone)) {
            throw new \Exception("Email atau nomor telepon wajib diisi (minimal salah satu)");
        }

        if (empty($username)) {
            throw new \Exception("Username PPPoE wajib diisi");
        }

        if (empty($plainPassword)) {
            throw new \Exception("Password PPPoE wajib diisi");
        }

        if (empty($serialNumber)) {
            throw new \Exception("Serial number perangkat wajib diisi");
        }

        if (empty($routerName)) {
            throw new \Exception("Nama router wajib diisi");
        }

        if (empty($startBillingDate) || empty($endBillingDate)) {
            throw new \Exception("Tanggal mulai dan akhir billing wajib diisi");
        }

        try {
            $startDate = Carbon::createFromFormat('Y-m-d', $startBillingDate);
            $endDate   = Carbon::createFromFormat('Y-m-d', $endBillingDate);
        } catch (\Exception $e) {
            throw new \Exception("Format tanggal tidak valid. Gunakan format YYYY-MM-DD");
        }

        // ── CARI / BUAT USER CUSTOMER ─────────────────────────────────────
        $userCustomer    = null;
        $internetCustomer = null;

        if (!empty($email)) {
            $userCustomer = UserCustomer::where('email', $email)
                ->where('company_id', $this->companyId)
                ->first();
        }

        if (!$userCustomer && !empty($phone)) {
            $userCustomer = UserCustomer::where('phone_number', $phone)
                ->where('company_id', $this->companyId)
                ->first();
        }

        if ($userCustomer) {
            // Pelanggan sudah ada — cari internet customer-nya
            $internetCustomer = InternetCustomer::where('user_customer_id', $userCustomer->id)
                ->where('company_id', $this->companyId)
                ->first();

            if (!$internetCustomer) {
                throw new \Exception("User '{$identifier}' ditemukan tapi tidak memiliki data internet customer");
            }
        } else {
            // ── REGISTRASI BARU ───────────────────────────────────────────
            if (empty($provinceName) || empty($cityName) || empty($districtName) || empty($subdistrictName)) {
                throw new \Exception("Provinsi, Kota, Kecamatan, dan Kelurahan wajib diisi untuk pelanggan baru");
            }

            if (empty($packageName)) {
                throw new \Exception("Paket internet wajib diisi untuk pelanggan baru");
            }

            // Cari wilayah
            $provinceModel = Province::where('name', 'like', '%' . $provinceName . '%')->first();
            if (!$provinceModel) {
                throw new \Exception("Provinsi tidak ditemukan: {$provinceName}");
            }

            $cityModel = City::where('province_id', $provinceModel->id)
                ->where('name', $cityName)
                ->first();
            if (!$cityModel) {
                throw new \Exception("Kota tidak ditemukan: {$cityName}");
            }

            $districtModel = District::where('city_id', $cityModel->id)
                ->where('name', $districtName)
                ->first();
            if (!$districtModel) {
                throw new \Exception("Kecamatan tidak ditemukan: {$districtName}");
            }

            $subdistrictModel = Subdistrict::where('district_id', $districtModel->id)
                ->where('name', $subdistrictName)
                ->first();
            if (!$subdistrictModel) {
                throw new \Exception("Kelurahan tidak ditemukan: {$subdistrictName}");
            }

            // Cek coverage
            $hasCoverage = CoverageService::where('subdistrict_id', $subdistrictModel->id)->exists();
            if (!$hasCoverage) {
                throw new \Exception("Coverage service tidak tersedia di: {$subdistrictName}, {$districtName}, {$cityName}");
            }

            // Cari paket internet
            $package = InternetPackage::forRegion($provinceModel->id, $cityModel->id, $districtModel->id)
                ->where('name', 'like', '%' . $packageName . '%')
                ->first();
            if (!$package) {
                throw new \Exception("Paket internet tidak ditemukan: {$packageName}");
            }

            $roleId = Role::where('name', RoleSchema::CUSTOMER_INTERNET)->value('id');

            $userCustomer = UserCustomer::create([
                'name'               => $name,
                'phone_number'       => $phone ?: null,
                'email'              => $email ?: null,
                'company_id'         => $this->companyId,
                'role_id'            => $roleId,
                'start_billing_date' => $startDate->format('Y-m-d'),
                'end_billing_date'   => $endDate->format('Y-m-d'),
            ]);

            $internetCustomer = InternetCustomer::create([
                'company_id'         => $this->companyId,
                'province_id'        => $provinceModel->id,
                'city_id'            => $cityModel->id,
                'district_id'        => $districtModel->id,
                'subdistrict_id'     => $subdistrictModel->id,
                'internet_package_id'=> $package->id,
                'user_customer_id'   => $userCustomer->id,
                'name'               => $name,
                'address'            => $address,
                'ktp_number'         => null,
                'ktp_photo'          => null,
                'is_paid'            => false,
                'status'             => ParamSchema::CUSTOMER_EXISTING,
            ]);
        }

        // ── VALIDASI STATUS UNTUK INSTALASI ──────────────────────────────
        $allowedStatuses = [ParamSchema::CUSTOMER_EXISTING, ParamSchema::PROCESS_INSTALLATION];
        if (!in_array($internetCustomer->status, $allowedStatuses)) {
            throw new \Exception(
                "Pelanggan '{$internetCustomer->code}' tidak dapat diinstal, status saat ini: {$internetCustomer->status}"
            );
        }

        if ($internetCustomer->installation) {
            throw new \Exception("Pelanggan '{$internetCustomer->code}' sudah memiliki data instalasi");
        }

        // ── CEK UNIQUENESS USERNAME ───────────────────────────────────────
        $usernameExists = InternetCustomer::where('username', $username)
            ->where('id', '!=', $internetCustomer->id)
            ->exists();
        if ($usernameExists) {
            throw new \Exception("Username PPPoE '{$username}' sudah digunakan pelanggan lain");
        }

        // ── CARI ROUTER ───────────────────────────────────────────────────
        $router = Router::where('company_id', $this->companyId)
            ->where('name', $routerName)
            ->first();
        if (!$router) {
            throw new \Exception("Router '{$routerName}' tidak ditemukan");
        }

        // ── CARI ADDRESS POOL (opsional) ──────────────────────────────────
        $overridePoolId = null;
        if ($poolName) {
            $pool = AddressPool::where('router_id', $router->id)
                ->where('name', $poolName)
                ->first();
            if (!$pool) {
                throw new \Exception("PPPoE pool '{$poolName}' tidak ditemukan di router '{$routerName}'");
            }
            $overridePoolId = $pool->id;
        }

        // ── UPDATE INTERNET CUSTOMER ──────────────────────────────────────
        $customerUpdate = [
            'status'                  => ParamSchema::INSTALLED,
            'router_id'               => $router->id,
            'username'                => $username,
            'pass_hash'               => $plainPassword,
            'grouping_id'             => $grouping,
            'optical_distribution_id' => $this->opticalDistributionId,
            'override_pool_id'        => $overridePoolId,
        ];

        if ($this->groupId) {
            $customerUpdate['group_id'] = $this->groupId;
        }

        $internetCustomer->update($customerUpdate);

        // ── BUAT RECORD INSTALASI ─────────────────────────────────────────
        InternetCustomerInstallation::updateOrCreate(
            ['internet_customer_id' => $internetCustomer->id],
            [
                'device_serial_number' => $serialNumber,
                'notes'                => 'Import massal (daftar & aktifkan)',
                'installed_at'         => now(),
                'technical_user_id'    => $this->userId,
            ]
        );

        // ── UPDATE BILLING DATES & PASSWORD ──────────────────────────────
        $userCustomer->update([
            'password'           => Hash::make($plainPassword),
            'start_billing_date' => $startDate->format('Y-m-d'),
            'end_billing_date'   => $endDate->format('Y-m-d'),
        ]);

        // ── QUEUE PROVISIONING ────────────────────────────────────────────
        JobsProvisioning::create([
            'type'                 => JobsProvisioning::TYPE_PROVISION,
            'internet_customer_id' => $internetCustomer->id,
            'router_id'            => $router->id,
            'status'               => JobsProvisioning::STATUS_QUEUED,
            'payload'              => ['initial_plain_password' => $plainPassword],
        ]);

        dispatch(new ProvisionCustomerJob($internetCustomer->id));
    }

    public function failed(\Throwable $exception): void
    {
        $progress = ImportProgress::where('batch_id', $this->batchId)->first();
        $total    = $progress ? $progress->total : 0;

        ImportProgress::updateOrCreate(
            ['batch_id' => $this->batchId],
            [
                'processed'    => $total,
                'total_import' => 0,
                'errors'       => [[
                    'row'     => 'System',
                    'message' => 'Job gagal: ' . $exception->getMessage(),
                ]],
            ]
        );

        Log::error('ImportRegisterAndActivateJob failed untuk batch ' . $this->batchId, [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

    protected function updateProgress(int $processed, int $total, int $imported, array $errors): void
    {
        ImportProgress::updateOrCreate(
            ['batch_id' => $this->batchId],
            [
                'processed'    => $processed,
                'total'        => $total,
                'total_import' => $imported,
                'errors'       => $errors,
            ]
        );
    }
}

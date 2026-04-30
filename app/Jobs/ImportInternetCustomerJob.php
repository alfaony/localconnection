<?php

namespace App\Jobs;

use App\Models\ImportProgress;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerInstallation;
use App\Models\JobsProvisioning;
use App\Models\Router;
use App\Models\AddressPool;
use App\Models\UserCustomer;
use App\Schemas\ParamSchema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ImportInternetCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    protected string $batchId;
    protected array $csvData;
    protected string $userId;
    protected string $companyId;
    protected string $opticalDistributionId;

    public function __construct(
        array  $csvData,
        string $userId,
        string $companyId,
        string $batchId,
        string $opticalDistributionId
    ) {
        $this->csvData              = $csvData;
        $this->userId               = $userId;
        $this->companyId            = $companyId;
        $this->batchId              = $batchId;
        $this->opticalDistributionId = $opticalDistributionId;
    }

    public function handle(): void
    {
        $total     = count($this->csvData) - 1; // exclude header row
        $processed = 0;
        $imported  = 0;
        $errors    = [];

        $this->updateProgress($processed, $total, $imported, $errors);

        foreach ($this->csvData as $index => $row) {
            // Skip header
            if ($index === 0) {
                continue;
            }

            try {
                DB::beginTransaction();

                // ── COLUMN MAPPING ────────────────────────────────────────────
                // email, phone, code, username, password, grouping,
                // serial_number, router, pppoe_pool, start_billing_date, end_billing_date, action
                $email            = trim($row[0] ?? '');
                $phone            = trim($row[1] ?? '');
                $code             = trim($row[2] ?? '');
                $username         = trim($row[3] ?? '');
                $plainPassword    = trim($row[4] ?? '');
                $grouping         = trim($row[5] ?? '') ?: null;
                $serialNumber     = trim($row[6] ?? '');
                $routerName       = trim($row[7] ?? '');
                $poolName         = trim($row[8] ?? '') ?: null;
                $startBillingDate = trim($row[9] ?? '');
                $endBillingDate   = trim($row[10] ?? '');
                $action           = strtoupper(trim($row[11] ?? ''));

                $identifier = $code ?: ($email ?: ($phone ?: ($serialNumber ?: ($grouping ?: 'Unknown'))));

                if ($action === 'SYNC') {
                    $this->handleSync(
                        $email, $phone, $code, $serialNumber, $grouping,
                        $startBillingDate, $endBillingDate, $identifier
                    );
                } else {
                    $this->handleNewInstallation(
                        $email, $phone, $code, $username, $plainPassword,
                        $grouping, $serialNumber, $routerName, $poolName,
                        $startBillingDate, $endBillingDate, $identifier
                    );
                }

                DB::commit();
                $imported++;

            } catch (\Exception $e) {
                DB::rollBack();

                $identifier = trim($row[2] ?? '') ?: (trim($row[0] ?? '') ?: (trim($row[1] ?? '') ?: 'Unknown'));
                $errors[] = [
                    'row'     => $index + 1,
                    'message' => $e->getMessage(),
                    'data'    => $identifier,
                ];

                Log::warning("ImportInternetCustomerJob: baris " . ($index + 1), [
                    'batch_id' => $this->batchId,
                    'error'    => $e->getMessage(),
                    'row_data' => array_slice($row, 0, 3),
                ]);
            }

            $processed++;
            $this->updateProgress($processed, $total, $imported, $errors);
        }

        $this->updateProgress($processed, $total, $imported, $errors);

        Log::info("ImportInternetCustomerJob selesai", [
            'batch_id'  => $this->batchId,
            'total'     => $total,
            'imported'  => $imported,
            'failed'    => count($errors),
        ]);
    }

    // ── SYNC: update billing / reaktivasi pelanggan yang sudah terpasang ─────
    protected function handleSync(
        string $email,
        string $phone,
        string $code,
        string $serialNumber,
        ?string $grouping,
        string $startBillingDate,
        string $endBillingDate,
        string $identifier
    ): void {
        // ── VALIDATION ────────────────────────────────────────────────────────
        if (empty($email) && empty($phone) && empty($code) && empty($serialNumber) && empty($grouping)) {
            throw new \Exception('SYNC: minimal satu identifikasi wajib diisi (code, email, phone, serial number, atau grouping)');
        }

        if (empty($startBillingDate)) {
            throw new \Exception('SYNC: Tanggal mulai billing wajib diisi');
        }

        if (empty($endBillingDate)) {
            throw new \Exception('SYNC: Tanggal akhir billing wajib diisi');
        }

        try {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startBillingDate);
            $endDate   = \Carbon\Carbon::createFromFormat('Y-m-d', $endBillingDate);
        } catch (\Exception $e) {
            throw new \Exception('SYNC: Format tanggal tidak valid. Gunakan format YYYY-MM-DD');
        }

        // ── LOOKUP ────────────────────────────────────────────────────────────
        $internetCustomer = null;
        $userCustomer     = null;

        // 1. Lookup by code
        if (!$internetCustomer && !empty($code)) {
            $internetCustomer = InternetCustomer::where('code', $code)
                ->where('company_id', $this->companyId)
                ->first();

            if ($internetCustomer) {
                $userCustomer = $internetCustomer->userCustomer;
            }
        }

        // 2. Lookup by email
        if (!$internetCustomer && !empty($email)) {
            $matchedUsers = UserCustomer::where('email', $email)
                ->where('company_id', $this->companyId)
                ->get();

            if ($matchedUsers->count() > 1) {
                throw new \Exception("SYNC: Duplikat — ditemukan {$matchedUsers->count()} pelanggan dengan email '{$email}'");
            }

            if ($matchedUsers->count() === 1) {
                $uc         = $matchedUsers->first();
                $candidates = InternetCustomer::where('user_customer_id', $uc->id)
                    ->where('company_id', $this->companyId)
                    ->get();

                if ($candidates->count() > 1) {
                    throw new \Exception("SYNC: Duplikat — ditemukan {$candidates->count()} data internet pelanggan dengan email '{$email}'");
                }

                if ($candidates->count() === 1) {
                    $internetCustomer = $candidates->first();
                    $userCustomer     = $uc;
                }
            }
        }

        // 3. Lookup by phone
        if (!$internetCustomer && !empty($phone)) {
            $matchedUsers = UserCustomer::where('phone_number', $phone)
                ->where('company_id', $this->companyId)
                ->get();

            if ($matchedUsers->count() > 1) {
                throw new \Exception("SYNC: Duplikat — ditemukan {$matchedUsers->count()} pelanggan dengan telepon '{$phone}'");
            }

            if ($matchedUsers->count() === 1) {
                $uc         = $matchedUsers->first();
                $candidates = InternetCustomer::where('user_customer_id', $uc->id)
                    ->where('company_id', $this->companyId)
                    ->get();

                if ($candidates->count() > 1) {
                    throw new \Exception("SYNC: Duplikat — ditemukan {$candidates->count()} data internet pelanggan dengan telepon '{$phone}'");
                }

                if ($candidates->count() === 1) {
                    $internetCustomer = $candidates->first();
                    $userCustomer     = $uc;
                }
            }
        }

        // 4. Lookup by serial number
        if (!$internetCustomer && !empty($serialNumber)) {
            $installations = InternetCustomerInstallation::where('device_serial_number', $serialNumber)
                ->whereHas('internetCustomer', fn($q) => $q->where('company_id', $this->companyId))
                ->with('internetCustomer.userCustomer')
                ->get();

            if ($installations->count() > 1) {
                throw new \Exception("SYNC: Terdapat Number / Grouping Ganda, Coba check serial number '{$serialNumber}'");
            }

            if ($installations->count() === 1) {
                $internetCustomer = $installations->first()->internetCustomer;
                $userCustomer     = $internetCustomer->userCustomer;
            }
        }

        // 5. Lookup by grouping
        if (!$internetCustomer && !empty($grouping)) {
            $candidates = InternetCustomer::where('grouping_id', $grouping)
                ->where('company_id', $this->companyId)
                ->get();

            if ($candidates->count() > 1) {
                throw new \Exception("SYNC: Terdapat Number / Grouping Ganda, Coba check grouping '{$grouping}'");
            }

            if ($candidates->count() === 1) {
                $internetCustomer = $candidates->first();
                $userCustomer     = $internetCustomer->userCustomer;
            }
        }

        if (!$internetCustomer) {
            throw new \Exception("SYNC: Pelanggan '{$identifier}' tidak ditemukan");
        }

        // ── STATUS GUARD ──────────────────────────────────────────────────────
        $status = $internetCustomer->status;

        if (in_array($status, [ParamSchema::CUSTOMER_EXISTING, ParamSchema::PROCESS_INSTALLATION])) {
            throw new \Exception("SYNC: Pelanggan '{$internetCustomer->code}' belum memiliki instalasi");
        }

        if ($status === ParamSchema::REACTIVATED) {
            throw new \Exception("SYNC: Pelanggan '{$internetCustomer->code}' sedang dalam proses mengaktivasi");
        }

        $needsProvision = [
            ParamSchema::WAITING_PAYMENT_SUBSCRIPTION,
            ParamSchema::SUSPENDED,
            ParamSchema::INSTALLED,
        ];

        if ($status !== ParamSchema::ACTIVE && !in_array($status, $needsProvision)) {
            throw new \Exception("SYNC: Pelanggan '{$internetCustomer->code}' dalam status lain ({$status}), tidak dapat diproses");
        }

        // ── UPDATE BILLING DATES ──────────────────────────────────────────────
        $userCustomer->update([
            'start_billing_date' => $startDate->format('Y-m-d'),
            'end_billing_date'   => $endDate->format('Y-m-d'),
        ]);

        // ── STATUS-BASED PROVISIONING ─────────────────────────────────────────
        if (in_array($status, $needsProvision)) {
            $internetCustomer->update(['status' => ParamSchema::REACTIVATED]);

            JobsProvisioning::create([
                'type'                 => JobsProvisioning::TYPE_PROVISION,
                'internet_customer_id' => $internetCustomer->id,
                'router_id'            => $internetCustomer->router_id,
                'status'               => JobsProvisioning::STATUS_QUEUED,
                'payload'              => null,
            ]);

            dispatch(new ProvisionCustomerJob($internetCustomer->id));
            \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetCustomer->id]);
        }
        // ACTIVE: hanya update billing, tidak ada provisioning
    }

    // ── NEW INSTALLATION: instalasi baru pelanggan yang belum terpasang ──────
    protected function handleNewInstallation(
        string $email,
        string $phone,
        string $code,
        string $username,
        string $plainPassword,
        ?string $grouping,
        string $serialNumber,
        string $routerName,
        ?string $poolName,
        string $startBillingDate,
        string $endBillingDate,
        string $identifier
    ): void {
        // ── VALIDATION ────────────────────────────────────────────────────────
        if (empty($email) && empty($phone) && empty($code)) {
            throw new \Exception('Email, nomor telepon, atau kode pelanggan wajib diisi (minimal salah satu)');
        }

        if (empty($username)) {
            throw new \Exception('Username PPPoE wajib diisi');
        }

        if (empty($plainPassword)) {
            throw new \Exception('Password PPPoE wajib diisi');
        }

        if (empty($serialNumber)) {
            throw new \Exception('Serial number perangkat wajib diisi');
        }

        if (empty($routerName)) {
            throw new \Exception('Nama router wajib diisi');
        }

        if (empty($startBillingDate)) {
            throw new \Exception('Tanggal mulai billing wajib diisi');
        }

        if (empty($endBillingDate)) {
            throw new \Exception('Tanggal akhir billing wajib diisi');
        }

        try {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startBillingDate);
            $endDate   = \Carbon\Carbon::createFromFormat('Y-m-d', $endBillingDate);
        } catch (\Exception $e) {
            throw new \Exception('Format tanggal tidak valid. Gunakan format YYYY-MM-DD (contoh: 2025-01-01)');
        }

        // ── FIND INTERNET CUSTOMER (belum memiliki instalasi) ─────────────────
        $internetCustomer = null;
        $userCustomer     = null;

        // 1. Lookup by code
        if (!empty($code)) {
            $internetCustomer = InternetCustomer::where('code', $code)
                ->where('company_id', $this->companyId)
                ->whereIn('status', [ParamSchema::CUSTOMER_EXISTING, ParamSchema::PROCESS_INSTALLATION])
                ->first();

            if ($internetCustomer) {
                $userCustomer = $internetCustomer->userCustomer;
            }
        }

        // 2. Lookup by email
        if (!$internetCustomer && !empty($email)) {
            $matchedUsers = UserCustomer::where('email', $email)
                ->where('company_id', $this->companyId)
                ->get();

            if ($matchedUsers->count() > 1) {
                throw new \Exception("Duplikat: ditemukan {$matchedUsers->count()} pelanggan dengan email '{$email}'");
            }

            if ($matchedUsers->count() === 1) {
                $uc         = $matchedUsers->first();
                $candidates = InternetCustomer::where('user_customer_id', $uc->id)
                    ->where('company_id', $this->companyId)
                    ->get();

                if ($candidates->count() > 1) {
                    throw new \Exception("Duplikat: ditemukan {$candidates->count()} data internet pelanggan dengan email '{$email}' yang belum terpasang");
                }

                if ($candidates->count() === 1) {
                    $internetCustomer = $candidates->first();
                    $userCustomer     = $uc;
                }
            }
        }

        // 3. Lookup by phone
        if (!$internetCustomer && !empty($phone)) {
            $matchedUsers = UserCustomer::where('phone_number', $phone)
                ->where('company_id', $this->companyId)
                ->get();

            if ($matchedUsers->count() > 1) {
                throw new \Exception("Duplikat: ditemukan {$matchedUsers->count()} pelanggan dengan telepon '{$phone}'");
            }

            if ($matchedUsers->count() === 1) {
                $uc         = $matchedUsers->first();
                $candidates = InternetCustomer::where('user_customer_id', $uc->id)
                    ->where('company_id', $this->companyId)
                    ->get();

                if ($candidates->count() > 1) {
                    throw new \Exception("Duplikat: ditemukan {$candidates->count()} data internet pelanggan dengan telepon '{$phone}' yang belum terpasang");
                }

                if ($candidates->count() === 1) {
                    $internetCustomer = $candidates->first();
                    $userCustomer     = $uc;
                }
            }
        }

        if (!$internetCustomer) {
            throw new \Exception("Pelanggan '{$identifier}' tidak ditemukan");
        }

        if ($internetCustomer->installation) {
            throw new \Exception("Pelanggan '{$internetCustomer->code}' sudah memiliki instalasi");
        }

        // ── CHECK USERNAME UNIQUENESS ─────────────────────────────────────────
        $usernameExists = InternetCustomer::where('username', $username)
            ->where('id', '!=', $internetCustomer->id)
            ->exists();

        if ($usernameExists) {
            throw new \Exception("Username PPPoE '{$username}' sudah digunakan pelanggan lain");
        }

        // ── FIND ROUTER ───────────────────────────────────────────────────────
        $router = Router::where('company_id', $this->companyId)
            ->where('name', $routerName)
            ->first();

        if (!$router) {
            throw new \Exception("Router '{$routerName}' tidak ditemukan");
        }

        // ── FIND ADDRESS POOL (optional) ──────────────────────────────────────
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

        // ── UPDATE INTERNET CUSTOMER ──────────────────────────────────────────
        $internetCustomer->update([
            'status'                  => ParamSchema::INSTALLED,
            'router_id'               => $router->id,
            'username'                => $username,
            'pass_hash'               => $plainPassword,
            'grouping_id'             => $grouping,
            'optical_distribution_id' => $this->opticalDistributionId,
            'override_pool_id'        => $overridePoolId,
        ]);

        // ── CREATE INSTALLATION RECORD ────────────────────────────────────────
        InternetCustomerInstallation::updateOrCreate(
            ['internet_customer_id' => $internetCustomer->id],
            [
                'device_serial_number' => $serialNumber,
                'notes'                => 'Import massal',
                'installed_at'         => now(),
                'technical_user_id'    => $this->userId,
            ]
        );

        // ── UPDATE USER CUSTOMER BILLING DATES ────────────────────────────────
        $userCustomer->update([
            'password'           => Hash::make($plainPassword),
            'start_billing_date' => $startDate->format('Y-m-d'),
            'end_billing_date'   => $endDate->format('Y-m-d'),
        ]);

        // ── QUEUE PROVISIONING ────────────────────────────────────────────────
        JobsProvisioning::create([
            'type'                 => JobsProvisioning::TYPE_PROVISION,
            'internet_customer_id' => $internetCustomer->id,
            'router_id'            => $router->id,
            'status'               => JobsProvisioning::STATUS_QUEUED,
            'payload'              => ['initial_plain_password' => $plainPassword],
        ]);

        dispatch(new ProvisionCustomerJob($internetCustomer->id));
        \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetCustomer->id]);
    }

    public function failed(\Throwable $exception): void
    {
        // Mark as "failed" by setting processed = total so polling stops,
        // and include the system error in errors array
        $progress = ImportProgress::where('batch_id', $this->batchId)->first();
        $total    = $progress ? $progress->total : 0;

        ImportProgress::updateOrCreate(
            ['batch_id' => $this->batchId],
            [
                'processed'    => $total, // force completion so JS polling stops
                'total_import' => 0,
                'errors'       => [[
                    'row'     => 'System',
                    'message' => 'Job gagal: ' . $exception->getMessage(),
                ]],
            ]
        );

        Log::error("ImportInternetCustomerJob failed untuk batch {$this->batchId}", [
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

<?php

namespace App\Http\Livewire\InternetCustomer\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\User;
use App\Models\InternetCustomerInstallation;
use App\Models\InternetCustomerPurchase;
use App\Models\JobsProvisioning;
use App\Models\Router;
use App\Models\CoverageService;
use App\Models\OpticalDistribution;
use App\Models\CoverageServiceDistribution;
use App\Models\HotspotServer;

use App\Jobs\ProvisionCustomerJob;
use App\Jobs\GenerateInternetPurchaseCouponJob;
use App\Jobs\ImportInternetCustomerJob;
use App\Jobs\GenerateGroupingIdJob;
use App\Models\InternetCustomerGroup;

use App\Models\ImportProgress;
use App\Models\InternetInstallationPhoto;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Helpers\InboxHelper;
use App\Helpers\Access;

use App\Schemas\ParamSchema;
use App\Schemas\RoleSchema;

use Carbon\Carbon;
class InternetCustomerIndex extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public ?string $override_pool_id = null;
    public array $availablePools = [];
    
    public ?string $optical_distribution_id = null;
    public ?string $group_id = null;
    public array $availableOdps = [];
    public array $availableGroups = [];

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedCompany = '';
    public $selectedPackage = '';
    public $statusFilter = ParamSchema::PENDING;
    public $customerTypeFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $dateType = 'billing';
    public $selectedCustomer = null;
    public $selectedPaymentProof;

    public $routers = [];
    public $router_id = null;
    public $username = null;
    public $password = null;

    public $currentInstallationId; 
    public $currentInstallationName;
    public $currentInstallationCode;
    public $deviceSerialNumber, $photos = [], $installationNotes;
    public $installationCustomerName, $installationCustomerCode, $installationCustomerId, $local_address;
    public $installationModal = false;
    public $currentInstallationCustomer;
    public $isSubmitting = false;

    public $plain_password = null;

    public $newUsernameChecked = false;
    public $newUsernameAvailable = false;
    public $newUsernameExistingCustomer = [];

    // Hotspot installation fields
    public ?string $hotspot_server_id = null;
    public ?string $ip_binding_type   = null;
    public ?string $ip_binding_mode   = null;
    public ?string $ip_address        = null;
    public ?string $mac_address       = null;

    public $canApprove;
    public $canTechnical;

    // ── Import properties ──────────────────────────────────────────────────
    public $csvFile;
    public bool $isFileReady     = false;
    public bool $uploadingFile   = false;
    public ?string $importBatchId = null;
    public ?array $importProgress = null;
    public bool $isImporting     = false;
    public bool $showImportSection = false;
    public ?string $import_odp_id  = null;
    public array $importAvailableOdps = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage',
        'sortField',
        'sortDirection',
        'selectedCompany',
        'selectedPackage',
        'statusFilter',
        'customerTypeFilter' => ['except' => ''],
        'dateFrom',
        'dateTo',
        'dateType' => ['except' => 'billing'],
    ];

    // Method untuk membuka modal
    // BARU: Method untuk load ODP berdasarkan customer
    public function openInstallationModal(string $customerId)
    {
        $radiusEnable = \App\Services\RadiusService::isEnabled();

        $cust = InternetCustomer::with([
            'internetPackage',
            'subdistrict.coverageService.coverageServiceOds.ods.pops.routers',
        ])->findOrFail($customerId);

        $this->currentInstallationId = $cust->id;
        $accessType = $cust->access_type ?? 'pppoe';

        // === FILTER ROUTER (PPPoE) ===
        $routerIds = collect(
            $cust->subdistrict?->coverageService?->coverageServiceOds ?? []
        )
        ->flatMap(function ($csod) {
            return collect($csod->opticalDistribution?->pops ?? [])
                ->flatMap(fn($pop) => collect($pop->routers ?? [])->pluck('id'));
        })
        ->unique()
        ->values();

        if ($routerIds->isEmpty())
        {
            $routers = Router::query()
                ->whereHas('pppoeServers')
                ->orderBy('name')
                ->get(['id','name','active_status']);
        }else
        {
            $routers = Router::query()
            ->whereIn('id', $routerIds)
            ->whereHas('pppoeServers', fn($q) => $q->whereNotNull('address_pool_id'))
            ->whereHas('addressPools')
            ->withCount(['pppoeServers' => fn($q) => $q->whereNotNull('address_pool_id')])
            ->orderBy('name')
            ->get(['id','name','active_status']);
        }

        // === HOTSPOT SERVERS ===
        $hotspotServers = [];
        if ($accessType === 'hotspot') {
            $hotspotServers = HotspotServer::query()
                ->whereHas('router', fn($q) => $q->where('company_id', auth()->user()->company_id))
                ->with('router:id,name')
                ->orderBy('name')
                ->get()
                ->map(fn($hs) => [
                    'id'        => $hs->id,
                    'name'      => $hs->name . ' (' . optional($hs->router)->name . ')',
                    'router_id' => $hs->router_id,
                ])
                ->values()
                ->toArray();
        }

        // Load ODP dari CoverageService
        $this->loadOdpsForCustomer($cust);

        $payload = [
            'customerName'   => $cust->name,
            'customerCode'   => $cust->code,
            'serialNumber'   => '',
            'accessType'     => $accessType,
            'routers'        => $routers->map(fn($r) => [
                'id'       => $r->id,
                'disabled' => $r->is_online ? false : true,
                'name'     => $r->name . ' (PPPoE: '.$r->pppoe_servers_count.')',
            ])->values(),
            'hotspotServers' => $hotspotServers,
            'odps'           => $this->availableOdps,
        ];

        $this->dispatchBrowserEvent('open-installation-modal', $payload);
    }

    protected function loadOdpsForCustomer($customer)
    {
        // Ambil coverage service dari subdistrict customer
        $coverageService = $customer->subdistrict?->coverageService;
        
        if (!$coverageService) {
            $this->availableOdps = [];
            return;
        }

        // Ambil semua ODP yang terkait dengan coverage service ini
        $this->availableOdps = CoverageServiceDistribution::query()
            ->where('coverage_service_id', $coverageService->id)
            ->with('ods:id,name') // eager load optical distribution
            ->get()
            ->pluck('ods')
            ->filter() // remove null values
            ->map(fn($odp) => [
                'id' => $odp->id,
                'label' => "{$odp->name}"
            ])
            ->unique('id')
            ->values()
            ->toArray();
    }

    // When ODP value changes via @this.set() from JS
    public function updatedOpticalDistributionId($value)
    {
        $this->group_id        = null;
        $this->availableGroups = [];

        $groups = [];
        if ($value) {
            $groups = InternetCustomerGroup::byCompany(Auth::user()->company_id)
                ->whereHas('odps', fn($q) => $q->where('optical_distributions.id', $value))
                ->orderBy('name')
                ->get(['id', 'name', 'description'])
                ->map(fn($g) => [
                    'id'          => $g->id,
                    'name'        => $g->name,
                    'description' => $g->description,
                ])
                ->values()
                ->toArray();
            $this->availableGroups = $groups;
        }

        $this->dispatchBrowserEvent('groups-loaded', ['groups' => $groups]);
        $this->dispatchBrowserEvent('grouping-id-preview', ['preview' => null]);
    }

    // Called by JS when user picks a group — compute next grouping_id as preview
    public function previewGroupingId(?string $groupId): void
    {
        if (!$groupId) {
            $this->dispatchBrowserEvent('grouping-id-preview', ['preview' => null]);
            return;
        }

        $group = InternetCustomerGroup::find($groupId);
        if (!$group) {
            $this->dispatchBrowserEvent('grouping-id-preview', ['preview' => null]);
            return;
        }

        $prefix     = $group->grouping_prefix;
        $lastNumber = (int) $group->last_number;
        if ($lastNumber == 0) {
            $lastNumber = (int) InternetCustomer::where('group_id', $group->id)
                ->whereNotNull('grouping_id')
                ->get('grouping_id')
                ->pluck('grouping_id')
                ->map(fn($gid) => InternetCustomerGroup::parseSequence(substr($gid, strlen($prefix))))
                ->max();
        }

        $preview = $prefix . InternetCustomerGroup::formatSequence($lastNumber + 1);
        $this->dispatchBrowserEvent('grouping-id-preview', ['preview' => $preview]);
    }

    // Method untuk validasi dan submit
    public function updatedUsername($value)
    {
        $this->newUsernameChecked = false;
        $this->newUsernameAvailable = false;
        $this->newUsernameExistingCustomer = [];

        if (!$value || strlen($value) < 3) {
            return;
        }
        
        $this->checkNewUsernameAvailability($value);
    }

    public function completeInstallation(
        $serialNumber,
        $notes,
        $routerId,
        $username,
        $password,
        $override_pool_id,
        $local_address,
        $optical_distribution_id = null,
        $grouping_id = null,
        $hotspot_server_id = null,
        $ip_binding_type   = null,
        $ip_binding_mode   = null,
        $ip_address        = null,
        $mac_address       = null
        ) {
        $this->deviceSerialNumber       = $serialNumber;
        $this->installationNotes        = $notes;
        $this->username                 = $username;
        $this->password                 = $password;
        $this->optical_distribution_id  = $optical_distribution_id;
        $this->grouping_id              = $grouping_id;
        $this->hotspot_server_id        = $hotspot_server_id;
        $this->ip_binding_type          = $ip_binding_type;
        $this->ip_binding_mode          = $ip_binding_mode;
        $this->ip_address               = $ip_address;
        $this->mac_address              = $mac_address;
        
        Log::info('completeInstallation called', [
            'serialNumber'           => $serialNumber,
            'notes'                  => $notes,
            'photos_count'           => count($this->photos),
            'currentInstallationId'  => $this->currentInstallationId,
            'optical_distribution_id'=> $optical_distribution_id,
            'grouping_id'            => $grouping_id,
        ]);
        
        // Cek apakah photos sudah ada
        if (empty($this->photos)) {
            Log::warning('Photos empty');
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Foto belum terupload. Silakan coba lagi.'
            ]);
            return false;
        }
        
        $validPhotos = array_filter($this->photos, function($photo) {
            return $photo !== null;
        });
        
        if (empty($validPhotos)) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Tidak ada foto yang valid. Silakan coba lagi.'
            ]);
            return false;
        }
        
        // Ambil customer lebih awal untuk tahu access_type
        $customer = InternetCustomer::findOrFail($this->currentInstallationId);
        $isHotspot = ($customer->access_type === 'hotspot');

        // Router dari hotspot server jika hotspot
        if ($isHotspot && $hotspot_server_id) {
            $hs = HotspotServer::find($hotspot_server_id);
            $this->router_id = $hs?->router_id;
        } else {
            $this->router_id        = $routerId;
            $this->override_pool_id = $override_pool_id;
            $this->local_address    = $local_address;
        }

        $isBypassed = $isHotspot && ($ip_binding_mode === 'bypassed');

        // Auto-generate identifier untuk bypassed (tidak perlu login, tapi username harus unik di DB)
        if ($isBypassed && empty($this->username)) {
            $macClean = preg_replace('/[^a-zA-Z0-9]/', '', $mac_address ?? '');
            $this->username = 'bypass_' . ($macClean ?: \Illuminate\Support\Str::random(10));
            $username = $this->username;
            $this->password = \Illuminate\Support\Str::random(16); // tidak pernah dipakai
        }

        // Validasi kondisional
        $rules = [
            'currentInstallationId'  => 'required|exists:internet_customers,id',
            'deviceSerialNumber'     => 'required|string|max:255',
            'optical_distribution_id'=> 'required|exists:optical_distributions,id',
            'grouping_id'            => 'nullable|string|max:255',
        ];
        $messages = [
            'deviceSerialNumber.required'      => 'Serial Number wajib diisi',
            'optical_distribution_id.required' => 'ODP wajib dipilih',
            'optical_distribution_id.exists'   => 'ODP tidak valid',
        ];

        if ($isHotspot) {
            $rules['hotspot_server_id'] = 'required|exists:hotspot_servers,id';
            $messages['hotspot_server_id.required'] = 'Hotspot Server wajib dipilih';

            if ($isBypassed) {
                $rules['mac_address'] = 'nullable|string|max:32';
                $rules['ip_address']  = 'nullable|ip';
                if (empty($mac_address) && empty($ip_address)) {
                    $this->addError('mac_address', 'Mode Bypassed membutuhkan minimal MAC address atau IP address');
                    return false;
                }
            } else {
                $rules['username']    = 'required|unique:internet_customers,username';
                $rules['password']    = 'required';
                $rules['ip_address']  = 'nullable|ip';
                $rules['mac_address'] = 'nullable|string|max:32';
                $messages['username.unique'] = 'Username hotspot sudah digunakan';
                // Direct binding (non-bypassed) juga butuh IP atau MAC
                if ($ip_binding_type === 'direct' && empty($ip_address) && empty($mac_address)) {
                    $this->addError('ip_address', 'Direct binding membutuhkan minimal IP Address atau MAC Address');
                    return false;
                }
            }
        } else {
            $rules['username']     = 'required|unique:internet_customers,username';
            $rules['password']     = 'required';
            $rules['router_id']    = 'required|exists:routers,id';
            $rules['local_address']= 'nullable|ip|unique:internet_customers,local_address';
            $messages['username.unique']      = 'Username PPPoE sudah digunakan';
            $messages['local_address.unique'] = 'Alamat IP lokal sudah terdaftar';
        }

        $this->validate($rules, $messages);

        DB::beginTransaction();
        try {
            Log::info('Customer found', ['id' => $customer->id, 'code' => $customer->code, 'access_type' => $customer->access_type]);

            $updateData = [
                'grouping_id'            => $grouping_id,
                'status'                 => ParamSchema::INSTALLED,
                'router_id'              => $this->router_id,
                'username'               => $username,
                'pass_hash'              => $password,
                'optical_distribution_id'=> $optical_distribution_id,
            ];

            if ($isHotspot) {
                $updateData['hotspot_server_id'] = $hotspot_server_id;
                $updateData['ip_binding_type']   = $ip_binding_type ?: null;
                $updateData['ip_binding_mode']   = $ip_binding_mode ?: null;
                $updateData['ip_address']        = $ip_address ?: null;
                $updateData['mac_address']       = $mac_address ?: null;
            } else {
                $updateData['local_address']    = $local_address;
                $updateData['override_pool_id'] = $override_pool_id ?: null;
            }

            $customer->update($updateData);

            dispatch(new \App\Jobs\ProvisionCustomerJob($customer->id));

            $customerInstallation = InternetCustomerInstallation::create([
                'internet_customer_id' => $customer->id,
                'device_serial_number' => $serialNumber,
                'notes'                => $notes,
                'installed_at'         => now(),
                'technical_user_id'    => Auth::id(),
            ]);

            $this->activate($customer->id, $password);

            Log::info('Installation record created', [
                'id'     => $customerInstallation->id,
                'odp_id' => $optical_distribution_id,
            ]);

            $photoCount = 0;

            foreach ($validPhotos as $index => $photo) {
                try {
                    if (is_string($photo)) {
                        $tmpPath = storage_path('app/livewire-tmp/' . $photo);

                        if (!file_exists($tmpPath)) {
                            Log::error('Temporary file not found', ['path' => $tmpPath]);
                            continue;
                        }

                        $fileContent = file_get_contents($tmpPath);
                        $extension   = pathinfo($photo, PATHINFO_EXTENSION) ?: 'png';
                        $filename    = uniqid() . '_' . time() . '_' . $index . '.' . $extension;
                        $s3Path      = 'installation-photos/' . $customer->code . '/' . $filename;

                        Storage::disk('s3')->put($s3Path, $fileContent, 'public');

                        InternetInstallationPhoto::create([
                            'internet_installation_id' => $customerInstallation->id,
                            'photo'   => $s3Path,
                            'caption' => 'Installation Photo ' . ($photoCount + 1),
                        ]);

                        @unlink($tmpPath);
                        $photoCount++;

                    } elseif ($photo instanceof \Illuminate\Http\UploadedFile) {
                        $extension = $photo->getClientOriginalExtension();
                        $filename  = uniqid() . '_' . time() . '_' . $index . '.' . $extension;

                        $path = $photo->storeAs(
                            'installation-photos/' . $customer->code,
                            $filename,
                            's3'
                        );

                        Storage::disk('s3')->setVisibility($path, 'public');

                        InternetInstallationPhoto::create([
                            'internet_installation_id' => $customerInstallation->id,
                            'photo'   => $path,
                            'caption' => 'Installation Photo ' . ($photoCount + 1),
                        ]);

                        $photoCount++;
                    }

                    \App\Jobs\SyncInstalledCustomersJob::dispatch([$customer->id]);
                } catch (\Exception $photoError) {
                    Log::error('Failed to process photo', [
                        'index' => $index,
                        'error' => $photoError->getMessage(),
                    ]);
                    continue;
                }
            }

            if ($photoCount === 0) {
                throw new \Exception('Tidak ada foto yang berhasil diupload. Silakan coba lagi.');
            }

            DB::commit();
            \App\Helpers\XpHelper::award(Auth::user(), $customerInstallation, "Internet Instalattion");

            Log::info('Installation completed successfully', [
                'customer_id'    => $customer->id,
                'photos_uploaded'=> $photoCount,
                'odp_id'         => $optical_distribution_id,
                'grouping_id'    => $grouping_id,
            ]);

            $this->reset([
                'installationModal',
                'currentInstallationId',
                'currentInstallationName',
                'currentInstallationCode',
                'deviceSerialNumber',
                'installationNotes',
                'photos',
                'optical_distribution_id',
                'group_id',
                'availableGroups',
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type'    => 'success',
                'message' => "Instalasi berhasil disimpan dengan {$photoCount} foto",
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Installation failed', [
                'customer_id' => $this->currentInstallationId,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type'    => 'error',
                'message' => 'Gagal menyimpan instalasi: ' . $e->getMessage(),
            ]);

            return false;
        }
    }


    // ── Import Methods ─────────────────────────────────────────────────────

    public function mountImportOdps(): void
    {
        $this->importAvailableOdps = OpticalDistribution::byCompany(Auth::user()->company_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($o) => ['id' => $o->id, 'label' => $o->name])
            ->toArray();
    }

    public function toggleImportSection(): void
    {
        $this->showImportSection = !$this->showImportSection;

        if (!$this->showImportSection) {
            $this->resetImport();
        }
    }

    public function resetImport(): void
    {
        $this->reset([
            'csvFile', 'importBatchId', 'importProgress',
            'isImporting', 'isFileReady', 'uploadingFile', 'import_odp_id',
        ]);
        $this->resetValidation(['csvFile', 'import_odp_id']);
    }

    public function updatedCsvFile(): void
    {
        $this->resetValidation('csvFile');
        // updatedCsvFile() is only called after Livewire has finished uploading
        // the file to temp storage, so if $csvFile is set the file is already ready.
        $this->isFileReady   = (bool) $this->csvFile;
        $this->uploadingFile = false;
    }

    public function checkImportFileReady(): bool
    {
        try {
            if ($this->csvFile && $this->csvFile->exists()) {
                $content = $this->csvFile->get();

                if (!empty($content)) {
                    $this->isFileReady  = true;
                    $this->uploadingFile = false;

                    $this->dispatchBrowserEvent('import-file-ready', [
                        'filename' => $this->csvFile->getClientOriginalName(),
                        'size'     => number_format($this->csvFile->getSize() / 1024, 2) . ' KB',
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Import file check error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function importCustomers(): void
    {
        $this->validate([
            'import_odp_id' => 'required|exists:optical_distributions,id',
            'csvFile'       => 'required|file|max:10240',
        ], [
            'import_odp_id.required' => 'Pilih ODP terlebih dahulu sebelum import',
            'import_odp_id.exists'   => 'ODP tidak valid',
            'csvFile.required'       => 'File CSV wajib diupload',
            'csvFile.max'            => 'Ukuran file maksimal 10MB',
        ]);

        if (!$this->isFileReady || !$this->csvFile) {
            $this->addError('csvFile', 'File belum siap. Silakan tunggu sebentar.');
            return;
        }

        try {
            if (!$this->csvFile->exists()) {
                $this->addError('csvFile', 'File tidak ditemukan. Silakan upload ulang.');
                return;
            }

            $fileContent = $this->csvFile->get();

            if (empty($fileContent)) {
                $this->addError('csvFile', 'File kosong atau corrupt.');
                return;
            }

            $csv = \League\Csv\Reader::createFromString($fileContent);
            $csv->setHeaderOffset(null);
            $csvData = iterator_to_array($csv->getRecords());

            if (count($csvData) <= 1) {
                $this->addError('csvFile', 'File CSV kosong atau hanya berisi header.');
                return;
            }

            $this->importBatchId = \Illuminate\Support\Str::uuid()->toString();

            ImportProgress::create([
                'batch_id'     => $this->importBatchId,
                'processed'    => 0,
                'total'        => count($csvData) - 1,
                'total_import' => 0,
                'errors'       => [],
            ]);

            ImportInternetCustomerJob::dispatch(
                $csvData,
                Auth::id(),
                Auth::user()->company_id,
                $this->importBatchId,
                $this->import_odp_id
            );

            // Inisialisasi progress agar blade langsung tampil tanpa menunggu poll pertama
            $this->importProgress = [
                'batch_id'  => $this->importBatchId,
                'processed' => 0,
                'total'     => count($csvData) - 1,
                'success'   => 0,
                'failed'    => 0,
                'percentage'=> 0,
                'status'    => 'processing',
                'errors'    => [],
                'updated_at'=> now()->toDateTimeString(),
            ];
            $this->isImporting   = true;
            $this->isFileReady   = false;
            $this->uploadingFile = false;
            $this->csvFile       = null;

            $this->dispatchBrowserEvent('import-started', [
                'total_rows' => count($csvData) - 1,
            ]);

            $this->dispatchBrowserEvent('start-progress-check');

        } catch (\Exception $e) {
            Log::error('Import internet customer error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('csvFile', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->isFileReady   = false;
            $this->uploadingFile = false;
        }
    }

    public function checkImportProgress(): void
    {
        if (!$this->importBatchId) {
            return;
        }

        $progress = ImportProgress::where('batch_id', $this->importBatchId)->first();

        if (!$progress) {
            return;
        }

        $errors = $progress->errors;
        if (!is_array($errors)) {
            $errors = [];
        }

        $isDone = $progress->total > 0 && $progress->processed >= $progress->total;

        $this->importProgress = [
            'batch_id'    => $progress->batch_id,
            'processed'   => $progress->processed,
            'total'       => $progress->total,
            'total_import'=> $progress->total_import,
            'success'     => $progress->success,
            'failed'      => $progress->failed,
            'percentage'  => $progress->percentage,
            'status'      => $isDone ? 'completed' : 'processing',
            'errors'      => $errors,
            'updated_at'  => $progress->updated_at->toDateTimeString(),
        ];

        if ($isDone) {
            // Tetap tampilkan progress section — JS yang akan hide setelah user dismiss SweetAlert
            $this->dispatchBrowserEvent('import-completed', [
                'progress' => $this->importProgress,
            ]);
        }
    }

    public function downloadImportTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'email', 'phone', 'code', 'username', 'password',
                'grouping', 'serial_number', 'router',
                'pppoe_pool', 'start_billing_date', 'end_billing_date', 'action',
            ]);

            // Contoh instalasi baru (tanpa action)
            fputcsv($file, [
                'pelanggan@email.com', '081234567890', 'KL-0001', 'pppoe_user1', 'P@ssw0rd',
                'GRPAB', 'SN-123456789', 'Router-Utama',
                'Pool-Main', '2025-01-01', '2025-02-01', '',
            ]);

            // Contoh SYNC: reaktivasi / perpanjang billing pelanggan existing
            fputcsv($file, [
                '', '', 'KL-0002', '', '',
                '', '', '',
                '', '2025-02-01', '2025-03-01', 'SYNC',
            ]);

            fclose($file);
        }, 'template_import_internet_customer.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function getImportStatusColor(string $status): string
    {
        return match ($status) {
            'queued'     => 'secondary',
            'processing' => 'info',
            'completed'  => 'success',
            'failed'     => 'danger',
            default      => 'secondary',
        };
    }

    // Search
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingSelectedCompany()
    {
        $this->resetPage();
    }

    public function updatingSelectedPackage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }



    public function viewPaymentProof($id)
    {
        $purchase = InternetCustomerPurchase::findOrFail($id);

        $this->selectedPaymentProof = $purchase->payment_proof ? s3_asset(true,10,$purchase->payment_proof) : null;
        
        $proofUrl= $purchase->payment_proof ? s3_asset(true,10,$purchase->payment_proof) : null;
    
        // Dispatch kedua jenis event
        $this->dispatchBrowserEvent('showPaymentProofModal', ['proofUrl' => $proofUrl, 'transferDetails' => [
                    'date' => $purchase->transfer_date ? \Carbon\Carbon::parse($purchase->transfer_date)->format('d M Y') : null,
                    'bank' => $purchase->transfer_from_bank,
                    'account_name' => $purchase->transfer_from_account_name,
                    'notes' => $purchase->transfer_notes
                ]]);
    }


    public function confirmPayment($customerId)
    {
        $internetPurchase = InternetCustomerPurchase::findOrFail($customerId);
        $internetCustomers = $internetPurchase->customer->userCustomer;

        DB::beginTransaction();
        try {
            $internetPurchase->update([
                'confirmation_finance_at' => now(),
                'user_finance_id' => Auth::user()->id
            ]);

             $date = $internetPurchase->period_end ? Carbon::parse($internetPurchase->period_end) : Carbon::now();
         
            // Smart billing date calculation  
            $periodStartDate = Carbon::parse($internetPurchase->period_start);
            $maxBillingDate = config('services.internet_custom.max_billing_date', 20);
            $currentBillingDay = $periodStartDate->day;
            
            if ($currentBillingDay > $maxBillingDate) {
                $startBillingDate = $periodStartDate->copy()->addMonths($internetPurchase->payment_months)->firstOfMonth();
            } else {
                $startBillingDate = $periodStartDate->copy()->addMonths($internetPurchase->payment_months);
            }
            
            $gracePeriod = config('services.internet_custom.end_billing_of_days', 5);
            $endBillingDate = $startBillingDate->copy()->addDays($gracePeriod);
            
            $internetCustomers->update([
                'start_billing_date' => $startBillingDate->format('Y-m-d'),
                'end_billing_date' => $endBillingDate->format('Y-m-d')
            ]);

            $post =[
                'is_paid' => true,
            ];
            
            
            if(!$internetPurchase->customer->installation)
            {
                $post['status'] = ParamSchema::PROCESS_INSTALLATION;
                
                $userTechnical = optional($internetPurchase->customer->subdistrict?->coverageService?->coverageServiceOds)
                ->pluck('ods.user_assign_id')
                ->unique()
                ->all();
        
                if(count($userTechnical) > 0)
                {
                    $message = "Pembayaran Langganan Internet Untuk Kode ".$internetPurchase->customer->code." Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
                    $directUrl = route('internet-customer.show',$internetPurchase->customer->id);
                    foreach($userTechnical as $tech)
                    {
                        $this->sentInbox($tech,$message, $directUrl);
                    }
                }
            }else
            {
                $post['status'] = ParamSchema::REACTIVATED;
                dispatch(new ProvisionCustomerJob($internetPurchase->internet_customer_id));
                \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetPurchase->internet_customer_id]);
            }

            GenerateInternetPurchaseCouponJob::dispatch($internetPurchase->customer->id, $internetPurchase->id, $internetPurchase->payment_months);

            $internetPurchase->customer->update($post);
            DB::commit();
    
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Pembayaran Langganan Internet Untuk Kode '.$internetPurchase->customer->code.' Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan']);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
            DB::rollBack();
            $this->dispatchBrowserEvent('showErrorAlert', ['message' => 'Gagal mengkonfirmasi pembayaran: ' . $th->getMessage()]);
        }
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->selectedCompany = '';
        $this->selectedPackage = '';
        $this->statusFilter = '';
        $this->customerTypeFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->dateType = 'billing';
    }

    public function mount(): void
    {
        $this->mountImportOdps();
    }

    public function render()
    {
        $user = Auth::user();

        $columns = [
            'id', 'name', 'code', 'status','address',
            'internet_package_id', 'user_customer_id', 'company_id',
            'ktp_number', 'created_at','grouping_id','customer_type'
        ];
        $query = InternetCustomer::query()
            ->byCompany($user->company_id) // batasi dataset sesuai akses
            ->select($columns)
            // eager load minimal yang dipakai di blade
            ->with([
                'installation:id,internet_customer_id,device_serial_number',
                'installation.medias:id,internet_installation_id,photo,caption', // eager load photos
                'userCustomer:id,name,email,phone_number,start_billing_date,end_billing_date',
                'company:id,name',
                'internetPackage:id,name'
            ]);

        // Pencarian data
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('installation', function ($q) {
                        $q->where('device_serial_number', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('userCustomer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('phone_number', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('company', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('ktp_number', 'like', '%' . $this->search . '%')
                    ->orWhere('grouping_id', 'like', '%' . $this->search . '%')
                    ;
            });
        }

        // Filter paket internet
        if ($this->selectedPackage) {
            $query->where('internet_package_id', $this->selectedPackage);
        }

        // Filter status
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Filter tipe pelanggan
        if ($this->customerTypeFilter) {
            // dd($this->customerTypeFilter);
            $query->where('customer_type', $this->customerTypeFilter);
        }

        // Filter tanggal berdasarkan tipe yang dipilih
        if ($this->dateFrom || $this->dateTo) {
            $from = $this->dateFrom ?: '1970-01-01';
            $to   = $this->dateTo   ?: now()->toDateString();

            if ($this->dateType === 'installation') {
                $query->whereHas('installation', function ($q) use ($from, $to) {
                    $q->whereBetween('installed_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
                });
            } elseif ($this->dateType === 'registration') {
                $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            } 
            elseif ($this->dateType === 'suspended') {
                $query->whereHas('userCustomer', function ($q) use ($from, $to) {
                    $q->whereBetween('end_billing_date', [$from, $to]);
                });
            } else {
                // default: billing
                $query->whereHas('userCustomer', function ($q) use ($from, $to) {
                    $q->whereBetween('start_billing_date', [$from, $to]);
                });
            }
        }

        // Filter region — hanya berlaku untuk user dengan permission as_finance/as_marketing/as_technician
        $hasRegionPermission = Access::can('as_finance', 'internet_customers')
            || Access::can('as_marketing', 'internet_customers')
            || Access::can('as_technician', 'internet_customers')
            || Access::can('as_manager', 'internet_customers')
            ;

        if ($hasRegionPermission) {
            $userRegions = $user->internetCustomerRegions()->get();
            if ($userRegions->isNotEmpty()) {
                $query->where(function ($q) use ($userRegions) {
                    foreach ($userRegions as $region) {
                        $q->orWhere(function ($q2) use ($region) {
                            if ($region->subdistrict_id) {
                                $q2->where('subdistrict_id', $region->subdistrict_id);
                            } elseif ($region->district_id) {
                                $q2->where('district_id', $region->district_id);
                            } elseif ($region->city_id) {
                                $q2->where('city_id', $region->city_id);
                            } elseif ($region->province_id) {
                                $q2->where('province_id', $region->province_id);
                            }
                        });
                    }
                });
            }
        }

        // Sorting + paginate
        $internetCustomers = $query
            ->byCompany($user->company_id)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
        
        // Get filter options
        $packages = InternetPackage::byCompany($user->company_id)->orderBy('name')->get();

        return view('livewire.internet-customer.admin.internet-customer-index', [
            'finance_access'       => Access::can('as_finance', 'internet_customers'),
            'technical_access'     => Access::can('as_technician', 'internet_customers'),
            'internetCustomers'    => $internetCustomers,
            'packages'             => $packages,
            'routers'              => $this->routers,
            'importAvailableOdps'  => $this->importAvailableOdps,
            'radiusEnable'         => \App\Services\RadiusService::isEnabled(),
        ])->extends('adminlte::page');
    }

    protected function activate(string $id)
    {
        // validasi opsional: minta password plaintext saat pertama kali create secret
        $this->validate([
            'password' => ['nullable','string','min:6','max:64'],
        ]);

        try {
            $cust = InternetCustomer::findOrFail($id);
            // Hindari kerja sia-sia kalau sudah active
            // if ($cust->status === 'active') {
            //     $this->dispatchBrowserEvent('show-notification', [
            //         'type' => 'success',
            //         'message' => 'Customer sudah aktif'
            //     ]);
            //     return;
            // }
    
            // Update status dulu (SoT = DB)
            // $cust->update(['pass_hash' => $this->password]);
            $cust->userCustomer->update(['password' => Hash::make($this->password)]);
    
            // (opsional) catat log provisioning khusus
            JobsProvisioning::create([
                'type' => JobsProvisioning::TYPE_PROVISION,
                'internet_customer_id' => $cust->id,
                'router_id' => $cust->router_id,
                'status' => JobsProvisioning::STATUS_QUEUED,
                'payload' => [
                    'initial_plain_password' => $this->password,
                ],
            ]);
    
            dispatch(new ProvisionCustomerJob($cust->id));
    
            // Kosongkan field password input agar tidak tersisa di memori form
            $this->password = null;
    
            return true;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function reactivate(string $id)
    {
        $cust = InternetCustomer::findOrFail($id);

        if ($cust->status === ParamSchema::INSTALLED || $cust->status === ParamSchema::ACTIVE) {
            $this->dispatchBrowserEvent('show-notification', ['type'=>'info','message'=>'Customer already Installed']);
            return;
        }

        $cust->update(['status' => ParamSchema::REACTIVATED]);

        JobsProvisioning::create([
            'type' => JobsProvisioning::TYPE_SUSPEND,
            'internet_customer_id' => $cust->id,
            'router_id' => $cust->router_id,
            'status' => JobsProvisioning::STATUS_QUEUED,
            'payload' => null,
        ]);

        
        // SyncInstalledCustomersJob::dispatch([$cust->id]);
        dispatch(new ProvisionCustomerJob($cust->id));
        \App\Jobs\SyncInstalledCustomersJob::dispatch([$cust->id]);
        // dispatch(new ProvisionCustomerJob($cust->id));

        $this->dispatchBrowserEvent('show-notification', ['type'=>'success','message'=>'Reactivation dispatched']);
    }
    public function suspend(string $id)
    {
        $cust = InternetCustomer::findOrFail($id);

        if ($cust->status === ParamSchema::SUSPENDED) {
            $this->dispatchBrowserEvent('show-notification', ['type'=>'info','message'=>'Customer already suspended']);
            return;
        }

        $cust->update(['status' => ParamSchema::SUSPENDED]);

        JobsProvisioning::create([
            'type' => JobsProvisioning::TYPE_SUSPEND,
            'internet_customer_id' => $cust->id,
            'router_id' => $cust->router_id,
            'status' => JobsProvisioning::STATUS_QUEUED,
            'payload' => null,
        ]);

        
        dispatch(new ProvisionCustomerJob($cust->id));

        $this->dispatchBrowserEvent('show-notification', ['type'=>'success','message'=>'Suspension dispatched']);
    }

    public function updatedRouterId($v)
    {

        // Livewire often sends "" (empty string) or string numbers from the DOM
        $this->loadPoolsForRouter(($v === '' || $v === null) ? null : (int) $v);
    }

    public function loadPoolsForRouter($routerId): void
    {
        // Coerce incoming value (can be "", null, or string number) to int or null
        $id = (is_numeric($routerId) && $routerId !== '' && $routerId !== null) ? (int) $routerId : null;

        if (!$id) {
            $this->availablePools = [];
            return;
        }

        $this->availablePools = \App\Models\AddressPool::query()
            ->where('router_id', $id) // scope pools to the selected router
            ->orderBy('name')
            ->get(['id','name','cidr','gateway'])
            ->map(fn($p) => [
                'id'    => $p->id,
                'label' => $p->name.' — '.$p->cidr.($p->gateway ? ' (gw '.$p->gateway.')' : '')
            ])
            ->toArray();

        $this->dispatchBrowserEvent('pools-options', ['options' => $this->availablePools]);
    }

    /**
     * Approve pendaftaran pelanggan dari status pending ke process_installation
     */
    public function approvePending(string $id)
    {
        DB::beginTransaction();
        try {
            $customer = InternetCustomer::findOrFail($id);
            
            // Validasi status harus pending
            if ($customer->status !== ParamSchema::PENDING) {
                $this->dispatchBrowserEvent('show-notification', [
                    'type' => 'error',
                    'message' => 'Customer tidak dalam status pending'
                ]);
                return;
            }
        
            $this->installation($customer);
            
            DB::commit();
            
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'success',
                'message' => "Pendaftaran pelanggan {$customer->code} telah disetujui"
            ]);
            
            // Log activity
            Log::info('Customer pending approved', [
                'customer_id' => $customer->id,
                'customer_code' => $customer->code,
                'approved_by' => Auth::id()
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to approve pending customer', [
                'customer_id' => $id,
                'error' => $th->getMessage()
            ]);
            
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Gagal menyetujui pendaftaran: ' . $th->getMessage()
            ]);
        }
    }

    /**
     * Close/batalkan pendaftaran pelanggan dari status pending
     */
    public function closePending(string $id)
    {
        DB::beginTransaction();
        try {
            $customer = InternetCustomer::findOrFail($id);
            
            // Validasi status harus pending
            if ($customer->status !== ParamSchema::PENDING) {
                $this->dispatchBrowserEvent('show-notification', [
                    'type' => 'error',
                    'message' => 'Customer tidak dalam status pending'
                ]);
                return;
            }
            
            // Update status ke closed
            $customer->update([
                'status' => ParamSchema::CLOSED,
                'action_user_id' => Auth::id() // opsional: tambahkan kolom ini di migration jika perlu
            ]);
            
            // Kirim notifikasi ke customer user jika ada
            // if ($customer->user_customer_id) {
            //     $message = "Pendaftaran Anda dengan kode {$customer->code} telah ditutup/dibatalkan oleh admin.";
            //     $directUrl = route('internet-customer.show', $customer->id);
            //     $this->sentInbox($customer->user_customer_id, $message, $directUrl);
            // }
            
            DB::commit();
            
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'success',
                'message' => "Pendaftaran pelanggan {$customer->code} telah ditutup"
            ]);
            
            // Log activity
            Log::info('Customer pending closed', [
                'customer_id' => $customer->id,
                'customer_code' => $customer->code,
                'closed_by' => Auth::id()
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to close pending customer', [
                'customer_id' => $id,
                'error' => $th->getMessage()
            ]);
            
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Gagal menutup pendaftaran: ' . $th->getMessage()
            ]);
        }
    }

    // GANTI method yang ada dengan:
    protected function checkNewUsernameAvailability($username)
    {
        $existing = InternetCustomer::where('username', $username)
            ->when($this->currentInstallationId, function($q) {
                $q->where('id', '!=', $this->currentInstallationId);
            })
            ->first(['id', 'code', 'name', 'status']);

        $this->newUsernameChecked = true;
        
        if ($existing) {
            $this->newUsernameAvailable = false;
            $this->newUsernameExistingCustomer = [
                'id' => $existing->id,
                'code' => $existing->code,
                'name' => $existing->name,
            ];
            
            // ✅ EMIT event ke JavaScript
            $this->dispatchBrowserEvent('usernameCheckComplete', [
                'available' => false,
                'existing' => [
                    'code' => $existing->code,
                    'name' => $existing->name,
                ]
            ]);
        } else {
            $this->newUsernameAvailable = true;
            $this->newUsernameExistingCustomer = [];
            
            // ✅ EMIT event ke JavaScript
            $this->dispatchBrowserEvent('usernameCheckComplete', [
                'available' => true
            ]);
        }
    }

    public function checkGroupingIdAvailability(?string $value): void
    {
        $value = trim($value ?? '');

        if (strlen($value) < 2) {
            $this->dispatchBrowserEvent('groupingIdCheckComplete', ['available' => true]);
            return;
        }

        $existing = InternetCustomer::where('grouping_id', $value)
            ->when($this->currentInstallationId, fn($q) => $q->where('id', '!=', $this->currentInstallationId))
            ->first(['id', 'code', 'name']);

        if ($existing) {
            $this->dispatchBrowserEvent('groupingIdCheckComplete', [
                'available' => false,
                'existing'  => ['code' => $existing->code, 'name' => $existing->name],
            ]);
        } else {
            $this->dispatchBrowserEvent('groupingIdCheckComplete', ['available' => true]);
        }
    }

    // TAMBAHKAN method baru:
    public function updatedLocalAddress($value)
    {
        $this->validate([
            'local_address' => 'nullable|ip'
        ]);
        
        if (!$value) {
            return;
        }
        
        $this->checkLocalAddressAvailability($value);
    }

    protected function checkLocalAddressAvailability($ip)
    {
        $existing = InternetCustomer::where('local_address', $ip)
            ->when($this->currentInstallationId, function($q) {
                $q->where('id', '!=', $this->currentInstallationId);
            })
            ->first(['id', 'code', 'name']);
        
        if ($existing) {
            $errorMsg = "IP {$ip} sudah digunakan oleh {$existing->code} - {$existing->name}";
            $this->addError('local_address', $errorMsg);
            
            // ✅ EMIT event ke JavaScript
            $this->dispatchBrowserEvent('localAddressCheckComplete', [
                'valid' => false,
                'message' => $errorMsg
            ]);
        } else {
            // ✅ EMIT event ke JavaScript
            $this->dispatchBrowserEvent('localAddressCheckComplete', [
                'valid' => true
            ]);
        }
    }

    private function sentInbox($to,$message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            Auth::user()->id, 
            $message, 
            $directUrl
        );
        return true;
    }

    private function installation($customer)
    {
        try {
            $customer->update([
                'status' => ParamSchema::PROCESS_INSTALLATION,
                'action_user_id' => Auth::id() // opsional: tambahkan kolom ini di migration jika perlu
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
                    $this->sentInbox($tech, $message, $directUrl);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

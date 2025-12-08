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

use App\Jobs\ProvisionCustomerJob;
use App\Jobs\GenerateInternetPurchaseCouponJob;

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
    
    // BARU: Properties untuk ODP dan Grouping
    public ?string $optical_distribution_id = null;
    public ?string $grouping_id = null;
    public array $availableOdps = [];

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedCompany = '';
    public $selectedPackage = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
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

    public $canApprove;
    public $canTechnical;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage',
        'sortField',
        'sortDirection',
        'selectedCompany',
        'selectedPackage',
        'statusFilter',
        'dateFrom',
        'dateTo'
    ];

    // Method untuk membuka modal
    // BARU: Method untuk load ODP berdasarkan customer
    public function openInstallationModal(string $customerId)
    {
        $cust = InternetCustomer::with([
            'internetPackage',
            'subdistrict.coverageService.coverageServiceOds.ods.pops.routers',
        ])->findOrFail($customerId);
            
        $this->currentInstallationId = $cust->id;

        // === FILTER ROUTER ===
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

        // BARU: Load ODP dari CoverageService
        $this->loadOdpsForCustomer($cust);
        
        $payload = [
            'customerName'  => $cust->name,
            'customerCode'  => $cust->code,
            'serialNumber'  => '',
            'routers'       => $routers->map(fn($r) => [
                'id'   => $r->id,
                'disabled' => $r->is_online ? false : true,
                'name' => $r->name . ' (PPPoE: '.$r->pppoe_servers_count.')',
            ])->values(),
            'odps' => $this->availableOdps, // BARU
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
        $optical_distribution_id = null,  // BARU
        $grouping_id = null                // BARU
        ) {
        // Update properties dari parameter
        $this->deviceSerialNumber = $serialNumber;
        $this->installationNotes = $notes;
        $this->router_id = $routerId;
        $this->username = $username;
        $this->password = $password;
        $this->override_pool_id = $override_pool_id;
        $this->local_address = $local_address;
        $this->optical_distribution_id = $optical_distribution_id;  // BARU
        $this->grouping_id = $grouping_id;                          // BARU
        
        Log::info('completeInstallation called', [
            'serialNumber' => $serialNumber,
            'notes' => $notes,
            'photos_count' => count($this->photos),
            'currentInstallationId' => $this->currentInstallationId,
            'optical_distribution_id' => $optical_distribution_id,  // BARU
            'grouping_id' => $grouping_id,                          // BARU
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
        
        // UPDATED: Validasi dengan field baru
        $validated = $this->validate([
            'currentInstallationId' => 'required|exists:internet_customers,id',
            'deviceSerialNumber' => 'required|string|max:255',
            'router_id' => 'required|exists:routers,id',
            'username' => 'required|unique:internet_customers,username',
            'password' => 'required',
            'local_address' => 'nullable|ip|unique:internet_customers,local_address',
            'optical_distribution_id' => 'required|exists:optical_distributions,id',  // BARU: Wajib
            'grouping_id' => 'nullable|string|max:255',                                // BARU: Opsional
        ], [
            'deviceSerialNumber.required' => 'Serial Number wajib diisi',
            'currentInstallationId.required' => 'Customer ID tidak valid',
            'currentInstallationId.exists' => 'Customer tidak ditemukan',
            'username.unique' => 'Username PPPoE sudah digunakan',
            'local_address.unique' => 'Alamat IP lokal sudah terdaftar',
            'optical_distribution_id.required' => 'ODP wajib dipilih',           // BARU
            'optical_distribution_id.exists' => 'ODP tidak valid',               // BARU
        ]);

        DB::beginTransaction();
        try {
            // 1. Ambil customer
            $customer = InternetCustomer::findOrFail($this->currentInstallationId);
            
            Log::info('Customer found', ['id' => $customer->id, 'code' => $customer->code]);
            
            // 2. UPDATED: Update customer dengan field baru
            $customer->update([
                'status' => ParamSchema::INSTALLED,
                'local_address' => $local_address,
                'router_id' => $routerId,
                'username' => $username,
                'pass_hash' => $password,
                'override_pool_id' => $override_pool_id ?: null,
                'optical_distribution_id' => $optical_distribution_id,  // BARU
            ]);
            
            dispatch(new \App\Jobs\ProvisionCustomerJob($customer->id));

            // 3. UPDATED: Buat record installation dengan field baru
            $customerInstallation = InternetCustomerInstallation::create([
                'internet_customer_id' => $customer->id,
                'device_serial_number' => $serialNumber,
                'notes' => $notes,
                'installed_at' => now(),
                'technical_user_id' => Auth::id(),
                'grouping_id' => $grouping_id,                          // BARU
            ]);

            $this->activate($customer->id, $password);
            
            Log::info('Installation record created', [
                'id' => $customerInstallation->id,
                'odp_id' => $optical_distribution_id,
                'grouping' => $grouping_id
            ]);

            // 4. Upload foto (sama seperti sebelumnya)
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
                        $extension = pathinfo($photo, PATHINFO_EXTENSION);
                        if (empty($extension)) {
                            $extension = 'png';
                        }
                        
                        $filename = uniqid() . '_' . time() . '_' . $index . '.' . $extension;
                        $s3Path = 'installation-photos/' . $customer->code . '/' . $filename;
                        
                        Storage::disk('s3')->put($s3Path, $fileContent, 'public');
                        
                        $photoRecord = InternetInstallationPhoto::create([
                            'internet_installation_id' => $customerInstallation->id,
                            'photo' => $s3Path,
                            'caption' => 'Installation Photo ' . ($photoCount + 1),
                        ]);
                        
                        @unlink($tmpPath);
                        $photoCount++;
                        
                    } elseif ($photo instanceof \Illuminate\Http\UploadedFile) {
                        $extension = $photo->getClientOriginalExtension();
                        $filename = uniqid() . '_' . time() . '_' . $index . '.' . $extension;
                        
                        $path = $photo->storeAs(
                            'installation-photos/' . $customer->code,
                            $filename,
                            's3'
                        );
                        
                        Storage::disk('s3')->setVisibility($path, 'public');
                        
                        InternetInstallationPhoto::create([
                            'internet_installation_id' => $customerInstallation->id,
                            'photo' => $path,
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
            
            Log::info('Installation completed successfully', [
                'customer_id' => $customer->id,
                'photos_uploaded' => $photoCount,
                'odp_id' => $optical_distribution_id,
                'grouping' => $grouping_id
            ]);

            // Reset form
            $this->reset([
                'installationModal',
                'currentInstallationId',
                'currentInstallationName',
                'currentInstallationCode',
                'deviceSerialNumber',
                'installationNotes',
                'photos',
                'optical_distribution_id',  // BARU
                'grouping_id',              // BARU
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'success',
                'message' => "Instalasi berhasil disimpan dengan {$photoCount} foto"
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Installation failed', [
                'customer_id' => $this->currentInstallationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Gagal menyimpan instalasi: ' . $e->getMessage()
            ]);
            
            return false;
        }
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



    public function viewPaymentProof($proofUrl)
    {
        $this->selectedPaymentProof = $proofUrl ? s3_asset(true,10,$proofUrl) : null;
        
        $proofUrl= $proofUrl ? s3_asset(true,10,$proofUrl) : null;
    
        // Dispatch kedua jenis event
        $this->dispatchBrowserEvent('showPaymentProofModal', ['proofUrl' => $proofUrl]);
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
        
            $internetCustomers->update([
                'start_billing_date' => $date->addMonth()->firstOfMonth()->format('Y-m-d'),
                'end_billing_date' => $date->addDays(config('services.internet_custom.end_billing_of_days'))->format('Y-m-d')
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
                dispatch(new ProvisionCustomerJob($internetCustomers->id));
                \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetCustomers->id]);
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
        $this->dateFrom = '';
        $this->dateTo = '';
    }

    public function render()
    {
        $user = Auth::user();

        $columns = [
            'id', 'name', 'code', 'status','address',
            'internet_package_id', 'user_customer_id', 'company_id',
            'ktp_number', 'created_at'
        ];
        $query = InternetCustomer::query()
            ->byCompany($user->company_id) // batasi dataset sesuai akses
            ->select($columns)
            // eager load minimal yang dipakai di blade
            ->with([
                'installation:id,internet_customer_id,device_serial_number',
                'installation.medias:id,internet_installation_id,photo,caption', // eager load photos
                'userCustomer:id,name,email,phone_number',
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
                    ->orWhere('ktp_number', 'like', '%' . $this->search . '%');
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

        // Filter tanggal (gabung jadi range)
        if ($this->dateFrom || $this->dateTo) {
            $from = $this->dateFrom ?: '1970-01-01';
            $to   = $this->dateTo   ?: now()->toDateString();
            $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }

        // Sorting + paginate
        $internetCustomers = $query
            ->byCompany($user->company_id)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();

        // Sorting
        $internetCustomers = $query->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);
        
        // Get filter options
        $packages = InternetPackage::byCompany($user->company_id)->orderBy('name')->get();

        return view('livewire.internet-customer.admin.internet-customer-index', [
            'finance_access'    => Access::can('as_finance', 'internet_customers'),
            'technical_access'  => Access::can('as_technician', 'internet_customers'),
            'internetCustomers' => $internetCustomers,
            'packages'          => $packages,
            'routers'           => $this->routers,
            
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
                    $this->sentInbox($tech,$from->id, $message, $directUrl);
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

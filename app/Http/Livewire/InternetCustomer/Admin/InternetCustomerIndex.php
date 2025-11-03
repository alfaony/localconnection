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

use App\Jobs\ProvisionCustomerJob;

use App\Models\InternetInstallationPhoto;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Helpers\InboxHelper;
use App\Helpers\Access;
use App\Schemas\ParamSchema;

use Carbon\Carbon;

class InternetCustomerIndex extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'bootstrap';

    public ?string $override_pool_id = null;
    public array $availablePools = [];
    

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

    public $plain_password = null; // di-bind dari modal/input ketika activate

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
    public function openInstallationModal(string $customerId)
    {
        $cust = InternetCustomer::with([
            'internetPackage',
            'subdistrict.coverageService.coverageServiceOds.ods.pops.routers',
        ])->findOrFail($customerId);
            
        $this->currentInstallationId = $cust->id;

        // === FILTER ROUTER ===
        // 1) Ambil router yang terkait cakupan pelanggan (OD -> POP -> Routers)
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
            // fallback: semua router aktif yang punya PPPoE server
            $routers = Router::query()
                // ->where('active','UP')
                ->whereHas('pppoeServers')
                ->orderBy('name')
                ->get(['id','name']);
        }else
        {
            // 2) Query router aktif + (opsional) punya PPPoE server & pool
            $routers = Router::query()
            ->whereIn('id', $routerIds)
            // ->where('active', 'UP')
            ->whereHas('pppoeServers', fn($q) => $q->whereNotNull('address_pool_id'))
            ->whereHas('addressPools') // jika pakai address_pools.router_id
            ->withCount(['pppoeServers' => fn($q) => $q->whereNotNull('address_pool_id')])
            ->orderBy('name')
            ->get(['id','name']);
        }

        

        // 3) Siapkan data untuk modal
        $payload = [
            'customerName'  => $cust->name,
            'customerCode'  => $cust->code,
            'serialNumber'  => '',                  // kalau ada default isikan di sini
            'routers'       => $routers->map(fn($r) => [
                'id'   => $r->id,
                'name' => $r->name . ' (PPPoE: '.$r->pppoe_servers_count.')',
            ])->values(),
        ];

        // kirim ke JS (Blade kamu sudah listen event ini)
        $this->dispatchBrowserEvent('open-installation-modal', $payload);
    }

    // Method untuk validasi dan submit
    // public function completeInstallation($serialNumber, $photos, $notes, $routerId, $username, $password, $override_pool_id, $local_address)
    // {
        // Validator::make([
        //     'currentInstallationId' => $this->currentInstallationId,
        //     'serialNumber' => $serialNumber,
        //     'photos' => $photos,
        //     'routerId' => $routerId,
        //     'username' => $username,
        //     'password' => $password,
        //     'override_pool_id' => $override_pool_id,
        //     'local_address' => $local_address
        // ], [
        //     'override_pool_id' => 'nullable|exists:address_pools,id',
        //     'currentInstallationId' => 'required|exists:internet_customers,id',
        //     'photos' => 'required|array|min:1',
        //     'photos.*' => 'required|string',
        //     'routerId' => 'required|exists:routers,id',
        //     'username' => 'required',
        //     'password' => 'required',
        //     'local_address' => 'nullable|ip',
        // ])->validate();

        // if (!$customer) {
        //     $this->dispatchBrowserEvent('show-notification', [
        //         'type' => 'error',
        //         'message' => 'Customer tidak ditemukan'
        //     ]);
        //     return;
        // }
        
        // // Reset photos array
        // $this->photos = [];
        
        // $this->currentInstallationId = $customer->id;
        // $this->currentInstallationName = $customer->name;
        // $this->currentInstallationCode = $customer->code;
        // $this->deviceSerialNumber = $customer->device_serial_number ?? '';
        // $this->installationNotes = '';
        
    //     $this->dispatchBrowserEvent('open-installation-modal', [
    //         'customerName' => $customer->name,
    //         'customerCode' => $customer->code,
    //         'serialNumber' => $customer->device_serial_number ?? ''
    //     ]);
        
    //     $this->installationModal = true;
    // }

    // Method untuk validasi dan submit
    public function completeInstallation($serialNumber, $notes, $routerId, $username, $password, $override_pool_id, $local_address)
    {
        // Update properties dari parameter
        $this->deviceSerialNumber = $serialNumber;
        $this->installationNotes = $notes;
        $this->router_id = $routerId;
        $this->username = $username;
        $this->password = $password;
        $this->override_pool_id = $override_pool_id;
        $this->local_address = $local_address;
        
        // DEBUG: Log photos property
        Log::info('completeInstallation called', [
            'serialNumber' => $serialNumber,
            'notes' => $notes,
            'photos_count' => count($this->photos),
            'photos_raw' => $this->photos,
            'currentInstallationId' => $this->currentInstallationId
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
        
        // Filter hanya yang ada nilai (bukan null)
        $validPhotos = array_filter($this->photos, function($photo) {
            return $photo !== null;
        });
        
        Log::info('Valid photos after filter', [
            'count' => count($validPhotos),
            'photos' => $validPhotos
        ]);
        
        if (empty($validPhotos)) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Tidak ada foto yang valid. Silakan coba lagi.'
            ]);
            return false;
        }
        
        // Validasi basic (tanpa validasi file dulu, karena masih string temporary)
        $validated = $this->validate([
            'currentInstallationId' => 'required|exists:internet_customers,id',
            'deviceSerialNumber' => 'required|string|max:255',
            'currentInstallationId' => 'required|exists:internet_customers,id',
            'router_id' => 'required|exists:routers,id',
            'username' => 'required',
            'password' => 'required',
            'local_address' => 'nullable|ip',
        ], [
            'deviceSerialNumber.required' => 'Serial Number wajib diisi',
            'currentInstallationId.required' => 'Customer ID tidak valid',
            'currentInstallationId.exists' => 'Customer tidak ditemukan',
        ]);

        DB::beginTransaction();
        try {
            // 1. Ambil customer
            $customer = InternetCustomer::findOrFail($this->currentInstallationId);
            
            Log::info('Customer found', ['id' => $customer->id, 'code' => $customer->code]);
            
            // 2. Update status customer
            $customer->update([
                'status' => ParamSchema::INSTALLED,
                'local_address' => $local_address,
                'router_id' => $routerId,
                'username' => $username,
                'pass_hash' => $password,
                'override_pool_id'  => $override_pool_id ?: null,
            ]);
            
            dispatch(new \App\Jobs\ProvisionCustomerJob($customer->id));

            // 3. Buat record installation
            $customerInstallation = InternetCustomerInstallation::create([
                'internet_customer_id' => $customer->id,
                'device_serial_number' => $serialNumber,
                'notes' => $notes,
                'installed_at' => now(),
                'technical_user_id' => Auth::id(),
            ]);

            $this->activate($customer->id, $password);
            
            Log::info('Installation record created', ['id' => $customerInstallation->id]);

            // 4. Upload foto dari Livewire temporary ke S3 dan simpan ke database
            $photoCount = 0;
            
            foreach ($validPhotos as $index => $photo) {
                Log::info('Processing photo', [
                    'index' => $index,
                    'photo_value' => $photo,
                    'photo_type' => gettype($photo),
                ]);
                
                try {
                    // Jika masih berupa string (temporary filename dari Livewire)
                    // Kita perlu mengambil UploadedFile dari Livewire
                    if (is_string($photo)) {
                        // Livewire menyimpan temporary file di storage/app/livewire-tmp
                        $tmpPath = storage_path('app/livewire-tmp/' . $photo);
                        
                        if (!file_exists($tmpPath)) {
                            Log::error('Temporary file not found', ['path' => $tmpPath]);
                            continue;
                        }
                        
                        // Baca file content
                        $fileContent = file_get_contents($tmpPath);
                        
                        // Extract extension dari filename
                        $extension = pathinfo($photo, PATHINFO_EXTENSION);
                        if (empty($extension)) {
                            $extension = 'png'; // default
                        }
                        
                        // Generate unique filename untuk S3
                        $filename = uniqid() . '_' . time() . '_' . $index . '.' . $extension;
                        $s3Path = 'installation-photos/' . $customer->code . '/' . $filename;
                        
                        Log::info('Uploading to S3', [
                            'from' => $tmpPath,
                            'to' => $s3Path
                        ]);
                        
                        // Upload ke S3
                        Storage::disk('s3')->put($s3Path, $fileContent, 'public');
                        
                        Log::info('Photo uploaded to S3', ['path' => $s3Path]);
                        
                        // Simpan record foto ke database
                        $photoRecord = InternetInstallationPhoto::create([
                            'internet_installation_id' => $customerInstallation->id,
                            'photo' => $s3Path,
                            'caption' => 'Installation Photo ' . ($photoCount + 1),
                        ]);
                        
                        Log::info('Photo record created', ['id' => $photoRecord->id]);
                        
                        // Hapus temporary file
                        @unlink($tmpPath);
                        
                        $photoCount++;
                        
                    } 
                    // Jika sudah UploadedFile object
                    elseif ($photo instanceof \Illuminate\Http\UploadedFile) {
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
                    
                } catch (\Exception $photoError) {
                    Log::error('Failed to process photo', [
                        'index' => $index,
                        'error' => $photoError->getMessage(),
                        'trace' => $photoError->getTraceAsString()
                    ]);
                    continue;
                }
            }

            // Validasi minimal ada 1 foto yang berhasil diupload
            if ($photoCount === 0) {
                throw new \Exception('Tidak ada foto yang berhasil diupload. Silakan coba lagi.');
            }

            DB::commit();
            
            Log::info('Installation completed successfully', [
                'customer_id' => $customer->id,
                'photos_uploaded' => $photoCount
            ]);

            // Reset form dan properties
            $this->reset([
                'installationModal',
                'currentInstallationId',
                'currentInstallationName',
                'currentInstallationCode',
                'deviceSerialNumber',
                'installationNotes',
                'photos'
            ]);

            // Kirim notifikasi success
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
        $this->selectedPaymentProof = $proofUrl;
    
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

            $startDate = Carbon::parse($internetCustomers->start_billing_date);
            if ($startDate->isSameMonth(now())) {
                $date = $startDate; // tetap Carbon object
            } else {
                $date = now();
            }
            
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
            }
    
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

        
        dispatch(new ProvisionCustomerJob($cust->id));

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
}

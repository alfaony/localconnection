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


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Helpers\InboxHelper;
use App\Helpers\Access;
use App\Schemas\ParamSchema;

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
    public $installationCustomerName, $installationCustomerCode, $installationCustomerId;
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
    public function completeInstallation($serialNumber, $photos, $notes, $routerId, $username, $password, $override_pool_id)
    {
        Validator::make([
            'currentInstallationId' => $this->currentInstallationId,
            'serialNumber' => $serialNumber,
            'photos' => $photos,
            'routerId' => $routerId,
            'username' => $username,
            'password' => $password,
            'override_pool_id' => $override_pool_id
        ], [
            'override_pool_id' => 'nullable|exists:address_pools,id',
            'currentInstallationId' => 'required|exists:internet_customers,id',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|string',
            'routerId' => 'required|exists:routers,id',
            'username' => 'required',
            'password' => 'required',
        ])->validate();


        try {
            // Upload foto
            $uploadedPaths = [];

            foreach ($photos as $tmpFilename) {
                $tmpPath = storage_path('app/livewire-tmp/' . $tmpFilename);
                $newPath = 'installation-photos/' . uniqid() . '-' . basename($tmpFilename);

                // Pindahkan dari tmp ke public/installation-photos
                Storage::put(
                    "public/" . $newPath,
                    file_get_contents($tmpPath)
                );

                $uploadedPaths[] = $newPath;

                // Hapus file tmp
                unlink($tmpPath);
            }
            
            // Update data
            $customer = InternetCustomer::find($this->currentInstallationId);
            $customer->update([
                'status' => ParamSchema::INSTALLED, 
                'router_id' => $routerId,
                'username' => $username,
                'pass_hash' => $password,
                'override_pool_id'  => $override_pool_id ?: null,
            ]);

            dispatch(new \App\Jobs\ProvisionCustomerJob($customer->id));

            $customerInstallation = InternetCustomerInstallation::create([
                'internet_customer_id' => $customer->id,
                'photos' => json_encode($uploadedPaths),
                'device_serial_number' => $serialNumber,
                'notes' => $notes,
                'installed_at' => now(),
                'technical_user_id' => Auth::id(),
            ]);
            
            $this->activate($customer->id, $password);

            // Reset form
            $this->reset([
                'installationModal',
                'currentInstallationId',
                'currentInstallationName',
                'currentInstallationCode',
                'deviceSerialNumber',
                'installationNotes',
                'photos'
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'success',
                'message' => 'Instalasi berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            // dd($e);
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

        DB::beginTransaction();
        try {
            $internetPurchase->update([
                'confirmation_finance_at' => now(),
                'user_finance_id' => Auth::user()->id
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
            }
    
            $internetPurchase->customer->update($post);
            DB::commit();
    
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Pembayaran Langganan Internet Untuk Kode '.$internetPurchase->customer->code.' Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan']);
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
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

        // kolom yang memang dipakai di tabel/index
        $columns = [
            'id', 'name', 'code', 'status',
            'internet_package_id', 'user_customer_id', 'company_id',
            'ktp_number', 'created_at'
        ];

        // whitelist kolom sort
        $allowedSorts = ['created_at', 'name', 'status', 'code'];
        if (!in_array($this->sortField, $allowedSorts, true)) {
            $this->sortField = 'created_at';
        }
        $this->sortDirection = strtolower($this->sortDirection) === 'asc' ? 'asc' : 'desc';

        $query = InternetCustomer::query()
            ->byCompany($user->company_id) // batasi dataset sesuai akses
            ->select($columns)
            // eager load minimal yang dipakai di blade
            ->with([
                'installation:id,internet_customer_id,device_serial_number',
                'userCustomer:id,name,email,phone_number',
                'company:id,name',
                'internetPackage:id,name'
            ]);

        // Pencarian
        if ($this->search !== '') {
            $term = trim($this->search);
            $like = "%{$term}%";

            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                ->orWhere('code', 'like', $like)
                ->orWhere('ktp_number', 'like', $like)
                ->orWhereHas('installation', function ($q) use ($like) {
                    $q->where('device_serial_number', 'like', $like);
                })
                ->orWhereHas('userCustomer', function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone_number', 'like', $like);
                })
                ->orWhereHas('company', function ($q) use ($like) {
                    $q->where('name', 'like', $like);
                });
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

        $packages = InternetPackage::byCompany($user->company_id)
            ->orderBy('name')
            ->get(['id','name']);

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
    

    // Tambahkan fungsi lainnya (delete, edit, dll) sesuai kebutuhan
}

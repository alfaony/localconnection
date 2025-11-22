<?php

namespace App\Http\Livewire\InternetCustomer\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Jobs\ProvisionCustomerJob;
use App\Jobs\GenerateInternetPurchaseCouponJob;
use App\Jobs\ProcessRouterMoveJob;
use App\Jobs\GenerateIsolirJob;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerPurchase;
use App\Models\Router;
use App\Models\AddressPool;
use App\Models\InternetPackage;

use App\Jobs\GenerateBillingJob;
use App\Schemas\ParamSchema;
use Carbon\Carbon;

use App\Helpers\Access;
use App\Helpers\InboxHelper;

class InternetCustomerShow extends Component
{
    use WithPagination;

    public $customer;
    public $agreementFields;
    public $paymentProofUrl;
    public $showPaymentProofModal = false;
    public $ktpPhotoUrl;
    public $showKtpModal = false;
    public $installationPhotos = [];
    public $showInstallationPhotosModal = false;

    // Properties untuk edit data pribadi
    public $name, $email, $phone_number, $start_billing_date, $end_billing_date;
    
    // Properties untuk edit data instalasi
    public $local_address, $username, $pass_hash, $device_serial_number;
    
    // ✅ Properties untuk move router
    public $new_router_id = null;
    public $new_username = null;
    public $new_local_address = null;
    public $new_pool_id = null;
    public $availableRouters = [];
    public $availablePoolsForNewRouter = [];
    
    // ✅ Validation for new router configuration
    public $newUsernameChecked = false;
    public $newUsernameAvailable = false;
    public $newUsernameExistingCustomer = [];
    
    public $newLocalAddressChecked = false;
    public $newLocalAddressAvailable = true;
    public $newLocalAddressExistingCustomer = [];

    public $editPackageModal = false;
    public $new_package_id = null;
    public $availablePackages = [];

    public function mount($customerId)
    {
        $this->customer = InternetCustomer::with([
            'company',
            'province',
            'city',
            'district',
            'subdistrict',
            'internetPackage',
            'partnershipAgreement',
            'userCustomer',
            'purchases',
            'installation',
            'router',
        ])->findOrFail($customerId);

        if ($this->customer->partnershipAgreement) {
            $this->agreementFields = json_decode($this->customer->partnershipAgreement->fields, true);
        }

        $this->ktpPhotoUrl = $this->customer->ktp_photo;

        if ($this->customer->installation && $this->customer->installation->medias->count() > 0) {
            $this->installationPhotos = $this->customer->installation->medias
                ->pluck('photo')
                ->toArray();
        }

        $this->availablePackages = InternetPackage::byCompany(Auth::user()->company_id)
        ->where('is_active', true)
        ->get();
    }

    // ========================================
    // MOVE ROUTER METHODS
    // ========================================

    /**
     * Open move router modal
     */
    public function openMoveRouterModal()
    {
        // Load available routers (exclude current)
        $this->availableRouters = Router::where('id', '!=', $this->customer->router_id)
            ->whereHas('pppoeServers')
            ->orderBy('name')
            ->get(['id', 'name', 'active_status'])
            ->toArray();

        // Reset form
        $this->reset([
            'new_router_id',
            'new_username',
            'new_local_address',
            'new_pool_id',
            'availablePoolsForNewRouter',
            'newUsernameChecked',
            'newUsernameAvailable',
            'newLocalAddressChecked',
            'newLocalAddressAvailable',
        ]);

        $this->dispatchBrowserEvent('open-move-router-modal');
    }

    /**
     * Watch for new_router_id changes
     */
    public function updatedNewRouterId($value)
    {
        $this->new_pool_id = null;
        $this->availablePoolsForNewRouter = [];
        
        if ($value) {
            $this->loadPoolsForNewRouter($value);
            
            // Re-check local_address if exists
            if ($this->new_local_address) {
                $this->checkNewLocalAddressAvailability($this->new_local_address, $value);
            }
        }
    }

    /**
     * Watch for new_username changes
     */
    public function updatedNewUsername($value)
    {
        $this->newUsernameChecked = false;
        $this->newUsernameAvailable = false;
        $this->newUsernameExistingCustomer = [];

        if (!$value || strlen($value) < 3) {
            return;
        }

        $this->checkNewUsernameAvailability($value);
    }

    /**
     * Watch for new_local_address changes
     */
    public function updatedNewLocalAddress($value)
    {
        $this->newLocalAddressChecked = false;
        $this->newLocalAddressAvailable = true;
        $this->newLocalAddressExistingCustomer = [];

        if (!$value) {
            return;
        }

        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            $this->newLocalAddressChecked = true;
            $this->newLocalAddressAvailable = false;
            $this->addError('new_local_address', 'Format IP address tidak valid');
            return;
        }

        if ($this->new_router_id) {
            $this->checkNewLocalAddressAvailability($value, $this->new_router_id);
        }
    }

    /**
     * ✅ Load address pools for new router
     */
    public function loadPoolsForNewRouter($routerId)
    {
        $pools = AddressPool::where('router_id', $routerId)
            ->orderBy('name')
            ->get(['id', 'name', 'cidr', 'gateway']);
        
        $this->availablePoolsForNewRouter = $pools->map(function($pool) {
            return [
                'id' => $pool->id,
                'name' => $pool->name,
                'label' => $pool->name . ' — ' . $pool->cidr . ($pool->gateway ? ' (gw: ' . $pool->gateway . ')' : '')
            ];
        })->toArray();
    }

    /**
     * Check new username availability
     */
    protected function checkNewUsernameAvailability($username)
    {
        $existing = InternetCustomer::where('username', $username)
            ->where('id', '!=', $this->customer->id)
            ->first(['id', 'code', 'name', 'status']);

        $this->newUsernameChecked = true;
        
        if ($existing) {
            $this->newUsernameAvailable = false;
            $this->newUsernameExistingCustomer = [
                'id' => $existing->id,
                'code' => $existing->code,
                'name' => $existing->name,
            ];
        } else {
            $this->newUsernameAvailable = true;
            $this->newUsernameExistingCustomer = [];
        }
    }

    /**
     * Check new local address availability
     */
    protected function checkNewLocalAddressAvailability($localAddress, $routerId)
    {
        $existing = InternetCustomer::where('local_address', $localAddress)
            ->where('router_id', $routerId)
            ->where('id', '!=', $this->customer->id)
            ->first(['id', 'code', 'name', 'username']);

        $this->newLocalAddressChecked = true;
        
        if ($existing) {
            $this->newLocalAddressAvailable = false;
            $this->newLocalAddressExistingCustomer = [
                'id' => $existing->id,
                'code' => $existing->code,
                'name' => $existing->name,
                'username' => $existing->username,
            ];
        } else {
            $this->newLocalAddressAvailable = true;
            $this->newLocalAddressExistingCustomer = [];
        }
    }

    /**
     * Submit move router
     */
    public function submitMoveRouter()
    {
        // Validation
        $this->validate([
            'new_router_id' => 'required|exists:routers,id|different:customer.router_id',
            'new_pool_id' => 'nullable|exists:address_pools,id',
            'new_username' => [
                'nullable',
                'string',
                'min:3',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && !$this->newUsernameAvailable) {
                        $fail('Username sudah digunakan');
                    }
                },
            ],
            'new_local_address' => [
                'nullable',
                'ip',
                function ($attribute, $value, $fail) {
                    if ($value && !$this->newLocalAddressAvailable) {
                        $fail('Local address sudah digunakan');
                    }
                },
            ],
        ], [
            'new_router_id.required' => 'Router tujuan harus dipilih',
            'new_router_id.different' => 'Router tujuan harus berbeda dari router saat ini',
            'new_pool_id.exists' => 'Address pool tidak valid',
        ]);

        try {
            ProcessRouterMoveJob::dispatch($this->customer->id, $this->customer->router_id, $this->new_router_id, $this->new_username, $this->new_local_address, $this->new_pool_id);
            
            // dd("here");
            $this->dispatchBrowserEvent('close-move-router-modal');
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'success',
                'message' => 'Proses perpindahan router sedang dijalankan di background'
            ]);

            // Refresh after delay
            $this->dispatchBrowserEvent('refresh-after-delay', ['delay' => 2000]);

        } catch (\Exception $e) {
            // dd($e);dis
            Log::error('Failed to dispatch router move', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customer->id,
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Gagal memproses perpindahan: ' . $e->getMessage()
            ]);
        }
    }

    // ========================================
    // KTP DOWNLOAD METHOD
    // ========================================
    
    /**
     * Download KTP Photo
     */
    public function downloadKtpPhoto()
    {
        if (!$this->ktpPhotoUrl) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Foto KTP tidak tersedia'
            ]);
            return;
        }
        
        try {
            $url = s3_asset(true, 10, $this->ktpPhotoUrl);
            $filename = 'KTP_' . $this->customer->code . '_' . str_replace(' ', '_', $this->customer->name) . '.jpg';
            
            $this->dispatchBrowserEvent('download-file', [
                'url' => $url,
                'filename' => $filename
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to download KTP', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customer->id
            ]);
            
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Gagal mendownload foto KTP'
            ]);
        }
    }

    // ========================================
    // EXISTING METHODS (unchanged)
    // ========================================

    public function openEditPribadiModal()
    {
        $this->name = $this->customer->name;
        $this->email = $this->customer->userCustomer->email ?? '';
        $this->phone_number = $this->customer->userCustomer->phone_number ?? '';
        $this->start_billing_date = $this->customer->userCustomer->start_billing_date ?? '';
        $this->end_billing_date = $this->customer->userCustomer->end_billing_date ?? '';
        
        $this->dispatchBrowserEvent('showEditPribadiModal', [
            'name' => $this->customer->name,
            'email' => $this->customer->userCustomer->email ?? '',
            'phone_number' => $this->customer->userCustomer->phone_number ?? '',
            'start_billing_date' => $this->customer->userCustomer->start_billing_date ?? '',
            'end_billing_date' => $this->customer->userCustomer->end_billing_date ?? '',
        ]);
    }

    public function savePribadi()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'start_billing_date' => 'nullable|date',
            'end_billing_date' => 'nullable|date|after:start_billing_date',
        ]);

        DB::beginTransaction();
        try {
            $this->customer->update(['name' => $this->name]);

            if ($this->customer->userCustomer) {   
                if($this->customer->userCustomer->start_billing_date != $this->start_billing_date && 
                   $this->start_billing_date == Carbon::now()->format('Y-m-d')) 
                {
                    GenerateBillingJob::dispatch($this->customer->userCustomer);
                }
                if($this->end_billing_date == Carbon::now()->format('Y-m-d'))
                {
                    GenerateIsolirJob::dispatch($this->customer->userCustomer);
                }
                
                $this->customer->userCustomer->update([
                    'name' => $this->name,
                    'email' => $this->email ?: null,
                    'phone_number' => $this->phone_number,
                    'start_billing_date' => $this->start_billing_date,
                    'end_billing_date' => $this->end_billing_date,
                ]);
            }

            DB::commit();
            $this->dispatchBrowserEvent('hideEditPribadiModal');
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Data pribadi berhasil diperbarui']);
            $this->mount($this->customer->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchBrowserEvent('showErrorAlert', ['message' => 'Gagal memperbarui data pribadi: ' . $e->getMessage()]);
        }
    }

    public function openEditInstalasiModal()
    {
        $this->local_address = $this->customer->local_address ?? '';
        $this->username = $this->customer->username ?? '';
        $this->pass_hash = $this->customer->pass_hash ?? '';
        $this->device_serial_number = $this->customer->installation->device_serial_number ?? '';
        
        $this->dispatchBrowserEvent('showEditInstalasiModal', [
            'local_address' => $this->customer->local_address ?? '',
            'username' => $this->customer->username ?? '',
            'pass_hash' => $this->customer->pass_hash ?? '',
            'device_serial_number' => $this->customer->installation->device_serial_number ?? '',
        ]);
    }

    public function saveInstalasi()
    {
        $this->validate([
            'local_address' => 'nullable|ip',
            'username' => 'required|string|max:255',
            'pass_hash' => 'required|string|max:255',
            'device_serial_number' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $this->customer->update([
                'status' => ParamSchema::REACTIVATED,
                'local_address' => $this->local_address,
                'username' => $this->username,
                'pass_hash' => $this->pass_hash,
            ]);

            if ($this->customer->installation) {
                $this->customer->installation->update([
                    'device_serial_number' => $this->device_serial_number,
                ]);
            }

            dispatch(new ProvisionCustomerJob($this->customer->id));
            \App\Jobs\SyncInstalledCustomersJob::dispatch([$this->customer->id]);
            
            DB::commit();
            $this->dispatchBrowserEvent('hideEditInstalasiModal');
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Data instalasi berhasil diperbarui']);
            $this->mount($this->customer->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatchBrowserEvent('showErrorAlert', ['message' => 'Gagal memperbarui data instalasi: ' . $e->getMessage()]);
        }
    }

    public function viewPaymentProof($purchaseId)
    {
        $purchase = InternetCustomerPurchase::find($purchaseId);
        $this->paymentProofUrl = $purchase->payment_proof ? s3_asset(true,10,$purchase->payment_proof) : null;

        $this->dispatchBrowserEvent('showImageModal', [
            'title' => 'Bukti Pembayaran ' . Carbon::parse($purchase->period)->format('F Y'),
            'imageUrl' => $this->paymentProofUrl
        ]);
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
    
            $post = ['is_paid' => true];
    
            if(!$internetPurchase->customer->installation) {
                $post['status'] = ParamSchema::PROCESS_INSTALLATION;
                
                $userTechnical = optional($internetPurchase->customer->subdistrict?->coverageService?->coverageServiceOds)
                    ->pluck('ods.user_assign_id')
                    ->unique()
                    ->all();
        
                if(count($userTechnical) > 0) {
                    $message = "Pembayaran Langganan Internet Untuk Kode ".$internetPurchase->customer->code." Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
                    $directUrl = route('internet-customer.show',$internetPurchase->customer->id);
                    foreach($userTechnical as $tech) {
                        $this->sentInbox($tech,$message, $directUrl);
                    }
                }
            } else {
                $post['status'] = ParamSchema::REACTIVATED;
                dispatch(new ProvisionCustomerJob($internetCustomers->id));
                \App\Jobs\SyncInstalledCustomersJob::dispatch([$internetCustomers->id]);
            }
            
            GenerateInternetPurchaseCouponJob::dispatch($internetPurchase->customer->id, $internetPurchase->id, $internetPurchase->payment_months);
            $internetPurchase->customer->update($post);
            
            DB::commit();
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Pembayaran berhasil dikonfirmasi']);
        } catch (\Throwable $th) {
            Log::error($th);
            DB::rollBack();
            $this->dispatchBrowserEvent('showErrorAlert', ['message' => 'Gagal mengkonfirmasi pembayaran: ' . $th->getMessage()]);
        }
    }

    public function viewKtpPhoto()
    {
        $url = s3_asset(true,10,$this->ktpPhotoUrl);
        $this->dispatchBrowserEvent('showImageModal', [
            'title' => 'Foto KTP',
            'imageUrl' => $url
        ]);
    }

    public function viewInstallationPhotos()
    {
        if (empty($this->installationPhotos)) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Foto instalasi tidak tersedia'
            ]);
            return;
        }

        $fullUrls = array_map(function($path) {
            return s3_asset(true, 10, $path);
        }, $this->installationPhotos);

        $fullUrls = array_filter($fullUrls, function($url) {
            return !empty($url);
        });

        if (empty($fullUrls)) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Tidak dapat memuat foto instalasi'
            ]);
            return;
        }

        $this->dispatchBrowserEvent('showGalleryModal', [
            'title' => 'Foto Instalasi - ' . $this->customer->code,
            'images' => array_values($fullUrls)
        ]);
    }

    public function render()
    {
        $purchases = $this->customer->purchases()->orderby('created_at','desc')->paginate(5);
        $financeAccess = Access::can('as_finance', 'internet_customers');
        
        return view('livewire.internet-customer.admin.internet-customer-show', compact('purchases','financeAccess'))
            ->extends('adminlte::page');
    }

    // Tambahkan method baru setelah method sentInbox()

    public function openEditPackageModal()
    {
        // Load available packages
        $this->availablePackages = InternetPackage::where('company_id', $this->customer->company_id)
            ->where('is_active', true)
            ->where('id', '!=', $this->customer->internet_package_id)
            ->get();
        
        // Reset form
        $this->new_package_id = null;
        
        // Open modal
        $this->dispatchBrowserEvent('show-edit-package-modal');
    }

    public function savePackageChange()
    {
        // ✅ SIMPLIFIED: Tanpa reason field
        $this->validate([
            'new_package_id' => 'required|exists:internet_packages,id',
        ], [
            'new_package_id.required' => 'Paket internet baru harus dipilih',
            'new_package_id.exists' => 'Paket internet tidak valid',
        ]);

        DB::beginTransaction();
        try {
            $oldPackage = $this->customer->internetPackage;
            $newPackage = InternetPackage::find($this->new_package_id);
            
            // Update customer package
            $this->customer->update([
                'status' => ParamSchema::REACTIVATED,
                'internet_package_id' => $this->new_package_id,
            ]);
            
            // Log the change
            Log::info('Package changed', [
                'customer_id' => $this->customer->id,
                'customer_code' => $this->customer->code,
                'old_package' => $oldPackage->name,
                'new_package' => $newPackage->name,
                'changed_by' => Auth::id(),
            ]);
            
            // Dispatch provisioning job untuk update di router
            if ($this->customer->router_id && ($this->customer->status === ParamSchema::ACTIVE || $this->customer->status === ParamSchema::REACTIVATED)) 
            {
                dispatch(new ProvisionCustomerJob($this->customer->id));
                \App\Jobs\SyncInstalledCustomersJob::dispatch([$this->customer->id]);
            }
            
            DB::commit();
            
            $this->dispatchBrowserEvent('hide-edit-package-modal');
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'success',
                'message' => "Paket berhasil diubah dari {$oldPackage->name} ke {$newPackage->name}"
            ]);
            
            // Refresh page after delay
            $this->dispatchBrowserEvent('refresh-after-delay', ['delay' => 2000]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to change package', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customer->id
            ]);
            
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Gagal mengubah paket: ' . $e->getMessage()
            ]);
        }
    }

    private function sentInbox($to,$message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent($to, Auth::user()->id, $message, $directUrl);
        return true;
    }
}
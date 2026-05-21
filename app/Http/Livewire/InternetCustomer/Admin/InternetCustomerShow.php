<?php

namespace App\Http\Livewire\InternetCustomer\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Jobs\ProvisionCustomerJob;
use App\Jobs\GenerateInternetPurchaseCouponJob;
use App\Jobs\ProcessRouterMoveJob;
use App\Jobs\GenerateIsolirJob;
use App\Jobs\SendPaymentSuccessWaJob;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerGroup;
use App\Models\InternetCustomerPurchase;
use App\Models\Router;
use App\Models\AddressPool;
use App\Models\InternetPackage;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Subdistrict;

use App\Services\MikrotikService;
use App\Jobs\GenerateBillingJob;
use App\Schemas\ParamSchema;
use Carbon\Carbon;

use App\Helpers\Access;
use App\Helpers\InboxHelper;

class InternetCustomerShow extends Component
{
    use WithPagination, WithFileUploads;

    public $customer;
    public $agreementFields;
    public $paymentProofUrl;
    public $showPaymentProofModal = false;
    public $ktpPhotoUrl;
    public $showKtpModal = false;
    public $installationPhotos = [];
    public $showInstallationPhotosModal = false;

    // Properties untuk edit data pribadi
    public $name, $email, $phone_number, $start_billing_date, $end_billing_date, $grouping_id;
    public $province_id, $city_id, $district_id, $subdistrict_id, $address;
    public $ktp_number;
    public ?string $edit_group_id = null;
    public array $availableGroupsForEdit = [];
    public $npwp_number;

    // Inline KTP upload (table)
    public $ktp_photo_upload;
    public ?string $ktp_photo_pending_path = null;
    public bool $showKtpUpload = false;

    // Inline NPWP upload (table)
    public $npwp_photo_upload;
    public ?string $npwp_photo_pending_path = null;
    public ?string $npwp_photo_url = null;
    public bool $showNpwpUpload = false;
    
    // Properties untuk edit data instalasi
    public $local_address, $username, $pass_hash, $device_serial_number;
    
    // ✅ Properties untuk move router
    public $new_router_id = null;
    public $new_username = null;
    public $new_local_address = null;
    public $new_pool_id = null;
    public $availableRouters = [];
    public $availablePoolsForNewRouter = [];
    public $status_active;
    
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

    // Manual payment properties
    public $admin_payment_proof;
    public $admin_purchase_id;
    public $admin_payment_months = 1;
    public $admin_transfer_date;
    public $admin_transfer_from_bank;
    public $admin_transfer_from_account_name;
    public $admin_transfer_notes;

    public function mount($customerId)
    {
        $this->customer = InternetCustomer::with([
            'company',
            'province',
            'city',
            'district',
            'subdistrict',
            'internetPackage.regions',  // eager-load regions untuk harga per wilayah
            'partnershipAgreement',
            'userCustomer',
            'purchases',
            'installation',
            'router',
        ])
        ->byCompany(Auth::user()->company_id)
        ->findOrFail($customerId);

        if ($this->customer->partnershipAgreement) {
            $this->agreementFields = json_decode($this->customer->partnershipAgreement->fields, true);
        }

        $this->ktpPhotoUrl    = $this->customer->ktp_photo;
        $this->npwp_photo_url = $this->customer->npwp_photo;

        if ($this->customer->installation && $this->customer->installation->medias->count() > 0) {
            $this->installationPhotos = $this->customer->installation->medias
                ->pluck('photo')
                ->toArray();
        }

        $this->status_active = $this->customer->status != ParamSchema::INACTIVE ? true : false;
        $this->availablePackages = [];
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
            
            $this->dispatchBrowserEvent('downloadFile', [
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
    // NPWP VIEW / DOWNLOAD METHODS
    // ========================================

    public function viewNpwpPhoto()
    {
        if (!$this->customer->npwp_photo) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Foto NPWP tidak tersedia'
            ]);
            return;
        }

        $url = s3_asset(true, 10, $this->customer->npwp_photo);

        $this->dispatchBrowserEvent('showImageModal', [
            'title'    => 'Foto NPWP — ' . $this->customer->name,
            'imageUrl' => $url,
        ]);
    }

    public function downloadNpwpPhoto()
    {
        if (!$this->customer->npwp_photo) {
            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Foto NPWP tidak tersedia'
            ]);
            return;
        }

        try {
            $url      = s3_asset(true, 10, $this->customer->npwp_photo);
            $filename = 'NPWP_' . $this->customer->code . '_' . str_replace(' ', '_', $this->customer->name) . '.jpg';

            $this->dispatchBrowserEvent('downloadFile', [
                'url'      => $url,
                'filename' => $filename,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to download NPWP', [
                'error'       => $e->getMessage(),
                'customer_id' => $this->customer->id,
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type'    => 'error',
                'message' => 'Gagal mendownload foto NPWP',
            ]);
        }
    }

    // ========================================
    // INLINE KTP UPLOAD (table)
    // ========================================

    public function toggleKtpUpload()
    {
        $this->showKtpUpload = !$this->showKtpUpload;
        if (!$this->showKtpUpload) {
            $this->ktp_photo_upload       = null;
            $this->ktp_photo_pending_path = null;
            $this->resetErrorBag('ktp_photo_upload');
        }
    }

    public function updatedKtpPhotoUpload()
    {
        $this->validateOnly('ktp_photo_upload', [
            'ktp_photo_upload' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if (!$this->ktp_photo_upload) return;

        try {
            $this->ktp_photo_pending_path = $this->ktp_photo_upload->store('ktps', 's3');
            $this->ktp_photo_upload       = null;
        } catch (\Exception $e) {
            Log::error('KTP photo upload failed', ['error' => $e->getMessage(), 'customer_id' => $this->customer->id]);
            $this->ktp_photo_pending_path = null;
            $this->addError('ktp_photo_upload', 'Gagal mengunggah foto KTP, silakan coba lagi.');
        }
    }

    public function saveKtpPhoto()
    {
        if (!$this->ktp_photo_pending_path) {
            $this->addError('ktp_photo_upload', 'Pilih file terlebih dahulu.');
            return;
        }

        $this->customer->update(['ktp_photo' => $this->ktp_photo_pending_path]);
        $this->ktpPhotoUrl            = $this->ktp_photo_pending_path;
        $this->ktp_photo_pending_path = null;
        $this->showKtpUpload          = false;

        $this->dispatchBrowserEvent('show-notification', ['type' => 'success', 'message' => 'Foto KTP berhasil diperbarui.']);
    }

    // ========================================
    // INLINE NPWP UPLOAD (table)
    // ========================================

    public function toggleNpwpUpload()
    {
        $this->showNpwpUpload = !$this->showNpwpUpload;
        if (!$this->showNpwpUpload) {
            $this->npwp_photo_upload       = null;
            $this->npwp_photo_pending_path = null;
            $this->resetErrorBag('npwp_photo_upload');
        }
    }

    public function saveNpwpPhoto()
    {
        if (!$this->npwp_photo_pending_path) {
            $this->addError('npwp_photo_upload', 'Pilih file terlebih dahulu.');
            return;
        }

        $this->customer->update(['npwp_photo' => $this->npwp_photo_pending_path]);
        $this->npwp_photo_url          = $this->npwp_photo_pending_path;
        $this->npwp_photo_pending_path = null;
        $this->showNpwpUpload          = false;

        $this->dispatchBrowserEvent('show-notification', ['type' => 'success', 'message' => 'Foto NPWP berhasil diperbarui.']);
    }

    // ========================================
    // NPWP FILE UPLOAD — eager upload on select
    // ========================================

    public function updatedNpwpPhotoUpload()
    {
        $this->validateOnly('npwp_photo_upload', [
            'npwp_photo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        if (!$this->npwp_photo_upload) {
            return;
        }

        try {
            $this->npwp_photo_pending_path = $this->npwp_photo_upload->store('npwps', 's3');
            $this->npwp_photo_upload       = null; // release tmp reference
        } catch (\Exception $e) {
            Log::error('NPWP photo upload failed in edit modal', [
                'error'       => $e->getMessage(),
                'customer_id' => $this->customer->id,
            ]);
            $this->npwp_photo_pending_path = null;
            $this->addError('npwp_photo_upload', 'Gagal mengunggah foto NPWP, silakan coba lagi.');
        }
    }

    // ========================================
    // EXISTING METHODS (unchanged)
    // ========================================

    public function openEditPribadiModal()
    {
        $this->name           = $this->customer->name;
        $this->email          = $this->customer->userCustomer->email ?? '';
        $this->phone_number   = $this->customer->userCustomer->phone_number ?? '';
        $this->ktp_number     = $this->customer->ktp_number;
        $this->npwp_number    = $this->customer->npwp_number;
        $this->start_billing_date = $this->customer->status != ParamSchema::INACTIVE ? $this->customer->userCustomer->start_billing_date : Carbon::now()->format('Y-m-d');
        $this->end_billing_date   = $this->customer->status != ParamSchema::INACTIVE ? $this->customer->userCustomer->end_billing_date : Carbon::now()->addDays(5)->format('Y-m-d');
        $this->grouping_id    = $this->customer->grouping_id;
        $this->edit_group_id  = $this->customer->group_id;
        $this->province_id    = $this->customer->province_id;
        $this->city_id = $this->customer->city_id;
        $this->district_id = $this->customer->district_id;
        $this->subdistrict_id = $this->customer->subdistrict_id;
        $this->address = $this->customer->address;

        // Fetch option data for cascading selects
        $cities = $this->province_id
            ? City::where('province_id', $this->province_id)->whereHas('cityCoverages')->orderBy('name')->get(['id','name'])
            : collect();
        $districts = $this->city_id
            ? District::where('city_id', $this->city_id)->whereHas('districtCoverages')->orderBy('name')->get(['id','name'])
            : collect();
        $subdistricts = $this->district_id
            ? Subdistrict::where('district_id', $this->district_id)->whereHas('subdistrictCoverages')->orderBy('name')->get(['id','name'])
            : collect();

        // Load groups only when customer has no group yet
        $groups = [];
        if (!$this->customer->group_id) {
            $groups = InternetCustomerGroup::byCompany(Auth::user()->company_id)
                ->orderBy('name')
                ->get(['id', 'name', 'description'])
                ->map(fn($g) => [
                    'id'          => $g->id,
                    'name'        => $g->name,
                    'description' => $g->description,
                ])
                ->toArray();
            $this->availableGroupsForEdit = $groups;
        }

        $this->dispatchBrowserEvent('showEditPribadiModal', [
            'status_active'      => $this->status_active,
            'name'               => $this->customer->name,
            'email'              => $this->customer->userCustomer->email ?? '',
            'phone_number'       => $this->customer->userCustomer->phone_number ?? '',
            'start_billing_date' => $this->start_billing_date,
            'end_billing_date'   => $this->end_billing_date,
            'grouping_id'        => $this->grouping_id,
            'province_id'        => $this->province_id,
            'city_id'            => $this->city_id,
            'district_id'        => $this->district_id,
            'subdistrict_id'     => $this->subdistrict_id,
            'address'            => $this->address,
            'cities'             => $cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray(),
            'districts'          => $districts->map(fn($d) => ['id' => $d->id, 'name' => $d->name])->toArray(),
            'subdistricts'       => $subdistricts->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray(),
            'has_group'          => (bool) $this->customer->group_id,
            'groups_for_edit'    => $groups,
            'edit_group_id'      => $this->edit_group_id,
            'ktp_number'         => $this->ktp_number,
            'npwp_number'        => $this->npwp_number,
        ]);
    }

    /**
     * Set semua location sekaligus tanpa trigger cascade reset
     */
    public function initLocationFields($province_id, $city_id, $district_id, $subdistrict_id)
    {
        $this->province_id    = $province_id;
        $this->city_id        = $city_id;
        $this->district_id    = $district_id;
        $this->subdistrict_id = $subdistrict_id;
    }

    public function updatedProvinceId($value)
    {
        $this->city_id = null;
        $this->district_id = null;
        $this->subdistrict_id = null;

        $cities = $value
            ? City::where('province_id', $value)->whereHas('cityCoverages')->orderBy('name')->get(['id','name'])
            : collect();

        $this->dispatchBrowserEvent('addressCascadeUpdate', [
            'level'        => 'province',
            'cities'       => $cities->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray(),
            'districts'    => [],
            'subdistricts' => [],
        ]);
    }

    public function updatedCityId($value)
    {
        $this->district_id = null;
        $this->subdistrict_id = null;

        $districts = $value
            ? District::where('city_id', $value)->whereHas('districtCoverages')->orderBy('name')->get(['id','name'])
            : collect();

        $this->dispatchBrowserEvent('addressCascadeUpdate', [
            'level'        => 'city',
            'districts'    => $districts->map(fn($d) => ['id' => $d->id, 'name' => $d->name])->toArray(),
            'subdistricts' => [],
        ]);
    }

    public function updatedDistrictId($value)
    {
        $this->subdistrict_id = null;

        $subdistricts = $value
            ? Subdistrict::where('district_id', $value)->whereHas('subdistrictCoverages')->orderBy('name')->get(['id','name'])
            : collect();

        $this->dispatchBrowserEvent('addressCascadeUpdate', [
            'level'        => 'district',
            'subdistricts' => $subdistricts->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray(),
        ]);
    }

    /**
     * Auto-update end_billing_date when start_billing_date changes
     * end_billing_date = start_billing_date + 5 days
     */
    public function checkGroupingIdAvailabilityShow(?string $value): void
    {
        $value = trim($value ?? '');

        if (strlen($value) < 2) {
            $this->dispatchBrowserEvent('groupingIdCheckComplete', ['available' => true]);
            return;
        }

        $existing = InternetCustomer::where('grouping_id', $value)
            ->where('id', '!=', $this->customer->id)
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

    public function updatedStartBillingDate($value)
    {
        if ($value) {
            $this->end_billing_date = Carbon::parse($value)->addDays(5)->format('Y-m-d');
        }
    }

    public function savePribadi()
    {
        $rules = [
            'name'               => 'required|string|max:255',
            'email'              => 'nullable|email',
            'phone_number'       => ['nullable', 'string', 'regex:/^[0-9]+$/'],
            'start_billing_date' => 'nullable|date',
            'end_billing_date'   => 'nullable|date|after:start_billing_date',
        ];

        // Validate grouping_id if customer has a group assigned
        if ($this->customer->group_id) {
            $group  = $this->customer->group;
            $prefix = $group ? $group->grouping_prefix : '';
            $selfId = $this->customer->id;

            $rules['grouping_id'] = [
                'nullable',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($prefix) {
                    if ($value && !str_starts_with($value, $prefix)) {
                        $fail("Grouping ID harus diawali dengan kode group: {$prefix}");
                    }
                },
                "unique:internet_customers,grouping_id,{$selfId},id",
            ];
        }

        $rules['ktp_number'] = ['nullable', 'string', 'max:20'];
        if ($this->customer->customer_type === 'bisnis') {
            $rules['npwp_number'] = ['nullable', 'string', 'max:30'];
        }

        $this->validate($rules, [
            'phone_number.regex' => 'Nomor telepon hanya boleh berisi angka, tanpa simbol.',
        ]);

        DB::beginTransaction();
        try {
            $customerUpdate = [
                'name'           => $this->name,
                'ktp_number'     => $this->ktp_number ?: null,
                'address'        => $this->address,
                'province_id'    => $this->province_id,
                'city_id'        => $this->city_id,
                'district_id'    => $this->district_id,
                'subdistrict_id' => $this->subdistrict_id,
            ];

            if ($this->customer->customer_type === 'bisnis') {
                $customerUpdate['npwp_number'] = $this->npwp_number ?: null;
            }
            
            $this->customer->update($customerUpdate);
            
            if(!$this->status_active)
            {
                if($this->customer->router)
                {
                    $mikrotik = new MikrotikService($this->customer->router->id);
                    $mikrotik->removeUser($this->customer->id);
                }


                $this->customer->update(['status' => ParamSchema::INACTIVE]);
                $this->customer->installation?->delete();
                


            }
            if($this->customer->status == ParamSchema::INACTIVE && $this->status_active)
            {
                GenerateBillingJob::dispatch($this->customer->userCustomer);
            }

            if ($this->customer->userCustomer && $this->status_active) {   
                if($this->customer->userCustomer->start_billing_date != $this->start_billing_date && 
                $this->start_billing_date == Carbon::now()->format('Y-m-d')) 
                {
                    GenerateBillingJob::dispatch($this->customer->userCustomer);
                }
                if($this->end_billing_date == Carbon::now()->format('Y-m-d'))
                {
                    GenerateIsolirJob::dispatch($this->customer->userCustomer);
                }

                if ($this->grouping_id !== $this->customer->grouping_id) {
                    $this->customer->update(['grouping_id' => $this->grouping_id ?: null]);
                }

                // Assign group if customer didn't have one yet → queue generates grouping_id
                if (!$this->customer->group_id && $this->edit_group_id) {
                    $this->customer->update(['group_id' => $this->edit_group_id]);
                    \App\Jobs\GenerateGroupingIdJob::dispatch($this->customer->id);
                }

                $this->customer->userCustomer->update([
                    'name'               => $this->name,
                    'email'              => $this->email ?: null,
                    'phone_number'       => $this->phone_number,
                    'start_billing_date' => $this->start_billing_date,
                    'end_billing_date'   => $this->end_billing_date,
                ]);

                if($this->customer->partnershipAgreement)
                {
                    $fields = json_decode($this->customer->partnershipAgreement->fields);
                    $fields->nama = $this->name;
                    $fields->email = $this->email;
                    $fields->telephon = $this->phone_number;
                    
                    $this->customer->partnershipAgreement->update([
                        'fields' => json_encode($fields),
                    ]);
                }
            }

            DB::commit();
            $this->dispatchBrowserEvent('hideEditPribadiModal');
            $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Data pribadi berhasil diperbarui']);
            $this->mount($this->customer->id);
        } catch (\Exception $e) {
            // dd($e);
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
            
            DB::commit();
            
            dispatch(new ProvisionCustomerJob($this->customer->id));
            \App\Jobs\SyncInstalledCustomersJob::dispatch([$this->customer->id]);
            
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
            'imageUrl' => $this->paymentProofUrl,
            'transferDetails' => [
                'date' => $purchase->transfer_date ? \Carbon\Carbon::parse($purchase->transfer_date)->format('d M Y') : null,
                'bank' => $purchase->transfer_from_bank,
                'account_name' => $purchase->transfer_from_account_name,
                'notes' => $purchase->transfer_notes
            ]
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

            SendPaymentSuccessWaJob::dispatch($internetPurchase->id);

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
        $provinces = Province::whereHas('provinceCoverages')->orderBy('name')->get();
        $cities = $this->province_id ? City::where('province_id', $this->province_id)->whereHas('cityCoverages')->orderBy('name')->get() : collect();
        $districts = $this->city_id ? District::where('city_id', $this->city_id)->whereHas('districtCoverages')->orderBy('name')->get() : collect();
        $subdistricts = $this->district_id ? Subdistrict::where('district_id', $this->district_id)->whereHas('subdistrictCoverages')->orderBy('name')->get() : collect();

        return view('livewire.internet-customer.admin.internet-customer-show', compact('purchases', 'financeAccess', 'provinces', 'cities', 'districts', 'subdistricts'))
            ->extends('adminlte::page');
    }

    // Tambahkan method baru setelah method sentInbox()

    public function openEditPackageModal()
    {
        // Load available packages — filter berdasarkan wilayah customer
        // Sehingga admin hanya bisa pilih paket yang berlaku di wilayah customer
        $packages = InternetPackage::where('company_id', $this->customer->company_id)
            ->where('is_active', true)
            ->where('customer_type', $this->customer->customer_type)
            ->where('id', '!=', $this->customer->internet_package_id)
            ->with('regions')
            ->forRegion(
                $this->customer->province_id,
                $this->customer->city_id,
                $this->customer->district_id
            )
            ->get();

        $formattedPackages = [];
        foreach ($packages as $pkg) {
            $priceData = $pkg->getPriceForRegion(
                $this->customer->province_id,
                $this->customer->city_id,
                $this->customer->district_id
            );
            
            $label = $pkg->name . ' - Rp ' . number_format($priceData['price_nett'], 0, ',', '.') . '/bulan';
            if ($priceData['region_type'] !== 'global') {
                $label .= ' (Wilayah)';
            }
            
            $formattedPackages[] = [
                'id' => $pkg->id,
                'label' => $label
            ];
        }
        
        $this->availablePackages = $formattedPackages;

        // Reset form
        $this->new_package_id = null;

        // Open modal
        $this->dispatchBrowserEvent('show-edit-package-modal', [
            'packages' => $formattedPackages
        ]);
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

    /**
     * Mark payment as expired
     */
    public function expirePayment($purchaseId)
    {
        try {
            $purchase = InternetCustomerPurchase::findOrFail($purchaseId);
            
            // Validate payment is not confirmed
            if ($purchase->isConfirmed()) {
                $this->dispatchBrowserEvent('show-notification', [
                    'type' => 'error',
                    'message' => 'Pembayaran yang sudah lunas tidak dapat di-expire'
                ]);
                return;
            }

            // Validate payment is not already expired
            if ($purchase->payment_method == ParamSchema::EXPIRED) {
                $this->dispatchBrowserEvent('show-notification', [
                    'type' => 'warning',
                    'message' => 'Pembayaran sudah di-mark sebagai expired'
                ]);
                return;
            }

            // Mark as expired
            $purchase->markAsExpired();

            Log::info('Payment marked as expired', [
                'purchase_id' => $purchaseId,
                'customer_id' => $purchase->internet_customer_id,
                'expired_by' => Auth::id(),
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'success',
                'message' => 'Pembayaran berhasil ditandai sebagai expired'
            ]);

            // Refresh the page
            $this->mount($this->customer->id);

        } catch (\Exception $e) {
            Log::error('Failed to expire payment', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchaseId
            ]);

            $this->dispatchBrowserEvent('show-notification', [
                'type' => 'error',
                'message' => 'Gagal menandai pembayaran sebagai expired: ' . $e->getMessage()
            ]);
        }
    }

    public function showManualPaymentModal($purchaseId)
    {
        $this->admin_purchase_id    = $purchaseId;
        $this->admin_payment_months = 1;
        $this->admin_payment_proof  = null;
        $this->dispatchBrowserEvent('show-admin-manual-payment-modal');
    }

    /**
     * Semua field (kecuali file) dikirim sebagai parameter langsung dari JS
     * agar tidak ada race-condition dengan @this.set().
     * File (admin_payment_proof) sudah diset via @this.upload() sebelum method ini dipanggil.
     */
    public function submitManualPayment(
        int     $months,
        string  $transferDate,
        ?string $bank        = null,
        ?string $accountName = null,
        ?string $notes       = null
    ) {
        $months = max(1, min(24, $months));

        // Guard: file harus sudah terupload
        if (!$this->admin_payment_proof) {
            $this->dispatchBrowserEvent('admin-payment-error', [
                'message' => 'Bukti pembayaran belum diupload. Silakan coba lagi.',
            ]);
            return;
        }

        // Validasi tanggal (field lain divalidasi client-side)
        if (!$transferDate || !strtotime($transferDate)) {
            $this->dispatchBrowserEvent('admin-payment-error', [
                'message' => 'Tanggal transfer tidak valid.',
            ]);
            return;
        }
        if (strtotime($transferDate) > strtotime(today()->toDateString())) {
            $this->dispatchBrowserEvent('admin-payment-error', [
                'message' => 'Tanggal transfer tidak boleh lebih dari hari ini.',
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            $purchase         = InternetCustomerPurchase::findOrFail($this->admin_purchase_id);
            $internetCustomer = $purchase->customer;
            $userCustomer     = $internetCustomer->userCustomer;

            // ── Hitung periode ───────────────────────────────────────────────
            $periodStart = $userCustomer->start_billing_date
                ? Carbon::parse($userCustomer->start_billing_date)
                : now()->startOfDay();
            $periodEnd = $periodStart->copy()->addMonths($months)->subDay();

            // ── Simpan file bukti ────────────────────────────────────────────
            $path = $this->admin_payment_proof->store('payment_proofs');

            $price    = $internetCustomer->internetPackage->price_nett ?? 0;
            $subtotal = $price * $months;

            // ── Update purchase + auto-konfirmasi sekaligus ──────────────────
            $purchase->update([
                'payment_proof'              => $path,
                'payment_method'             => 'transfer',
                'payment_months'             => $months,
                'period_start'               => $periodStart,
                'period_end'                 => $periodEnd,
                'amount_paid'                => $subtotal,
                'transfer_date'              => $transferDate,
                'transfer_from_bank'         => $bank,
                'transfer_from_account_name' => $accountName,
                'transfer_notes'             => $notes,
                // Auto-konfirmasi oleh admin finance
                'confirmation_finance_at'    => now(),
                'user_finance_id'            => Auth::id(),
            ]);

            // ── Smart billing date (sama persis dengan confirmPayment) ────────
            $maxBillingDay    = config('services.internet_custom.max_billing_date', 20);
            $startBillingDate = $periodStart->day > $maxBillingDay
                ? $periodStart->copy()->addMonths($months)->firstOfMonth()
                : $periodStart->copy()->addMonths($months);

            $gracePeriod    = config('services.internet_custom.end_billing_of_days', 5);
            $endBillingDate = $startBillingDate->copy()->addDays($gracePeriod);

            $userCustomer->update([
                'start_billing_date' => $startBillingDate->format('Y-m-d'),
                'end_billing_date'   => $endBillingDate->format('Y-m-d'),
            ]);

            // ── Update status pelanggan ──────────────────────────────────────
            $post = ['is_paid' => true];

            if (!$internetCustomer->installation) {
                $post['status'] = ParamSchema::PROCESS_INSTALLATION;

                $userTechnical = optional($internetCustomer->subdistrict?->coverageService?->coverageServiceOds)
                    ->pluck('ods.user_assign_id')
                    ->unique()
                    ->all();

                if (!empty($userTechnical)) {
                    $msg = "Pembayaran pelanggan {$internetCustomer->code} telah dikonfirmasi. Silakan segera lakukan pemasangan.";
                    $url = route('internet-customer.show', $internetCustomer->id);
                    foreach ($userTechnical as $tech) {
                        $this->sentInbox($tech, $msg, $url);
                    }
                }
            } else {
                $post['status'] = ParamSchema::REACTIVATED;
                dispatch(new ProvisionCustomerJob($userCustomer->id));
                \App\Jobs\SyncInstalledCustomersJob::dispatch([$userCustomer->id]);
            }

            GenerateInternetPurchaseCouponJob::dispatch($internetCustomer->id, $purchase->id, $months);
            $internetCustomer->update($post);

            DB::commit();

            SendPaymentSuccessWaJob::dispatch($purchase->id);

            Log::info('Admin confirmed manual payment', [
                'purchase_id'  => $purchase->id,
                'customer_id'  => $internetCustomer->id,
                'confirmed_by' => Auth::id(),
                'months'       => $months,
                'status'       => $post['status'],
            ]);

            $this->reset(['admin_payment_proof']);
            $this->dispatchBrowserEvent('hide-admin-manual-payment-modal');
            $this->dispatchBrowserEvent('showSuccessAlert', [
                'message' => 'Pembayaran berhasil dikonfirmasi. Status pelanggan diperbarui.',
            ]);
            $this->mount($this->customer->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin manual payment failed', [
                'error'       => $e->getMessage(),
                'purchase_id' => $this->admin_purchase_id,
                'confirmed_by'=> Auth::id(),
            ]);
            $this->dispatchBrowserEvent('admin-payment-error', [
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
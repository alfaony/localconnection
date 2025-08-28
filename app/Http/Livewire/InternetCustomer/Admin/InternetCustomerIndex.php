<?php

namespace App\Http\Livewire\InternetCustomer\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\User;
use App\Models\InternetCustomerInstallation;
use App\Models\InternetCustomerPurchase;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Storage;
use App\Helpers\InboxHelper;
use App\Helpers\Access;
use App\Schemas\ParamSchema;

use Carbon\Carbon;

class InternetCustomerIndex extends Component
{
    use WithPagination;
    use WithFileUploads;
    protected $paginationTheme = 'bootstrap';

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

    public $currentInstallationId; 
    public $currentInstallationName;
    public $currentInstallationCode;
    public $deviceSerialNumber, $photos = [], $installationNotes;
    public $installationCustomerName, $installationCustomerCode, $installationCustomerId;
    public $installationModal = false;
    public $currentInstallationCustomer;
    public $isSubmitting = false;

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
    public function openInstallationModal($customerId)
    {
        $customer = InternetCustomer::find($customerId);
        
        $this->currentInstallationId = $customer->id;
        $this->currentInstallationName = $customer->name;
        $this->currentInstallationCode = $customer->code;
        $this->deviceSerialNumber = $customer->device_serial_number ?? '';
        
        $this->dispatchBrowserEvent('open-installation-modal', [
            'customerName' => $customer->name,
            'customerCode' => $customer->code,
            'serialNumber' => $customer->device_serial_number ?? ''
        ]);
        
        $this->installationModal = true;
    }

    // Method untuk validasi dan submit
    public function completeInstallation($serialNumber, $photos, $notes)
    {
        // Validasi tambahan
        $this->validate([
            'currentInstallationId' => 'required|exists:internet_customers,id',
            'photos' => 'required|array|min:1',
            'photos.*' => 'image|max:10240' // Max 2MB per file
        ]);

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

            // dd($uploadedPaths);

            // Update data
            $customer = InternetCustomer::find($this->currentInstallationId);
            $customer->update([
                'status' => ParamSchema::INSTALLED 
            ]);
            $customerInstallation = InternetCustomerInstallation::create([
                'internet_customer_id' => $customer->id,
                'photos' => json_encode($uploadedPaths),
                'device_serial_number' => $serialNumber,
                'notes' => $notes,
                'installed_at' => now(),
                'technical_user_id' => Auth::id(),
            ]);

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

            $date = Carbon::parse($internetCustomers->start_billing_date);
            
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

        $query = InternetCustomer::query()->with([
            'company',
            'province',
            'city',
            'district',
            'subdistrict',
            'internetPackage',
            'partnershipAgreement',
            'userCustomer'
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
                        $q->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%')->orWhere('phone_number', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('company', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('ktp_number', 'like', '%' . $this->search . '%')
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

        // Filter tanggal
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Sorting
        $internetCustomers = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Get filter options

        $packages = InternetPackage::byCompany($user->company_id)->orderBy('name')->get();

        return view('livewire.internet-customer.admin.internet-customer-index', [
            'finance_access' => Access::can('as_finance', 'internet_customers'),
            'technical_access' => Access::can('as_technician', 'internet_customers'),
            'internetCustomers' => $internetCustomers,
            'packages' => $packages
        ])->extends('adminlte::page');
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

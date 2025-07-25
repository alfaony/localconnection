<?php

namespace App\Http\Livewire\InternetCustomer\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\User;
use App\Models\InternetCustomerInstallation;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Helpers\InboxHelper;
use App\Helpers\Access;
use App\Schemas\ParamSchema;

class InternetCustomerIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

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
                $newPath = 'public/installation-photos/' . uniqid() . '-' . basename($tmpFilename);

                // Pindahkan dari tmp ke public/installation-photos
                Storage::put(
                    $newPath,
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

            return true;

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
        $customer = InternetCustomer::findOrFail($customerId);
        $customer->update([
            'is_paid' => true,
            'status' => ParamSchema::PROCESS_INSTALLATION,
        ]);

        $customer->purchase->update([
            'confirmation_finance_at' => now(),
            'user_finance_id' => Auth::user()->id
        ]);
        
        $userTechnical = optional($customer->subdistrict?->coverageService?->coverageServiceOds)
        ->pluck('ods.user_assign_id')
        ->unique()
        ->all();

        if(count($userTechnical) > 0)
        {
            $message = "Pembayaran Langganan Internet Untuk Kode ".$customer->code." Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan";
            $directUrl = route('internet-customer.index');
            foreach($userTechnical as $tech)
            {
                $this->sentInbox($tech,$message, $directUrl);
            }
        }

        $this->dispatchBrowserEvent('showSuccessAlert', ['message' => 'Pembayaran Langganan Internet Untuk Kode '.$customer->code.' Telah di Setujui Oleh Finance Silahkan segera lakukan Pemasangan']);
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
                    ->orWhere('address', 'like', '%' . $this->search . '%')
                    ->orWhere('ktp_number', 'like', '%' . $this->search . '%')
                    ->orWhere('device_serial_number', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
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

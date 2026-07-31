<?php

namespace App\Http\Livewire\StaticCustomer;

use App\Jobs\ProvisionCustomerJob;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Router;
use App\Schemas\ParamSchema;
use App\Services\StaticReconcileService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StaticReconcileIndex extends Component
{
    public ?int $routerId = null;
    public array $entries = [];
    public bool $scanning = false;
    public ?string $scanError = null;

    // State modal "Hubungkan ke Customer"
    public ?string $linkingIp = null;
    public ?string $linkingMac = null;
    public ?string $selectedCustomerId = null;

    // State modal "Buat Customer Baru"
    public ?string $creatingIp = null;
    public ?string $creatingMac = null;
    public string $newCustomerName = '';
    public ?int $newPackageId = null;

    public function mount()
    {
        $firstRouter = Router::byCompany(Auth::user()->company_id)->first();
        $this->routerId = $firstRouter?->id;
    }

    public function updatedRouterId()
    {
        $this->entries = [];
    }

    public function scan(StaticReconcileService $service)
    {
        if (!$this->routerId) {
            $this->scanError = 'Pilih router dulu.';
            return;
        }

        $this->scanning = true;
        $this->scanError = null;

        try {
            $router = Router::byCompany(Auth::user()->company_id)->findOrFail($this->routerId);
            $rows = $service->discover($router);

            // Serialize ke array plain biar bisa disimpan di public property Livewire
            $this->entries = collect($rows)->map(function ($row) {
                return [
                    'ip' => $row['ip'],
                    'mac' => $row['mac'],
                    'interface' => $row['interface'],
                    'matched_customer_id' => $row['matched_customer']?->id,
                    'matched_customer_name' => $row['matched_customer']?->name,
                    'matched_customer_status' => $row['matched_customer']?->status,
                ];
            })->toArray();

        } catch (\Throwable $e) {
            $this->scanError = 'Gagal scan router: ' . $e->getMessage();
        } finally {
            $this->scanning = false;
        }
    }

    // ============== LINK KE CUSTOMER EXISTING ==============

    public function openLinkModal(string $ip, ?string $mac)
    {
        $this->linkingIp = $ip;
        $this->linkingMac = $mac;
        $this->selectedCustomerId = null;
        $this->dispatchBrowserEvent('open-link-modal');
    }

    public function getCandidateCustomersProperty()
    {
        // Customer static yang belum punya IP (menunggu setup teknis)
        return InternetCustomer::where('company_id', Auth::user()->company_id)
            ->where('access_type', 'ipoe')
            ->whereNull('ip_address')
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    public function confirmLink()
    {
        $this->validate([
            'selectedCustomerId' => 'required|exists:internet_customers,id',
        ]);

        $customer = InternetCustomer::findOrFail($this->selectedCustomerId);
        $customer->update([
            'router_id' => $this->routerId,
            'ip_address' => $this->linkingIp,
            'mac_address' => $this->linkingMac,
            'access_type' => 'ipoe',
        ]);

        if (in_array($customer->status, [ParamSchema::INSTALLED, ParamSchema::REACTIVATED])) {
            dispatch(new ProvisionCustomerJob($customer->id))->afterResponse();
        }

        $this->linkingIp = null;
        $this->linkingMac = null;
        $this->selectedCustomerId = null;

        session()->flash('message', "Berhasil menghubungkan {$customer->name} ke IP tersebut.");
        $this->dispatchBrowserEvent('close-link-modal');
    }

    // ============== BUAT CUSTOMER BARU ==============

    public function openCreateModal(string $ip, ?string $mac)
    {
        $this->creatingIp = $ip;
        $this->creatingMac = $mac;
        $this->newCustomerName = "Static - {$ip}";
        $this->newPackageId = null;
        $this->dispatchBrowserEvent('open-create-modal');
    }

    public function getIpoePackagesProperty()
    {
        return InternetPackage::where('company_id', Auth::user()->company_id)
            ->where('access_type', 'ipoe')
            ->where('is_active', true)
            ->get(['id', 'name', 'price']);
    }

    public function confirmCreate()
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newPackageId' => 'required|exists:internet_packages,id',
        ]);

        $customer = InternetCustomer::create([
            'company_id' => Auth::user()->company_id,
            'internet_package_id' => $this->newPackageId,
            'name' => $this->newCustomerName,
            'status' => ParamSchema::PENDING,
            'access_type' => 'ipoe',
            'router_id' => $this->routerId,
            'ip_address' => $this->creatingIp,
            'mac_address' => $this->creatingMac,
        ]);

        $this->creatingIp = null;
        $this->creatingMac = null;

        session()->flash('message', "Customer baru dibuat dengan status PENDING. Lengkapi data (alamat, KTP, dll) sebelum diaktifkan.");

        return redirect()->route('internet-customer.edit', $customer->id);
    }

    public function render()
    {
        return view('livewire.static-customer.static-reconcile-index', [
            'routers' => Router::byCompany(Auth::user()->company_id)->get(),
        ])->extends('adminlte::page');
    }
}

<?php

namespace App\Http\Livewire\PppoeCustomer;

use App\Jobs\ProvisionCustomerJob;
use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Router;
use App\Schemas\ParamSchema;
use App\Services\PppoeReconcileService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PppoeReconcileIndex extends Component
{
    public ?int $routerId = null;
    public array $entries = [];
    public bool $scanning = false;
    public ?string $scanError = null;

    // State modal "Hubungkan ke Customer"
    public ?string $linkingUsername = null;
    public ?string $selectedCustomerId = null;

    // State modal "Buat Customer Baru"
    public ?string $creatingUsername = null;
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

    public function scan(PppoeReconcileService $service)
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

            $this->entries = collect($rows)->map(function ($row) {
                return [
                    'username' => $row['username'],
                    'profile' => $row['profile'],
                    'service' => $row['service'],
                    'disabled' => $row['disabled'],
                    'matched_customer_id' => $row['matched_customer']?->id,
                    'matched_customer_name' => $row['matched_customer']?->name,
                    'matched_customer_status' => $row['matched_customer']?->status,
                    'suggested_package_id' => $row['suggested_package']?->id,
                    'suggested_package_name' => $row['suggested_package']?->name,
                ];
            })->toArray();

        } catch (\Throwable $e) {
            $this->scanError = 'Gagal scan router: ' . $e->getMessage();
        } finally {
            $this->scanning = false;
        }
    }

    // ============== LINK KE CUSTOMER EXISTING ==============

    public function openLinkModal(string $username)
    {
        $this->linkingUsername = $username;
        $this->selectedCustomerId = null;
        $this->dispatchBrowserEvent('open-link-modal');
    }

    public function getCandidateCustomersProperty()
    {
        // Customer PPPoE yang belum punya username (menunggu setup teknis)
        return InternetCustomer::where('company_id', Auth::user()->company_id)
            ->where('access_type', 'pppoe')
            ->whereNull('username')
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
            'username' => $this->linkingUsername,
            'access_type' => 'pppoe',
        ]);

        // TIDAK dispatch ProvisionCustomerJob di sini — secret-nya udah ada
        // manual di router, kalau kita panggil provisioning ulang bisa
        // nimpa/reset password yang lagi dipakai customer aktif. Cukup
        // catat di sistem aja (link data), tanpa sentuh router lagi.

        $this->linkingUsername = null;
        $this->selectedCustomerId = null;

        session()->flash('message', "Berhasil menghubungkan {$customer->name} ke secret PPPoE tersebut. Password TIDAK diubah — password lama di router tetap berlaku.");
        $this->dispatchBrowserEvent('close-link-modal');
    }

    // ============== BUAT CUSTOMER BARU ==============

    public function openCreateModal(string $username, ?int $suggestedPackageId)
    {
        $this->creatingUsername = $username;
        $this->newCustomerName = "PPPoE - {$username}";
        $this->newPackageId = $suggestedPackageId;
        $this->dispatchBrowserEvent('open-create-modal');
    }

    public function getPppoePackagesProperty()
    {
        return InternetPackage::where('company_id', Auth::user()->company_id)
            ->where('access_type', 'pppoe')
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
            'access_type' => 'pppoe',
            'router_id' => $this->routerId,
            'username' => $this->creatingUsername,
        ]);

        // Sama seperti link: TIDAK dispatch provisioning. Password secret
        // yang lama di router tetap dipakai, sistem cuma "mengenali" secret
        // yang sudah ada, bukan bikin ulang.

        $this->creatingUsername = null;

        session()->flash('message', "Customer baru dibuat dengan status PENDING dan terhubung ke secret PPPoE yang sudah ada. Lengkapi data (alamat, KTP, dll) sebelum diaktifkan.");

        return redirect()->route('internet-customer.edit', $customer->id);
    }

    public function render()
    {
        return view('livewire.pppoe-customer.pppoe-reconcile-index', [
            'routers' => Router::byCompany(Auth::user()->company_id)->get(),
        ])->extends('adminlte::page');
    }
}

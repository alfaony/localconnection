<?php

namespace App\Http\Livewire\Billing;

use App\Models\Company;
use App\Models\PlatformInvoice;
use App\Services\PlatformBilling\PlatformInvoiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman billing sisi superadmin: kelola tagihan platform semua Company
 * (Rp1000/customer/bulan), termasuk override manual "tandai lunas" dan
 * "buka/tutup suspend" untuk kasus-kasus di luar alur otomatis.
 */
class PlatformBillingIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function generateNow(PlatformInvoiceService $service)
    {
        $created = $service->generateInvoicesForPeriod();
        session()->flash('message', count($created) . ' invoice periode ini berhasil digenerate.');
    }

    public function markPaid(int $invoiceId, PlatformInvoiceService $service)
    {
        $invoice = PlatformInvoice::findOrFail($invoiceId);
        $service->markPaid($invoice, Auth::id());
        session()->flash('message', "Invoice {$invoice->period} milik {$invoice->company->name} ditandai lunas.");
    }

    public function suspendCompany(string $companyId, PlatformInvoiceService $service)
    {
        $company = Company::findOrFail($companyId);
        $service->manuallySuspend($company);
        session()->flash('message', "Akses dashboard {$company->name} disuspend manual.");
    }

    public function unsuspendCompany(string $companyId, PlatformInvoiceService $service)
    {
        $company = Company::findOrFail($companyId);
        $service->manuallyUnsuspend($company);
        session()->flash('message', "Akses dashboard {$company->name} dibuka kembali.");
    }

    public function render()
    {
        $invoices = PlatformInvoice::with('company')
            ->whereHas('company', function ($query) {
                $query->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('period')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.billing.platform-billing-index', [
            'invoices' => $invoices,
            'suspendedCount' => Company::whereNotNull('billing_suspended_at')->count(),
        ])->extends('adminlte::page');
    }
}

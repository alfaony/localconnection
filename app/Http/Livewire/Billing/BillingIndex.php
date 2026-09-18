<?php

namespace App\Http\Livewire\Billing;

use App\Models\Company;
use App\Models\PlatformInvoice;
use App\Services\PlatformBilling\PlatformBillingMidtransService;
use App\Services\PlatformBilling\PlatformInvoiceService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Halaman billing sisi Company: lihat tagihan platform (Rp1000/customer/bulan)
 * dan bayar via Midtrans. Ini juga halaman yang tetap bisa diakses walau
 * Company sedang disuspend (lihat EnsureCompanyNotSuspended middleware).
 */
class BillingIndex extends Component
{
    public function company(): Company
    {
        return Company::findOrFail(Auth::user()->company_id);
    }

    public function pay(int $invoiceId, PlatformInvoiceService $service)
    {
        $invoice = PlatformInvoice::where('company_id', $this->company()->id)->findOrFail($invoiceId);

        if ($invoice->isPaid()) {
            session()->flash('message', 'Invoice ini sudah lunas.');
            return;
        }

        if (!$invoice->midtrans_snap_token) {
            $service->attachMidtransTransaction($invoice);
            $invoice = $invoice->fresh();
        }

        if (!$invoice->midtrans_snap_token) {
            session()->flash('error', 'Gagal membuat transaksi pembayaran. Silakan coba lagi atau hubungi support.');
            return;
        }

        $midtrans = new PlatformBillingMidtransService();
        return redirect()->away($midtrans->getRedirectUrl($invoice->midtrans_snap_token));
    }

    public function render()
    {
        $company = $this->company();

        return view('livewire.billing.billing-index', [
            'company' => $company,
            'invoices' => $company->platformInvoices()->orderByDesc('period')->paginate(12),
            'rate' => (int) config('services.platform_billing.rate_per_customer', 1000),
        ])->extends('adminlte::page');
    }
}

<?php

namespace App\Services\PlatformBilling;

use App\Models\Company;
use App\Models\CompanyCustomerSnapshot;
use App\Models\InternetCustomer;
use App\Models\PlatformInvoice;
use App\Schemas\ParamSchema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Logika bisnis tagihan platform (Keloola BOS -> Company): rate Rp1000/customer
 * aktif/bulan, dihitung dari rata-rata snapshot harian jumlah customer aktif.
 */
class PlatformInvoiceService
{
    protected PlatformBillingMidtransService $midtrans;

    public function __construct(PlatformBillingMidtransService $midtrans)
    {
        $this->midtrans = $midtrans;
    }

    public function ratePerCustomer(): int
    {
        return (int) config('services.platform_billing.rate_per_customer', 1000);
    }

    public function dueDays(): int
    {
        return (int) config('services.platform_billing.due_days', 7);
    }

    /**
     * Snapshot harian jumlah customer aktif (INSTALLED/REACTIVATED) tiap company.
     * Dipanggil oleh scheduler tiap hari.
     */
    public function takeDailySnapshot(?Carbon $date = null): void
    {
        $date = $date ?? now();

        Company::query()->select('id')->chunkById(100, function ($companies) use ($date) {
            foreach ($companies as $company) {
                $count = InternetCustomer::where('company_id', $company->id)
                    ->whereIn('status', [ParamSchema::INSTALLED, ParamSchema::REACTIVATED])
                    ->count();

                CompanyCustomerSnapshot::updateOrCreate(
                    ['company_id' => $company->id, 'snapshot_date' => $date->toDateString()],
                    ['active_customer_count' => $count]
                );
            }
        });
    }

    /**
     * Generate invoice bulanan untuk periode tertentu (default: bulan lalu),
     * dari rata-rata snapshot harian dibulatkan. Idempotent: skip company yang
     * sudah punya invoice di periode itu.
     */
    public function generateInvoicesForPeriod(?string $period = null): array
    {
        $period = $period ?? now()->subMonthNoOverflow()->format('Y-m');
        $periodStart = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $created = [];

        Company::query()->select('id')->chunkById(100, function ($companies) use ($period, $periodStart, $periodEnd, &$created) {
            foreach ($companies as $company) {
                if (PlatformInvoice::where('company_id', $company->id)->where('period', $period)->exists()) {
                    continue;
                }

                $avgCount = CompanyCustomerSnapshot::where('company_id', $company->id)
                    ->whereBetween('snapshot_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                    ->avg('active_customer_count');

                if (is_null($avgCount)) {
                    continue;
                }

                $billedCount = (int) round($avgCount);
                $rate = $this->ratePerCustomer();

                $invoice = PlatformInvoice::create([
                    'company_id' => $company->id,
                    'period' => $period,
                    'billed_customer_count' => $billedCount,
                    'rate_per_customer' => $rate,
                    'amount' => $billedCount * $rate,
                    'status' => PlatformInvoice::STATUS_PENDING,
                    'due_date' => now()->addDays($this->dueDays()),
                ]);

                $this->attachMidtransTransaction($invoice);

                $created[] = $invoice;
            }
        });

        return $created;
    }

    /**
     * Buat/refresh SNAP token Midtrans untuk satu invoice. Dipanggil saat invoice
     * dibuat, dan bisa dipanggil ulang manual dari UI kalau token expired.
     */
    public function attachMidtransTransaction(PlatformInvoice $invoice): PlatformInvoice
    {
        if ($invoice->isPaid()) {
            return $invoice;
        }

        $result = $this->midtrans->createSnapTransaction($invoice);

        if ($result['success'] ?? false) {
            $invoice->update([
                'midtrans_order_id' => $result['order_id'],
                'midtrans_snap_token' => $result['snap_token'],
            ]);
        } else {
            Log::warning('Gagal membuat SNAP token invoice platform', [
                'invoice_id' => $invoice->id,
                'message' => $result['message'] ?? null,
            ]);
        }

        return $invoice->fresh();
    }

    /**
     * Tandai invoice yang sudah lewat jatuh tempo tapi belum dibayar sebagai
     * overdue, lalu suspend akses dashboard company terkait.
     */
    public function markOverdueAndSuspend(): void
    {
        $overdueInvoices = PlatformInvoice::where('status', PlatformInvoice::STATUS_PENDING)
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => PlatformInvoice::STATUS_OVERDUE]);

            $company = $invoice->company;
            if ($company && !$company->isBillingSuspended()) {
                $company->update(['billing_suspended_at' => now()]);
                Log::info('Company disuspend karena tagihan platform menunggak', [
                    'company_id' => $company->id,
                    'invoice_id' => $invoice->id,
                    'period' => $invoice->period,
                ]);
            }
        }
    }

    /**
     * Tandai invoice lunas (dipanggil dari webhook Midtrans ATAU aksi manual
     * superadmin "tandai lunas"). Buka suspend company kalau sudah tidak ada
     * invoice overdue lain yang tersisa.
     */
    public function markPaid(PlatformInvoice $invoice, ?string $manuallyConfirmedBy = null): PlatformInvoice
    {
        if (!$invoice->isPaid()) {
            $invoice->update([
                'status' => PlatformInvoice::STATUS_PAID,
                'paid_at' => now(),
                'manually_confirmed_by' => $manuallyConfirmedBy,
            ]);
        }

        $this->reactivateCompanyIfClear($invoice->company);

        return $invoice->fresh();
    }

    /**
     * Buka suspend company kalau tidak ada lagi invoice overdue/pending-lewat-jatuh-tempo.
     */
    public function reactivateCompanyIfClear(?Company $company): void
    {
        if (!$company || !$company->isBillingSuspended()) {
            return;
        }

        $hasOverdue = PlatformInvoice::where('company_id', $company->id)
            ->where('status', PlatformInvoice::STATUS_OVERDUE)
            ->exists();

        if (!$hasOverdue) {
            $company->update(['billing_suspended_at' => null]);
        }
    }

    /**
     * Override manual superadmin: suspend/unsuspend company terlepas dari status invoice.
     */
    public function manuallySuspend(Company $company): void
    {
        $company->update(['billing_suspended_at' => now()]);
    }

    public function manuallyUnsuspend(Company $company): void
    {
        $company->update(['billing_suspended_at' => null]);
    }
}

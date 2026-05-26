<?php

namespace App\Jobs;

use App\Exports\SaleExport;
use App\Helpers\InboxHelper;
use App\Models\Sale;
use App\Models\User;
use App\Schemas\RoleSchema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportSaleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 1;

    protected $filters;
    protected $requestUser;
    protected $companyIds;
    protected $fileName;

    public function __construct($filters, $requestUser, $companyIds)
    {
        $this->filters     = $filters;
        $this->requestUser = $requestUser;
        $this->companyIds  = $companyIds;

        $timestamp      = now()->format('Ymd_His');
        $this->fileName = "sales_export_{$timestamp}.xlsx";
    }

    public function handle()
    {
        try {
            Log::info('Starting export Sales', [
                'user_id'   => $this->requestUser->id,
                'file_name' => $this->fileName,
                'filters'   => $this->filters,
            ]);

            $filePath = 'exports/' . $this->fileName;
            Excel::store(
                new SaleExport($this->filters, $this->companyIds),
                $filePath,
                's3'
            );

            $downloadUrl = Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(10));

            $totalRecords = $this->getTotalRecords();

            Log::info('Sale export completed', [
                'file_path'    => $filePath,
                'total_records' => $totalRecords,
            ]);

            $this->sendInboxNotification($downloadUrl, $totalRecords);

        } catch (\Exception $e) {
            Log::error('Sale export failed', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => $this->requestUser->id,
            ]);

            $this->sendErrorNotification($e->getMessage());
        }
    }

    protected function getTotalRecords(): int
    {
        $companyIds = $this->companyIds;

        $query = Sale::whereHas('user', function ($q) use ($companyIds) {
            $q->whereIn('company_id', $companyIds);
        })->where('status', 'completed');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        return $query->count();
    }

    protected function sendInboxNotification(string $downloadUrl, int $totalRecords): void
    {
        try {
            $inboxHelper = new InboxHelper();

            $systemUser = User::whereHas('role', function ($q) {
                $q->whereIn('name', [
                    RoleSchema::SYSTEM_BOS,
                    RoleSchema::ROOT,
                    RoleSchema::ADMIN,
                    RoleSchema::DIRECTOR,
                    RoleSchema::MANAGER,
                ]);
            })->first();

            $filterInfo = $this->buildFilterInfo();
            $message    = "✅ Export Sales selesai! {$filterInfo}Total: {$totalRecords} transaksi | File: {$this->fileName}";

            $inboxHelper->sent(
                $this->requestUser->id,
                $systemUser->id,
                $message,
                null,
                true,
                'download',
                $downloadUrl
            );

            Log::info('Sale export inbox sent', [
                'user_id'      => $this->requestUser->id,
                'download_url' => $downloadUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send sale export inbox notification', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function sendErrorNotification(string $errorMessage): void
    {
        try {
            $inboxHelper = new InboxHelper();

            $systemUser = User::whereHas('role', function ($q) {
                $q->whereIn('name', [
                    RoleSchema::SYSTEM_BOS,
                    RoleSchema::ROOT,
                    RoleSchema::ADMIN,
                    RoleSchema::DIRECTOR,
                    RoleSchema::MANAGER,
                ]);
            })->first();

            $message = "❌ Export Sales gagal! Error: {$errorMessage}";

            $inboxHelper->sent(
                $this->requestUser->id,
                $systemUser->id,
                $message,
                null,
                false,
                'email',
                null
            );

        } catch (\Throwable $e) {
            Log::error('Failed to send sale export error notification', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function buildFilterInfo(): string
    {
        $parts = [];

        if (!empty($this->filters['search'])) {
            $parts[] = "Search: {$this->filters['search']}";
        }

        $pmLabels = ['cash' => 'Cash', 'qris' => 'QRIS', 'debit_credit' => 'Debit/Kredit'];
        if (!empty($this->filters['payment_method'])) {
            $parts[] = 'Metode: ' . ($pmLabels[$this->filters['payment_method']] ?? $this->filters['payment_method']);
        }

        if (!empty($this->filters['start_date'])) {
            $parts[] = "Dari: {$this->filters['start_date']}";
        }

        if (!empty($this->filters['end_date'])) {
            $parts[] = "Sampai: {$this->filters['end_date']}";
        }

        return !empty($parts) ? implode(' | ', $parts) . ' | ' : '';
    }
}

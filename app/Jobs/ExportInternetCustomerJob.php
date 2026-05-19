<?php

namespace App\Jobs;

use App\Exports\InternetCustomerExport;
use App\Helpers\InboxHelper;
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

class ExportInternetCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 1;

    protected array $filters;
    protected $requestUser;
    protected $company_id;
    protected string $format;
    protected string $fileName;

    public function __construct(array $filters, $requestUser, $company_id, string $format = 'xlsx')
    {
        $this->filters     = $filters;
        $this->requestUser = $requestUser;
        $this->company_id  = $company_id;
        $this->format      = $format;

        $timestamp      = now()->format('Ymd_His');
        $ext            = $format === 'csv' ? 'csv' : 'xlsx';
        $this->fileName = "internet_customers_export_{$timestamp}.{$ext}";
    }

    public function handle()
    {
        try {
            Log::info('Starting export Internet Customer', [
                'user_id'   => $this->requestUser->id,
                'file_name' => $this->fileName,
                'filters'   => $this->filters,
            ]);

            $filePath     = 'exports/' . $this->fileName;
            $exportFormat = $this->format === 'csv'
                ? \Maatwebsite\Excel\Excel::CSV
                : \Maatwebsite\Excel\Excel::XLSX;

            Excel::store(
                new InternetCustomerExport($this->filters, $this->company_id),
                $filePath,
                's3',
                $exportFormat
            );

            $downloadUrl = Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(30));

            Log::info('Internet customer export completed', ['file_path' => $filePath]);

            $this->sendInboxNotification($downloadUrl);

        } catch (\Exception $e) {
            Log::error('Internet customer export failed', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => $this->requestUser->id,
            ]);

            $this->sendErrorNotification($e->getMessage());
        }
    }

    protected function sendInboxNotification(string $downloadUrl): void
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
            $message    = "✅ Export Internet Customer selesai! {$filterInfo}File: {$this->fileName}";

            $inboxHelper->sent(
                $this->requestUser->id,
                $systemUser->id,
                $message,
                null,
                true,
                'download',
                $downloadUrl
            );

            Log::info('Internet customer export inbox sent', [
                'user_id'      => $this->requestUser->id,
                'download_url' => $downloadUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send internet customer export inbox', ['error' => $e->getMessage()]);
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

            $message = "❌ Export Internet Customer gagal! Error: {$errorMessage}";

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
            Log::error('Failed to send internet customer export error notification', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function buildFilterInfo(): string
    {
        $parts = [];

        if (!empty($this->filters['search'])) {
            $parts[] = "Search: {$this->filters['search']}";
        }

        if (!empty($this->filters['statusFilter'])) {
            $parts[] = "Status: {$this->filters['statusFilter']}";
        }

        if (!empty($this->filters['customerTypeFilter'])) {
            $parts[] = "Tipe: {$this->filters['customerTypeFilter']}";
        }

        if (!empty($this->filters['dateFrom'])) {
            $parts[] = "Dari: {$this->filters['dateFrom']}";
        }

        if (!empty($this->filters['dateTo'])) {
            $parts[] = "Sampai: {$this->filters['dateTo']}";
        }

        return !empty($parts) ? implode(' | ', $parts) . ' | ' : '';
    }
}

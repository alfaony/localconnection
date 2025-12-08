<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\ProductStoreExport;
use App\Helpers\InboxHelper;
use App\Models\User;
use App\Schemas\RoleSchema;

class ExportProductStoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $requestUser;
    protected $fileName;

    /**
     * Create a new job instance.
     *
     * @param array $filters
     * @param User $requestUser
     */
    public function __construct($filters, $requestUser)
    {
        $this->filters = $filters;
        $this->requestUser = $requestUser;
        
        // Generate filename
        $timestamp = now()->format('Ymd_His');
        $this->fileName = "product_store_export_{$timestamp}.xlsx";
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('Starting export Product Store', [
                'user_id' => $this->requestUser->id,
                'file_name' => $this->fileName,
                'filters' => $this->filters
            ]);

            // Export to Excel
            $filePath = 'exports/' . $this->fileName;
            Excel::store(
                new ProductStoreExport($this->filters),
                $filePath,
                's3'
            );

            // Generate download URL (valid for 10 minutes)
            $downloadUrl = Storage::disk('s3')->temporaryUrl($filePath, now()->addMinutes(10));

            Log::info('Export completed successfully', [
                'file_path' => $filePath,
                'download_url' => $downloadUrl
            ]);

            // Get total exported records
            $totalRecords = $this->getTotalRecords();

            // Send notification with download link
            $this->sendInboxNotification($downloadUrl, $totalRecords);

        } catch (\Exception $e) {
            Log::error('Failed to export Product Store', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->requestUser->id
            ]);

            // Send error notification
            $this->sendErrorNotification($e->getMessage());
        }
    }

    /**
     * Get total records based on filters
     *
     * @return int
     */
    protected function getTotalRecords()
    {
        $query = \App\Models\ProductStore::query();

        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        if (!empty($this->filters['category'])) {
            $query->where('category_product_store_id', $this->filters['category']);
        }

        if (!empty($this->filters['warehouse'])) {
            $query->whereHas('rack.zone.warehouse', function ($q) {
                $q->where('id', $this->filters['warehouse']);
            });
        }

        if (!empty($this->filters['zone'])) {
            $query->whereHas('rack.zone', function ($q) {
                $q->where('id', $this->filters['zone']);
            });
        }

        return $query->count();
    }

    /**
     * Send inbox notification with download link
     *
     * @param string $downloadUrl
     * @param int $totalRecords
     * @return void
     */
    protected function sendInboxNotification($downloadUrl, $totalRecords)
    {
        try {
            $inboxHelper = new InboxHelper();

            // System user ID
            $systemUserId = User::whereHas('role', function ($query) {
                $query->whereIn('name', [
                    RoleSchema::SYSTEM_BOS, 
                    RoleSchema::ROOT, 
                    RoleSchema::ADMIN, 
                    RoleSchema::DIRECTOR, 
                    RoleSchema::MANAGER
                ]);
            })->first();

            // Build filter info
            $filterInfo = $this->buildFilterInfo();

            // Message for notification
            $message = "✅ Export Product Store selesai! {$filterInfo}Total: {$totalRecords} data | File: {$this->fileName}";

            // Send with download_url parameter
            $inboxHelper->sent(
                $this->requestUser->id,
                $systemUserId->id,
                $message,
                null,
                true,
                'download',
                $downloadUrl
            );

            Log::info('Inbox notification sent with download_url', [
                'user_id' => $this->requestUser->id,
                'download_url' => $downloadUrl,
                'total_records' => $totalRecords
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send inbox notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e; 
        }
    }

    /**
     * Send error notification
     *
     * @param string $errorMessage
     * @return void
     */
    protected function sendErrorNotification($errorMessage)
    {
        try {
            $inboxHelper = new InboxHelper();

            $systemUserId = User::whereHas('role', function ($query) {
                $query->whereIn('name', [
                    RoleSchema::SYSTEM_BOS, 
                    RoleSchema::ROOT, 
                    RoleSchema::ADMIN, 
                    RoleSchema::DIRECTOR, 
                    RoleSchema::MANAGER
                ]);
            })->first();

            $message = "❌ Export Product Store gagal! Error: {$errorMessage}";

            $inboxHelper->sent(
                $this->requestUser->id,
                $systemUserId->id,
                $message,
                null,
                false,
                'email',
                null
            );

            Log::info('Error notification sent', [
                'user_id' => $this->requestUser->id,
                'error' => $errorMessage
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to send error notification', [
                'error' => $e->getMessage()
            ]);

            throw $e; 
        }
    }

    /**
     * Build filter information string
     *
     * @return string
     */
    protected function buildFilterInfo()
    {
        $filterParts = [];

        if (!empty($this->filters['search'])) {
            $filterParts[] = "Search: {$this->filters['search']}";
        }

        if (!empty($this->filters['category'])) {
            $category = \App\Models\CategoryProductStore::find($this->filters['category']);
            if ($category) {
                $filterParts[] = "Category: {$category->name}";
            }
        }

        if (!empty($this->filters['warehouse'])) {
            $warehouse = \App\Models\Warehouse::find($this->filters['warehouse']);
            if ($warehouse) {
                $filterParts[] = "Warehouse: {$warehouse->name}";
            }
        }

        if (!empty($this->filters['zone'])) {
            $zone = \App\Models\Zone::find($this->filters['zone']);
            if ($zone) {
                $filterParts[] = "Zone: {$zone->name}";
            }
        }

        return !empty($filterParts) ? implode(' | ', $filterParts) . ' | ' : '';
    }
}
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

use App\Exports\UsedLaptopExport;
use App\Helpers\InboxHelper;
use App\Models\User;
use App\Schemas\RoleSchema;

class ExportUsedLaptopJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $requestUser;
    protected $fileName;
    protected $companyIds;

    public function __construct($filters, $requestUser)
    {
        $this->filters = $filters;
        $this->requestUser = $requestUser;
        
        // Get accessible company IDs
        $this->companyIds = $requestUser->accessibleCompanies
            ->pluck('id')
            ->push($requestUser->company_id)
            ->unique()
            ->toArray();
        
        $timestamp = now()->format('Ymd_His');
        $this->fileName = "used_laptop_export_{$timestamp}.xlsx";
    }

    public function handle()
    {
        try {
            Log::info('Starting export Used Laptop', [
                'user_id' => $this->requestUser->id,
                'file_name' => $this->fileName,
                'filters' => $this->filters,
                'company_ids' => $this->companyIds
            ]);

            // Export to Excel and store directly to S3
            $filePath = 'exports/' . $this->fileName;
            Excel::store(
                new UsedLaptopExport($this->filters, $this->companyIds),
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
            Log::error('Failed to export Used Laptop', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->requestUser->id
            ]);

            // Send error notification
            $this->sendErrorNotification($e->getMessage());
        }
    }

    protected function getTotalRecords()
    {
        $query = \App\Models\UsedLaptop::whereHas('user', function ($q) {
            $q->whereIn('company_id', $this->companyIds);
        });

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['is_sold'])) {
            if ($this->filters['is_sold'] === 'sold') {
                $query->where('is_sold', 1);
            } elseif ($this->filters['is_sold'] === 'available') {
                $query->where('is_sold', 0);
            } elseif ($this->filters['is_sold'] === 'inventory') {
                $query->whereNull('is_sold');
            }
        }

        if (!empty($this->filters['rack_id'])) {
            $query->where('rack_id', $this->filters['rack_id']);
        }

        if (!empty($this->filters['zone_id'])) {
            $query->whereHas('rack.zone', function ($q) {
                $q->where('id', $this->filters['zone_id']);
            });
        }

        if (!empty($this->filters['warehouse_id'])) {
            $query->whereHas('rack.zone.warehouse', function ($q) {
                $q->where('id', $this->filters['warehouse_id']);
            });
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query->count();
    }

    protected function sendInboxNotification($downloadUrl, $totalRecords)
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

            $filterInfo = $this->buildFilterInfo();
            $message = "✅ Export Used Laptop selesai! {$filterInfo}Total: {$totalRecords} data | File: {$this->fileName}";

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

            $message = "❌ Export Used Laptop gagal! Error: {$errorMessage}";

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

    protected function buildFilterInfo()
    {
        $filterParts = [];

        if (!empty($this->filters['search'])) {
            $filterParts[] = "Search: {$this->filters['search']}";
        }

        if (isset($this->filters['is_sold'])) {
            $statusLabels = [
                'sold' => 'Terjual',
                'available' => 'Tersedia',
                'inventory' => 'Inventory',
            ];
            $filterParts[] = "Status: " . ($statusLabels[$this->filters['is_sold']] ?? $this->filters['is_sold']);
        }

        if (!empty($this->filters['rack_id'])) {
            $rack = \App\Models\Rack::find($this->filters['rack_id']);
            if ($rack) {
                $filterParts[] = "Rack: {$rack->name}";
            }
        }

        if (!empty($this->filters['zone_id'])) {
            $zone = \App\Models\Zone::find($this->filters['zone_id']);
            if ($zone) {
                $filterParts[] = "Zone: {$zone->name}";
            }
        }

        if (!empty($this->filters['warehouse_id'])) {
            $warehouse = \App\Models\Warehouse::find($this->filters['warehouse_id']);
            if ($warehouse) {
                $filterParts[] = "Warehouse: {$warehouse->name}";
            }
        }

        if (!empty($this->filters['start_date'])) {
            $filterParts[] = "Dari: {$this->filters['start_date']}";
        }

        if (!empty($this->filters['end_date'])) {
            $filterParts[] = "Sampai: {$this->filters['end_date']}";
        }

        return !empty($filterParts) ? implode(' | ', $filterParts) . ' | ' : '';
    }
}
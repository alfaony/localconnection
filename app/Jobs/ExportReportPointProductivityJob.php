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
use Carbon\Carbon;

use App\Exports\ReportPointProductivityExport;
use App\Helpers\InboxHelper;
use App\Models\User;
use App\Schemas\RoleSchema;

class ExportReportPointProductivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reports;
    protected $startDate;
    protected $endDate;
    protected $requestUser;
    protected $fileName;

    public function __construct($reports, $startDate, $endDate, $requestUser)
    {
        $this->reports = $reports;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->requestUser = $requestUser;
        
        // Generate filename
        $dateRange = $startDate->format('Ymd') . '-' . $endDate->format('Ymd');
        $this->fileName = "report_point_productivity_{$dateRange}_" . time() . ".xlsx";
    }

    public function handle()
    {
        try {
            Log::info('Starting export Report Point Productivity', [
                'user_id' => $this->requestUser->id,
                'file_name' => $this->fileName,
                'total_reports' => count($this->reports)
            ]);

            // Export to Excel
            $filePath = 'exports/' . $this->fileName;
            Excel::store(
                new ReportPointProductivityExport($this->reports, $this->startDate, $this->endDate),
                $filePath
            );
            

            // Generate download URL
            $downloadUrl = s3_asset(true,10, $filePath);

            Log::info('Export completed successfully', [
                'file_path' => $filePath,
                'download_url' => $downloadUrl
            ]);

            // Send notification with download link
            $this->sendInboxNotification($downloadUrl, count($this->reports));

        } catch (\Exception $e) {

            Log::error('Failed to export Report Point Productivity', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $this->requestUser->id
            ]);

            // Send error notification
            $this->sendErrorNotification($e->getMessage());
        }
    }

    protected function sendInboxNotification($downloadUrl, $totalReports)
    {
        try {
            $inboxHelper = new InboxHelper();

            // System user ID
            $systemUserId = User::whereHas('role', function ($query) {
                $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR, RoleSchema::MANAGER]);
            })->first();

            // Message untuk notifikasi
            $dateRange = $this->startDate->format('d/m/Y') . ' - ' . $this->endDate->format('d/m/Y');
            $message = "✅ Export Report Point Productivity selesai! Periode: {$dateRange} | Total: {$totalReports} data | File: {$this->fileName}";

            // PENTING: Pakai download_url parameter, directUrl = null
            $inboxHelper->sent(
                userToId: $this->requestUser->id,
                userFromId: $systemUserId->id,
                message: $message,
                directUrl: null,
                isRead: false,
                category: 'download',
                downloadUrl: $downloadUrl
            );

            Log::info('Inbox notification sent with download_url', [
                'user_id' => $this->requestUser->id,
                'download_url' => $downloadUrl,
                'total_reports' => $totalReports
            ]);

        } catch (\Exception $e) {
            // dd($e);
            Log::error('Failed to send inbox notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function sendErrorNotification($errorMessage)
    {
        try {
            $inboxHelper = new InboxHelper();

            $systemUserId = User::whereHas('role', function ($query) {
                $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR, RoleSchema::MANAGER]);
            })->first();

            $message = "❌ Export Report Point Productivity gagal! Error: {$errorMessage}";

            $inboxHelper->sent(
                userToId: $this->requestUser->id,
                userFromId: $systemUserId->id,
                message: $message,
                directUrl: null,
                isRead: false,
                category: 'email',
                download_url: null
            );

        } catch (\Exception $e) {
            // dd($e);
            Log::error('Failed to send error notification', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
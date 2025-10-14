<?php

namespace App\Jobs;

use App\Exports\UsersExport;
use App\Models\User;
use App\Helpers\InboxHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestUser;
    protected $filters;
    protected $fileName;

    public $timeout = 600;
    public $tries = 3;

    public function __construct($requestUser, $filters = [])
    {
        $this->requestUser = $requestUser;
        $this->filters = $filters;
        $this->fileName = 'users_export_' . Carbon::now()->format('YmdHis') . '.xlsx';
    }

    public function handle()
    {
        try {
            Log::info('Starting KYE user export job', [
                'user_id' => $this->requestUser->id,
                'filters' => $this->filters,
                'file_name' => $this->fileName
            ]);

            // Query users with filters
            $query = User::with([
                'kye',
                'divisions',
                'userPositions.position',
                'userSalaries' => function ($q) {
                    $q->latest();
                }
            ]);

            $query = $this->applyFilters($query);
            $users = $query->get();

            Log::info('Users retrieved for export', [
                'count' => $users->count()
            ]);

            // Create export
            $export = new UsersExport($users);

            // Store file in exports directory (public disk)
            $path = 'exports/' . $this->fileName;
            Excel::store($export, $path, 'public');

            Log::info('Export file created successfully', [
                'path' => $path,
                'file_name' => $this->fileName,
                'total_users' => $users->count()
            ]);

            // Generate DIRECT storage URL (no controller needed!)
            // File akan accessible di: http://domain.com/storage/exports/file.xlsx
            $downloadUrl = Storage::url($path);

            Log::info('Generated storage URL', [
                'download_url' => $downloadUrl
            ]);

            // Send notification via InboxHelper dengan download_url
            $this->sendInboxNotification($downloadUrl, $users->count());

            Log::info('Export job completed successfully');

        } catch (\Exception $e) {
            Log::error('Export job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->sendErrorNotification($e->getMessage());
            throw $e;
        }
    }

    protected function applyFilters($query)
    {
        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('kye', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%")
                            ->orWhere('ktp_number', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Send inbox notification dengan download_url
     * DIRECT STORAGE URL - no controller needed!
     */
    protected function sendInboxNotification($downloadUrl, $totalUsers)
    {
        try {
            $inboxHelper = new InboxHelper();
            $systemUserId = 1;

            $message = "✅ Export Data User selesai! Total: {$totalUsers} user | File: {$this->fileName}";

            // IMPORTANT: 
            // - directUrl = null (no button link)
            // - downloadUrl = DIRECT storage URL (browser can download directly)
            $inboxHelper->sent(
                userToId: $this->requestUser->id,
                userFromId: $systemUserId,
                message: $message,
                directUrl: null,  // NULL - no button
                isRead: false,
                category: 'email',  // Play email sound
                downloadUrl: $downloadUrl  // Direct storage URL: /storage/exports/file.xlsx
            );

            Log::info('Inbox notification sent with storage URL', [
                'user_id' => $this->requestUser->id,
                'download_url' => $downloadUrl,
                'total_users' => $totalUsers
            ]);

        } catch (\Exception $e) {
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
            $systemUserId = 1;

            $message = "❌ Export Data User gagal! Error: " . substr($errorMessage, 0, 100);

            $inboxHelper->sent(
                userToId: $this->requestUser->id,
                userFromId: $systemUserId,
                message: $message,
                directUrl: null,
                isRead: false,
                category: 'high',
                downloadUrl: null
            );

            Log::info('Error notification sent', [
                'user_id' => $this->requestUser->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send error notification', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Export job failed permanently', [
            'user_id' => $this->requestUser->id,
            'error' => $exception->getMessage()
        ]);

        $this->sendErrorNotification($exception->getMessage());
    }
}
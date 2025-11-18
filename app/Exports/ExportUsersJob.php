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

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($requestUser, $filters = [])
    {
        $this->requestUser = $requestUser;
        $this->filters = $filters;
        $this->fileName = 'users_export_' . Carbon::now()->format('YmdHis') . '.xlsx';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
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

            // Apply filters
            $query = $this->applyFilters($query);

            // Get users
            $users = $query->get();

            Log::info('Users retrieved for export', [
                'count' => $users->count()
            ]);

            // Create export
            $export = new UsersExport($users);

            // Store file in exports directory
            $path = 'exports/' . $this->fileName;
            Excel::store($export, $path);

            Log::info('Export file created successfully', [
                'path' => $path,
                'file_name' => $this->fileName,
                'total_users' => $users->count()
            ]);

            // Generate download URL
            $downloadUrl = route('users.export.download', ['fileName' => $this->fileName]);

            // Send notification via InboxHelper dengan download_url
            $this->sendInboxNotification($downloadUrl, $users->count());

            Log::info('Export job completed successfully');

        } catch (\Exception $e) {
            Log::error('Export job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Send error notification
            $this->sendErrorNotification($e->getMessage());

            throw $e;
        }
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query)
    {
        // Search by name
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
     * PENTING: Pakai download_url, bukan directUrl
     */
    protected function sendInboxNotification($downloadUrl, $totalUsers)
    {
        try {
            $inboxHelper = new InboxHelper();

            // System user ID
            $systemUserId = User::whereHas('role', function ($query) {
                $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT, RoleSchema::ADMIN, RoleSchema::DIRECTOR, RoleSchema::MANAGER]);
            })->first();

            // Message untuk notifikasi
            $message = "✅ Export Data User selesai! Total: {$totalUsers} user | File: {$this->fileName}";

            // PENTING: Pakai download_url parameter, directUrl = null
            $inboxHelper->sent(
                userToId: $this->requestUser->id,
                userFromId: $systemUserId,
                message: $message,
                directUrl: null,  // NULL - tidak pakai button link
                isRead: false,
                category: 'download',  // category 'email' = play email sound
                download_url: $downloadUrl  // Parameter baru untuk auto-download
            );

            Log::info('Inbox notification sent with download_url', [
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

    /**
     * Send error notification via InboxHelper
     */
    protected function sendErrorNotification($errorMessage)
    {
        try {
            $inboxHelper = new InboxHelper();
            $systemUserId = 1;

            $message = "❌ Export Data User gagal! Error: " . substr($errorMessage, 0, 100);

            // Error notification - no download_url
            $inboxHelper->sent(
                userToId: $this->requestUser->id,
                userFromId: $systemUserId,
                message: $message,
                directUrl: null,
                isRead: false,
                category: 'high',  // category 'high' = play high sound
                download_url: null  // No download for error
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

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Export job failed permanently', [
            'user_id' => $this->requestUser->id,
            'error' => $exception->getMessage()
        ]);

        // Send error notification
        $this->sendErrorNotification($exception->getMessage());
    }
}